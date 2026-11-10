<?php
// Inventario: consulta de stock por producto y ajustes manuales.
// Tambien expone funciones de stock que usan ventas y entradas de mercancia.
// Traducido de InventarioService.java / InventarioRepository.java.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/productos.php'; // para buscarEstadoProductoId / nombreEstadoProducto

// Lista las combinaciones (talla/color) de un producto, con stock disponible calculado.
function listarInventarioPorProducto($pdo, $idProducto) {
    $sql = "SELECT i.id_Inventario, t.Nombre AS Talla, co.Nombre AS Color,
                   i.Stock, i.Stock_Reservado,
                   (i.Stock - i.Stock_Reservado) AS Disponible
            FROM inventario i
            JOIN talla t  ON t.id_Talla = i.id_Talla
            JOIN color co ON co.id_Color = i.id_Color
            WHERE i.id_Producto = :id
            ORDER BY t.id_Talla, co.id_Color";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $idProducto]);
    return $stmt->fetchAll();
}

function buscarInventarioPorId($pdo, $idInventario) {
    $stmt = $pdo->prepare("SELECT * FROM inventario WHERE id_Inventario = :id");
    $stmt->execute([':id' => $idInventario]);
    return $stmt->fetch() ?: null;
}

// Busca una combinacion exacta producto/talla/color (o null)
function buscarCombinacion($pdo, $idProducto, $idTalla, $idColor) {
    $stmt = $pdo->prepare("SELECT * FROM inventario
                           WHERE id_Producto = :p AND id_Talla = :t AND id_Color = :c");
    $stmt->execute([':p' => $idProducto, ':t' => $idTalla, ':c' => $idColor]);
    return $stmt->fetch() ?: null;
}

// Catalogos para los selectores de inventario
function tallas($pdo) { return $pdo->query("SELECT id_Talla, Nombre FROM talla ORDER BY id_Talla")->fetchAll(); }
function colores($pdo) { return $pdo->query("SELECT id_Color, Nombre FROM color ORDER BY Nombre")->fetchAll(); }

// --- Reglas de stock (reutilizadas por ventas y entradas) ---

// Reserva stock al agregar un producto a una venta pendiente.
function reservarStock($pdo, $idInventario, $cantidad) {
    $inv = buscarInventarioPorId($pdo, $idInventario);
    if (!$inv) {
        throw new Exception('La combinación de inventario no existe');
    }
    $disponible = $inv['Stock'] - $inv['Stock_Reservado'];
    if ($disponible < $cantidad) {
        throw new Exception('No hay stock disponible suficiente para esa combinación');
    }
    $pdo->prepare("UPDATE inventario SET Stock_Reservado = Stock_Reservado + :c WHERE id_Inventario = :id")
        ->execute([':c' => $cantidad, ':id' => $idInventario]);
}

// Libera stock reservado (al quitar de una venta o anularla). No deja el reservado negativo.
function liberarStock($pdo, $idInventario, $cantidad) {
    $inv = buscarInventarioPorId($pdo, $idInventario);
    $nuevo = max(0, $inv['Stock_Reservado'] - $cantidad);
    $pdo->prepare("UPDATE inventario SET Stock_Reservado = :r WHERE id_Inventario = :id")
        ->execute([':r' => $nuevo, ':id' => $idInventario]);
}

// Descuenta stock definitivamente al completar una venta (baja total y reservado).
function descontarStock($pdo, $idInventario, $cantidad) {
    $inv = buscarInventarioPorId($pdo, $idInventario);
    $pdo->prepare("UPDATE inventario SET Stock = Stock - :c, Stock_Reservado = Stock_Reservado - :c
                   WHERE id_Inventario = :id")
        ->execute([':c' => $cantidad, ':id' => $idInventario]);
    verificarEstadoAgotado($pdo, $inv['id_Producto']);
}

// Repone stock (al anular una venta ya completada no aplica; se usa para reponer total).
function reponerStock($pdo, $idInventario, $cantidad) {
    $inv = buscarInventarioPorId($pdo, $idInventario);
    $pdo->prepare("UPDATE inventario SET Stock = Stock + :c WHERE id_Inventario = :id")
        ->execute([':c' => $cantidad, ':id' => $idInventario]);
    verificarEstadoAgotado($pdo, $inv['id_Producto']);
}

// Suma stock a una combinacion; si no existe, la crea (usado por entrada de mercancia).
// Devuelve el id_Inventario afectado.
function sumarOCrearStock($pdo, $idProducto, $idTalla, $idColor, $cantidad) {
    $inv = buscarCombinacion($pdo, $idProducto, $idTalla, $idColor);
    if ($inv) {
        $pdo->prepare("UPDATE inventario SET Stock = Stock + :c WHERE id_Inventario = :id")
            ->execute([':c' => $cantidad, ':id' => $inv['id_Inventario']]);
        $id = $inv['id_Inventario'];
    } else {
        $pdo->prepare("INSERT INTO inventario (Stock, Stock_Reservado, id_Producto, id_Talla, id_Color)
                       VALUES (:stock, 0, :p, :t, :co)")
            ->execute([':stock' => $cantidad, ':p' => $idProducto, ':t' => $idTalla, ':co' => $idColor]);
        $id = (int)$pdo->lastInsertId();
    }
    verificarEstadoAgotado($pdo, $idProducto);
    return $id;
}

// Actualiza el estado del producto: Agotado si todas sus combinaciones tienen disponible 0;
// Disponible si hay alguna con disponible > 0. No toca productos Descontinuados.
function verificarEstadoAgotado($pdo, $idProducto) {
    if (nombreEstadoProducto($pdo, $idProducto) === 'Descontinuado') {
        return;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventario
                           WHERE id_Producto = :id AND (Stock - Stock_Reservado) > 0");
    $stmt->execute([':id' => $idProducto]);
    $hayDisponible = $stmt->fetchColumn() > 0;

    $nuevoEstado = $hayDisponible ? 'Disponible' : 'Agotado';
    $pdo->prepare("UPDATE producto SET id_Estado_Prod = :e WHERE id_Producto = :id")
        ->execute([':e' => buscarEstadoProductoId($pdo, $nuevoEstado), ':id' => $idProducto]);
}

// Registra un ajuste manual de inventario (solo el Administrador).
// No deja el stock negativo ni por debajo de lo reservado.
function registrarAjuste($pdo, $idInventario, $cantidadAjustada, $motivo, $idEmpleado) {
    $inv = buscarInventarioPorId($pdo, $idInventario);
    if (!$inv) {
        throw new Exception('La combinación de inventario no existe');
    }
    $resultante = $inv['Stock'] + $cantidadAjustada;
    if ($resultante < 0) {
        throw new Exception('El ajuste dejaría el stock en negativo');
    }
    if ($resultante < $inv['Stock_Reservado']) {
        throw new Exception('El ajuste no puede dejar el stock por debajo de las unidades reservadas');
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE inventario SET Stock = :s WHERE id_Inventario = :id")
            ->execute([':s' => $resultante, ':id' => $idInventario]);
        $pdo->prepare("INSERT INTO ajuste_inventario (Motivo, Cantidad_ajustada, Fecha, id_Inventario, id_Empleado)
                       VALUES (:motivo, :cant, NOW(), :inv, :emp)")
            ->execute([':motivo' => $motivo, ':cant' => $cantidadAjustada, ':inv' => $idInventario, ':emp' => $idEmpleado]);
        verificarEstadoAgotado($pdo, $inv['id_Producto']);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
