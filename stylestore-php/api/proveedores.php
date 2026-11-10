<?php
// Endpoint AJAX del modulo de proveedores. Devuelve JSON. Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../funciones/proveedores.php';

protegerApi('Administrador');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    $pdo = conectar();

    switch ($accion) {

        case 'listar':
            echo json_encode(['ok' => true, 'datos' => listarProveedores($pdo)]);
            break;

        case 'obtener':
            $p = buscarProveedor($pdo, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
            echo $p
                ? json_encode(['ok' => true, 'datos' => $p])
                : json_encode(['ok' => false, 'mensaje' => 'Proveedor no encontrado']);
            break;

        case 'crear':
        case 'editar':
            $datos = [
                'nombre'   => trim($_POST['nombre'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo'   => trim($_POST['correo'] ?? ''),
            ];
            // Validaciones de servidor
            $error = primerError([
                ['condicion' => requerido($datos['nombre']),                                      'mensaje' => 'El nombre es obligatorio'],
                ['condicion' => requerido($datos['telefono']) && soloNumeros($datos['telefono']), 'mensaje' => 'El teléfono solo puede contener números'],
                ['condicion' => emailValido($datos['correo']),                                    'mensaje' => 'El correo no tiene un formato válido'],
            ]);
            if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }

            if ($accion === 'crear') {
                registrarProveedor($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Proveedor registrado correctamente']);
            } else {
                $datos['id_Proveedor'] = (int)($_POST['id_Proveedor'] ?? 0);
                editarProveedor($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Proveedor actualizado correctamente']);
            }
            break;

        case 'eliminar':
            eliminarProveedor($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Proveedor eliminado correctamente']);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
