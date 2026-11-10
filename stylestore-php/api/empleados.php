<?php
// Endpoint AJAX del modulo de empleados. Devuelve JSON. Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../funciones/empleados.php';

protegerApi('Administrador');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

try {
    $pdo = conectar();

    switch ($accion) {

        case 'listar':
            echo json_encode(['ok' => true, 'datos' => listarEmpleados($pdo)]);
            break;

        case 'obtener':
            $emp = buscarEmpleado($pdo, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
            if (!$emp) {
                echo json_encode(['ok' => false, 'mensaje' => 'Empleado no encontrado']);
            } else {
                // No enviamos el hash de la contrasena al cliente
                unset($emp['Contrasena']);
                echo json_encode(['ok' => true, 'datos' => $emp]);
            }
            break;

        case 'crear':
        case 'editar':
            $datos = [
                'documento' => trim($_POST['documento'] ?? ''),
                'nombre'    => trim($_POST['nombre'] ?? ''),
                'apellido'  => trim($_POST['apellido'] ?? ''),
                'correo'    => trim($_POST['correo'] ?? ''),
                'usuario'   => trim($_POST['usuario'] ?? ''),
                'id_Cargo'  => (int)($_POST['id_Cargo'] ?? 0),
            ];

            // Validaciones de servidor comunes
            $reglas = [
                ['condicion' => requerido($datos['nombre']) && soloLetras($datos['nombre']),        'mensaje' => 'El nombre solo puede contener letras'],
                ['condicion' => requerido($datos['apellido']) && soloLetras($datos['apellido']),    'mensaje' => 'El apellido solo puede contener letras'],
                ['condicion' => requerido($datos['documento']) && soloNumeros($datos['documento']), 'mensaje' => 'El documento solo puede contener números'],
                ['condicion' => emailValido($datos['correo']),                                      'mensaje' => 'El correo no tiene un formato válido'],
                ['condicion' => requerido($datos['usuario']),                                       'mensaje' => 'El usuario es obligatorio'],
                ['condicion' => $datos['id_Cargo'] > 0,                                             'mensaje' => 'Debe seleccionar un cargo'],
            ];

            if ($accion === 'crear') {
                $datos['contrasena'] = $_POST['contrasena'] ?? '';
                $reglas[] = ['condicion' => longitudMinima($datos['contrasena'], 6), 'mensaje' => 'La contraseña debe tener al menos 6 caracteres'];
                $error = primerError($reglas);
                if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
                registrarEmpleado($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Empleado registrado correctamente']);
            } else {
                $datos['id_Empleado'] = (int)($_POST['id_Empleado'] ?? 0);
                $error = primerError($reglas);
                if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
                editarEmpleado($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Empleado actualizado correctamente']);
            }
            break;

        case 'activar':
            activarEmpleado($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Empleado activado correctamente']);
            break;

        case 'desactivar':
            desactivarEmpleado($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Empleado desactivado correctamente']);
            break;

        case 'desbloquear':
            desbloquearCuenta($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Cuenta desbloqueada correctamente']);
            break;

        case 'eliminar':
            $r = eliminarEmpleado($pdo, (int)($_POST['id'] ?? 0));
            if ($r === 'eliminado') {
                echo json_encode(['ok' => true, 'mensaje' => 'Empleado eliminado correctamente']);
            } else {
                echo json_encode(['ok' => true, 'mensaje' => 'El empleado tiene movimientos registrados, por lo que fue desactivado en lugar de eliminado']);
            }
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
