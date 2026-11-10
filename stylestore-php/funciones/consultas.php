<?php
// Consultas (reportes) por rol. Reutiliza funciones de ventas y mercancia.
// Traducido de ConsultaService.java.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/ventas.php';     // listarVentas, listarAnulaciones
require_once __DIR__ . '/mercancia.php';   // listarEntradas

// Ventas pendientes que llevan mas de 24 horas (regla del manual: expiran a las 24h)
function consultarVentasVencidas($pdo) {
    $sql = "SELECT v.id_Venta, v.Fecha_hora, v.Total,
                   CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                   CONCAT(e.Nombre,' ',e.Apellido) AS Empleado
            FROM venta v
            JOIN estado_venta ev ON ev.id_Estado_Venta = v.id_Estado_Venta
            JOIN clientes c      ON c.id_Cliente = v.id_Cliente
            JOIN empleados e     ON e.id_Empleado = v.id_Empleado
            WHERE ev.Nombre = 'Pendiente' AND v.Fecha_hora < (NOW() - INTERVAL 24 HOUR)
            ORDER BY v.Fecha_hora";
    return $pdo->query($sql)->fetchAll();
}

// Resumen de entradas por proveedor (historial de abastecimiento)
function consultarEntradas($pdo) {
    return listarEntradas($pdo);
}
