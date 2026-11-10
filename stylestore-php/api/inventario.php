<?php
// Endpoint AJAX del modulo de inventario. Devuelve JSON.
// Consulta: Administrador y Bodeguero. Ajuste manual: solo Administrador.
// El Bodeguero puede marcar agotado / descontinuar (reglas del manual).
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/errores.php';
require_once __DIR__ . '/../funciones/inventario.php';

protegerApi(['Administrador', 'Bodeguero']);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$cargo  = empleadoActivoCargo();

try {
    $pdo = conectar();

    switch ($accion) {

        case 'consultar':
            // Lista las combinaciones de un producto
            $idProducto = (int)($_POST['id_Producto'] ?? $_GET['id_Producto'] ?? 0);
            if ($idProducto <= 0) { echo json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar un producto']); break; }
            echo json_encode(['ok' => true, 'datos' => listarInventarioPorProducto($pdo, $idProducto)]);
            break;

        case 'ajustar':
            // Solo el Administrador registra ajustes
            if ($cargo !== 'Administrador') {
                echo json_encode(['ok' => false, 'mensaje' => 'Solo el Administrador puede registrar ajustes']); break;
            }
            $idInventario = (int)($_POST['id_Inventario'] ?? 0);
            $cantidad     = $_POST['cantidad'] ?? '';
            $motivo       = trim($_POST['motivo'] ?? '');
            $error = primerError([
                ['condicion' => $idInventario > 0,              'mensaje' => 'Debe indicar el ID de inventario'],
                ['condicion' => is_numeric($cantidad) && (int)$cantidad != 0, 'mensaje' => 'La cantidad ajustada debe ser un número distinto de cero'],
                ['condicion' => requerido($motivo),             'mensaje' => 'El motivo es obligatorio'],
            ]);
            if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
            registrarAjuste($pdo, $idInventario, (int)$cantidad, $motivo, empleadoActivoId());
            echo json_encode(['ok' => true, 'mensaje' => 'Ajuste registrado correctamente']);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(respuestaErrorBD($e, 'inventario/' . $accion));
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
