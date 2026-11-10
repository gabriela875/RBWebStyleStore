<?php
// Entrada de mercancia. Usa transaccion PDO.
// Traducido de AbastecimientoService.java, con las reglas del manual.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/inventario.php';   // sumarOCrearStock, verificarEstadoAgotado
require_once __DIR__ . '/productos.php';     // nombreEstadoProducto

// Registra una entrada completa con sus detalles, dentro de una transaccion.
// $detalles: lista de ['id_Producto','id_Talla','id_Color','cantidad','precio_unitario'].
function registrarEntrada($pdo, $idProveedor, $idEmpleado, $detalles) {
    if (empty($detalles)) {
        throw new Exception('Debe agregar al menos un producto a la entrada');
    }

    // Validaciones previas (antes de tocar la base de datos)
    foreach ($detalles as $d) {
        $producto = buscarProducto($pdo, (int)$d['id_Producto']);
        if (!$producto) {
            throw new Exception('Uno de los productos no existe');
        }
        // No se puede ingresar mercancia de un producto descontinuado
        if (nombreEstadoProducto($pdo, (int)$d['id_Producto']) === 'Descontinuado') {
            throw new Exception('No se puede ingresar mercancía de un producto descontinuado: ' . $producto['Nombre']);
        }
        // El proveedor de la entrada debe coincidir con el del producto
        if ((int)$producto['id_Proveedor'] !== (int)$idProveedor) {
            throw new Exception('El proveedor seleccionado no coincide con el proveedor del producto: ' . $producto['Nombre']);
        }
        if ((int)$d['cantidad'] <= 0) {
            throw new Exception('La cantidad debe ser mayor a cero');
        }
        if ((float)$d['precio_unitario'] <= 0) {
            throw new Exception('El precio unitario debe ser mayor a cero');
        }
    }

    $pdo->beginTransaction();
    try {
        // Cabecera de la entrada
        $pdo->prepare("INSERT INTO entrada_mercancia (Fecha, id_Empleado, id_Proveedor)
                       VALUES (NOW(), :emp, :prov)")
            ->execute([':emp' => $idEmpleado, ':prov' => $idProveedor]);
        $idEntrada = (int)$pdo->lastInsertId();

        $stmtDetalle = $pdo->prepare("INSERT INTO detalle_entrada
                                        (Cantidad, Precio_unitario, id_Entrada, id_Inventario)
                                      VALUES (:cant, :precio, :entrada, :inv)");

        foreach ($detalles as $d) {
            // Crea la combinacion talla/color si no existe y suma el stock
            $idInventario = sumarOCrearStock(
                $pdo, (int)$d['id_Producto'], (int)$d['id_Talla'], (int)$d['id_Color'], (int)$d['cantidad']
            );
            $stmtDetalle->execute([
                ':cant'    => (int)$d['cantidad'],
                ':precio'  => $d['precio_unitario'],
                ':entrada' => $idEntrada,
                ':inv'     => $idInventario,
            ]);
        }

        $pdo->commit();
        return $idEntrada;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Lista las entradas con su proveedor y empleado (historial)
function listarEntradas($pdo) {
    $sql = "SELECT em.id_Entrada, em.Fecha, pr.Nombre AS Proveedor,
                   CONCAT(e.Nombre, ' ', e.Apellido) AS Empleado
            FROM entrada_mercancia em
            JOIN proveedor pr ON pr.id_Proveedor = em.id_Proveedor
            JOIN empleados e  ON e.id_Empleado = em.id_Empleado
            ORDER BY em.Fecha DESC";
    return $pdo->query($sql)->fetchAll();
}
