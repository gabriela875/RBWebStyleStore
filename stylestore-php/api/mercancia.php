<?php
// Endpoint AJAX de entrada de mercancia. Devuelve JSON. Administrador y Bodeguero.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../funciones/mercancia.php';
require_once __DIR__ . '/../funciones/productos.php';
require_once __DIR__ . '/../funciones/proveedores.php';

protegerApi(['Administrador', 'Bodeguero']);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    $pdo = conectar();

    switch ($accion) {

        case 'catalogos':
            // Datos para armar la entrada: proveedores, productos, tallas y colores
            echo json_encode([
                'ok' => true,
                'proveedores' => listarProveedores($pdo),
                'productos'   => listarProductos($pdo),
                'tallas'      => tallas($pdo),
                'colores'     => colores($pdo),
            ]);
            break;

        case 'registrar':
            $idProveedor = (int)($_POST['id_Proveedor'] ?? 0);
            $detallesJson = $_POST['detalles'] ?? '[]';
            $detalles = json_decode($detallesJson, true);

            if ($idProveedor <= 0) { echo json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar el proveedor']); break; }
            if (!is_array($detalles) || count($detalles) === 0) {
                echo json_encode(['ok' => false, 'mensaje' => 'Debe agregar al menos un producto a la entrada']); break;
            }

            registrarEntrada($pdo, $idProveedor, empleadoActivoId(), $detalles);
            echo json_encode(['ok' => true, 'mensaje' => 'Entrada registrada correctamente']);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
