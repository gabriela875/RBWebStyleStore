<?php
// Ventas: creacion, detalles (reserva de stock), pagos multiples, completar y anular.
// Usa transacciones PDO en completar y anular. Traducido de VentaService.java.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/inventario.php'; // reservar/liberar/descontar stock
require_once __DIR__ . '/productos.php';   // buscarDescuentoActivo, buscarProducto

// --- Precio final con descuento vigente (traducido de calcularPrecioFinal) ---
function calcularPrecioFinal($pdo, $idProducto) {
    $producto = buscarProducto($pdo, $idProducto);
    $precioBase = (float)$producto['Precio_base'];
    $desc = buscarDescuentoActivo($pdo, $idProducto);
    if (!$desc) {
        return $precioBase;
    }
    if ($desc['TipoDescuento'] === 'Porcentaje') {
        $precioFinal = $precioBase - ($precioBase * ((float)$desc['Valor'] / 100));
    } else {
        $precioFinal = $precioBase - (float)$desc['Valor'];
    }
    if ($precioFinal <= 0) {
        throw new Exception('El descuento no puede dejar el precio en cero o negativo');
    }
    return $precioFinal;
}

// Crea una venta en estado Pendiente y devuelve su id
function crearVenta($pdo, $idEmpleado, $idCliente) {
    $pdo->prepare("INSERT INTO venta (Fecha_hora, Total, id_Estado_Venta, id_Cliente, id_Empleado)
                   VALUES (NOW(), 0,
                     (SELECT id_Estado_Venta FROM estado_venta WHERE Nombre = 'Pendiente'),
                     :cli, :emp)")
        ->execute([':cli' => $idCliente, ':emp' => $idEmpleado]);
    return (int)$pdo->lastInsertId();
}

function estadoVenta($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT ev.Nombre FROM venta v
                           JOIN estado_venta ev ON ev.id_Estado_Venta = v.id_Estado_Venta
                           WHERE v.id_Venta = :id");
    $stmt->execute([':id' => $idVenta]);
    return $stmt->fetchColumn();
}

// Agrega un producto (combinacion de inventario) a una venta pendiente: reserva stock.
function agregarDetalle($pdo, $idVenta, $idInventario, $cantidad) {
    if (estadoVenta($pdo, $idVenta) !== 'Pendiente') {
        throw new Exception('Solo se pueden agregar productos a ventas pendientes');
    }
    $inv = buscarInventarioPorId($pdo, $idInventario);
    if (!$inv) {
        throw new Exception('La combinación de inventario no existe');
    }
    $estadoProd = nombreEstadoProducto($pdo, $inv['id_Producto']);
    if ($estadoProd === 'Descontinuado') {
        throw new Exception('No se puede vender un producto descontinuado');
    }
    if ($estadoProd === 'Agotado') {
        throw new Exception('No se puede vender un producto agotado');
    }
    if ((int)$cantidad <= 0) {
        throw new Exception('La cantidad debe ser mayor a cero');
    }

    $pdo->beginTransaction();
    try {
        // Reservar stock (valida disponibilidad)
        reservarStock($pdo, $idInventario, (int)$cantidad);

        $producto = buscarProducto($pdo, $inv['id_Producto']);
        $precioBase  = (float)$producto['Precio_base'];
        $precioFinal = calcularPrecioFinal($pdo, $inv['id_Producto']);
        $descuentoAplicado = ($precioFinal < $precioBase) ? ($precioBase - $precioFinal) : null;

        $pdo->prepare("INSERT INTO detalle_venta
                         (Cantidad, Precio_unitario, Descuento_aplicado, id_Venta, id_Inventario)
                       VALUES (:cant, :precio, :desc, :venta, :inv)")
            ->execute([
                ':cant'   => (int)$cantidad,
                ':precio' => $precioFinal,
                ':desc'   => $descuentoAplicado,
                ':venta'  => $idVenta,
                ':inv'    => $idInventario,
            ]);

        recalcularTotal($pdo, $idVenta);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Quita un producto de una venta pendiente: libera el stock reservado.
function quitarDetalle($pdo, $idVenta, $idDetalle) {
    if (estadoVenta($pdo, $idVenta) !== 'Pendiente') {
        throw new Exception('Solo se pueden quitar productos de ventas pendientes');
    }
    $stmt = $pdo->prepare("SELECT * FROM detalle_venta WHERE id_Detalle_Venta = :d AND id_Venta = :v");
    $stmt->execute([':d' => $idDetalle, ':v' => $idVenta]);
    $detalle = $stmt->fetch();
    if (!$detalle) {
        throw new Exception('Detalle no encontrado');
    }

    $pdo->beginTransaction();
    try {
        liberarStock($pdo, $detalle['id_Inventario'], $detalle['Cantidad']);
        $pdo->prepare("DELETE FROM detalle_venta WHERE id_Detalle_Venta = :d")->execute([':d' => $idDetalle]);
        recalcularTotal($pdo, $idVenta);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Recalcula el total de la venta sumando subtotales (precio_unitario * cantidad)
function recalcularTotal($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(Precio_unitario * Cantidad), 0) FROM detalle_venta WHERE id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    $total = $stmt->fetchColumn();
    $pdo->prepare("UPDATE venta SET Total = :t WHERE id_Venta = :v")
        ->execute([':t' => $total, ':v' => $idVenta]);
}

function totalVenta($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT Total FROM venta WHERE id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    return (float)$stmt->fetchColumn();
}

function sumaPagos($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(Valor), 0) FROM pago WHERE id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    return (float)$stmt->fetchColumn();
}

// Registra un pago en una venta pendiente (pagos multiples permitidos).
function registrarPago($pdo, $idVenta, $idMetodo, $valor, $referencia) {
    if (estadoVenta($pdo, $idVenta) !== 'Pendiente') {
        throw new Exception('Solo se pueden registrar pagos en ventas pendientes');
    }
    if ((float)$valor <= 0) {
        throw new Exception('El valor del pago debe ser mayor a cero');
    }

    $stmt = $pdo->prepare("SELECT Nombre FROM metodo_pago WHERE id_Metodo_Pago = :id");
    $stmt->execute([':id' => $idMetodo]);
    $metodo = $stmt->fetchColumn();
    if (!$metodo) {
        throw new Exception('Método de pago no válido');
    }

    // Tarjeta y transferencia requieren referencia
    if ($metodo !== 'Efectivo' && trim((string)$referencia) === '') {
        throw new Exception('Tarjeta y transferencia requieren número de referencia');
    }

    // Efectivo: calcular cambio si el pago supera el total
    $cambio = null;
    if ($metodo === 'Efectivo') {
        $diferencia = (float)$valor - totalVenta($pdo, $idVenta);
        if ($diferencia > 0) {
            $cambio = $diferencia;
        }
    }

    $pdo->prepare("INSERT INTO pago (Valor, id_Metodo_Pago, Referencia, Cambio, id_Venta)
                   VALUES (:valor, :metodo, :ref, :cambio, :venta)")
        ->execute([
            ':valor'  => $valor,
            ':metodo' => $idMetodo,
            ':ref'    => ($referencia !== '' ? $referencia : null),
            ':cambio' => $cambio,
            ':venta'  => $idVenta,
        ]);
}

// Completa una venta: verifica pagos, descuenta stock definitivamente. Transaccion.
function completarVenta($pdo, $idVenta) {
    if (estadoVenta($pdo, $idVenta) !== 'Pendiente') {
        throw new Exception('Solo se pueden completar ventas pendientes');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM detalle_venta WHERE id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('La venta debe tener al menos un producto');
    }
    if (sumaPagos($pdo, $idVenta) < totalVenta($pdo, $idVenta)) {
        throw new Exception('La suma de los pagos no cubre el total de la venta');
    }

    $pdo->beginTransaction();
    try {
        // Descontar stock de cada detalle
        $detalles = $pdo->prepare("SELECT id_Inventario, Cantidad FROM detalle_venta WHERE id_Venta = :v");
        $detalles->execute([':v' => $idVenta]);
        foreach ($detalles->fetchAll() as $d) {
            descontarStock($pdo, $d['id_Inventario'], $d['Cantidad']);
        }
        $pdo->prepare("UPDATE venta SET id_Estado_Venta =
                         (SELECT id_Estado_Venta FROM estado_venta WHERE Nombre = 'Completada')
                       WHERE id_Venta = :v")->execute([':v' => $idVenta]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Anula una venta pendiente: libera stock, borra pagos, registra la anulacion. Transaccion.
function anularVenta($pdo, $idVenta, $motivo, $idEmpleado) {
    if (estadoVenta($pdo, $idVenta) !== 'Pendiente') {
        throw new Exception('Solo se pueden anular ventas pendientes');
    }
    if (mb_strlen(trim($motivo)) < 5) {
        throw new Exception('El motivo de anulación debe tener al menos 5 caracteres');
    }

    $pdo->beginTransaction();
    try {
        // Liberar el stock reservado de cada detalle
        $detalles = $pdo->prepare("SELECT id_Inventario, Cantidad FROM detalle_venta WHERE id_Venta = :v");
        $detalles->execute([':v' => $idVenta]);
        foreach ($detalles->fetchAll() as $d) {
            liberarStock($pdo, $d['id_Inventario'], $d['Cantidad']);
        }
        // Los pagos parciales quedan sin efecto
        $pdo->prepare("DELETE FROM pago WHERE id_Venta = :v")->execute([':v' => $idVenta]);
        // Registrar la anulacion con fecha, hora y empleado
        $pdo->prepare("INSERT INTO anulacion_venta (Motivo, Fecha_hora, id_Venta, id_Empleado)
                       VALUES (:motivo, NOW(), :venta, :emp)")
            ->execute([':motivo' => trim($motivo), ':venta' => $idVenta, ':emp' => $idEmpleado]);
        $pdo->prepare("UPDATE venta SET id_Estado_Venta =
                         (SELECT id_Estado_Venta FROM estado_venta WHERE Nombre = 'Anulada')
                       WHERE id_Venta = :v")->execute([':v' => $idVenta]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// --- Consultas ---

function buscarVenta($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT v.*, ev.Nombre AS Estado,
                                  CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                                  CONCAT(e.Nombre,' ',e.Apellido) AS Empleado
                           FROM venta v
                           JOIN estado_venta ev ON ev.id_Estado_Venta = v.id_Estado_Venta
                           JOIN clientes c      ON c.id_Cliente = v.id_Cliente
                           JOIN empleados e     ON e.id_Empleado = v.id_Empleado
                           WHERE v.id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    return $stmt->fetch() ?: null;
}

function detallesVenta($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT dv.id_Detalle_Venta, dv.Cantidad, dv.Precio_unitario, dv.Descuento_aplicado,
                                  (dv.Precio_unitario * dv.Cantidad) AS Subtotal,
                                  p.Nombre AS Producto, t.Nombre AS Talla, co.Nombre AS Color
                           FROM detalle_venta dv
                           JOIN inventario i ON i.id_Inventario = dv.id_Inventario
                           JOIN producto p   ON p.id_Producto = i.id_Producto
                           JOIN talla t      ON t.id_Talla = i.id_Talla
                           JOIN color co     ON co.id_Color = i.id_Color
                           WHERE dv.id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    return $stmt->fetchAll();
}

function pagosVenta($pdo, $idVenta) {
    $stmt = $pdo->prepare("SELECT pg.Valor, pg.Referencia, pg.Cambio, mp.Nombre AS Metodo
                           FROM pago pg
                           JOIN metodo_pago mp ON mp.id_Metodo_Pago = pg.id_Metodo_Pago
                           WHERE pg.id_Venta = :v");
    $stmt->execute([':v' => $idVenta]);
    return $stmt->fetchAll();
}

// Lista ventas (todas, o de un empleado si se indica)
function listarVentas($pdo, $idEmpleado = null) {
    $sql = "SELECT v.id_Venta, v.Fecha_hora, v.Total, ev.Nombre AS Estado,
                   CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                   CONCAT(e.Nombre,' ',e.Apellido) AS Empleado
            FROM venta v
            JOIN estado_venta ev ON ev.id_Estado_Venta = v.id_Estado_Venta
            JOIN clientes c      ON c.id_Cliente = v.id_Cliente
            JOIN empleados e     ON e.id_Empleado = v.id_Empleado";
    if ($idEmpleado !== null) {
        $sql .= " WHERE v.id_Empleado = :emp";
    }
    $sql .= " ORDER BY v.Fecha_hora DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($idEmpleado !== null ? [':emp' => $idEmpleado] : []);
    return $stmt->fetchAll();
}

// Lista el registro de anulaciones (solo lo consulta el Administrador)
function listarAnulaciones($pdo) {
    $sql = "SELECT av.id_Anulacion, av.Motivo, av.Fecha_hora, av.id_Venta,
                   CONCAT(e.Nombre,' ',e.Apellido) AS Empleado
            FROM anulacion_venta av
            JOIN empleados e ON e.id_Empleado = av.id_Empleado
            ORDER BY av.Fecha_hora DESC";
    return $pdo->query($sql)->fetchAll();
}
