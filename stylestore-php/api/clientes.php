<?php
// Endpoint AJAX del modulo de clientes. Devuelve JSON. Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../funciones/clientes.php';

protegerApi('Administrador');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    $pdo = conectar();

    switch ($accion) {

        case 'listar':
            echo json_encode(['ok' => true, 'datos' => listarClientes($pdo)]);
            break;

        case 'obtener':
            $c = buscarCliente($pdo, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
            echo $c
                ? json_encode(['ok' => true, 'datos' => $c])
                : json_encode(['ok' => false, 'mensaje' => 'Cliente no encontrado']);
            break;

        case 'editar':
            $datos = [
                'id_Cliente'  => (int)($_POST['id_Cliente'] ?? 0),
                'id_Tipo_Doc' => (int)($_POST['id_Tipo_Doc'] ?? 0),
                'nombre'      => trim($_POST['nombre'] ?? ''),
                'apellido'    => trim($_POST['apellido'] ?? ''),
                'correo'      => trim($_POST['correo'] ?? ''),
            ];
            $error = primerError([
                ['condicion' => $datos['id_Tipo_Doc'] > 0,                                       'mensaje' => 'Debe seleccionar el tipo de documento'],
                ['condicion' => requerido($datos['nombre']) && soloLetras($datos['nombre']),     'mensaje' => 'El nombre solo puede contener letras'],
                ['condicion' => requerido($datos['apellido']) && soloLetras($datos['apellido']), 'mensaje' => 'El apellido solo puede contener letras'],
                ['condicion' => $datos['correo'] === '' || emailValido($datos['correo']),        'mensaje' => 'El correo no tiene un formato válido'],
            ]);
            if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
            editarCliente($pdo, $datos);
            echo json_encode(['ok' => true, 'mensaje' => 'Cliente actualizado correctamente']);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
