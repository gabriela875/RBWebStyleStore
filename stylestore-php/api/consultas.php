<?php
// Endpoint AJAX de consultas (reportes). Devuelve JSON. Cada rol ve lo suyo.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../funciones/consultas.php';

protegerApi(['Administrador', 'Vendedor', 'Bodeguero']);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$cargo  = empleadoActivoCargo();
$yo     = empleadoActivoId();

// Que consultas puede ver cada rol
$permisos = [
    'Administrador' => ['mis_ventas', 'todas_ventas', 'anulaciones', 'vencidas', 'entradas'],
    'Vendedor'      => ['mis_ventas'],
    'Bodeguero'     => ['entradas'],
];
if (!in_array($accion, $permisos[$cargo] ?? [], true)) {
    echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para esta consulta']);
    exit;
}

try {
    $pdo = conectar();

    switch ($accion) {
        case 'mis_ventas':
            // El vendedor ve las suyas; el admin tambien puede usar esta vista
            echo json_encode(['ok' => true, 'datos' => listarVentas($pdo, $yo)]);
            break;
        case 'todas_ventas':
            echo json_encode(['ok' => true, 'datos' => listarVentas($pdo)]);
            break;
        case 'anulaciones':
            echo json_encode(['ok' => true, 'datos' => listarAnulaciones($pdo)]);
            break;
        case 'vencidas':
            echo json_encode(['ok' => true, 'datos' => consultarVentasVencidas($pdo)]);
            break;
        case 'entradas':
            echo json_encode(['ok' => true, 'datos' => consultarEntradas($pdo)]);
            break;
        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
