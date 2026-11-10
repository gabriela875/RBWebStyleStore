<?php
// Endpoint AJAX del modulo de productos. Devuelve JSON.
// Administrador: todas las acciones. Bodeguero: solo marcar agotado y descontinuar.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../funciones/productos.php';

// Exigir al menos sesion de Admin o Bodeguero; el detalle por accion se valida abajo
protegerApi(['Administrador', 'Bodeguero']);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$cargo  = empleadoActivoCargo();

// Acciones que el Bodeguero SI puede hacer; el resto son solo del Administrador
$accionesBodeguero = ['listar', 'obtener', 'marcar_agotado', 'descontinuar'];
if ($cargo === 'Bodeguero' && !in_array($accion, $accionesBodeguero, true)) {
    echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para esta acción']);
    exit;
}

try {
    $pdo = conectar();

    switch ($accion) {

        case 'listar':
            echo json_encode(['ok' => true, 'datos' => listarProductos($pdo)]);
            break;

        case 'obtener':
            $p = buscarProducto($pdo, (int)($_POST['id'] ?? $_GET['id'] ?? 0));
            echo $p
                ? json_encode(['ok' => true, 'datos' => $p])
                : json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado']);
            break;

        case 'crear':
        case 'editar':
            $datos = [
                'codigo'       => trim($_POST['codigo'] ?? ''),
                'nombre'       => trim($_POST['nombre'] ?? ''),
                'marca'        => trim($_POST['marca'] ?? ''),
                'id_Genero'    => (int)($_POST['id_Genero'] ?? 0),
                'id_Categoria' => (int)($_POST['id_Categoria'] ?? 0),
                'id_Proveedor' => (int)($_POST['id_Proveedor'] ?? 0),
            ];
            $reglas = [
                ['condicion' => requerido($datos['codigo']),       'mensaje' => 'El código es obligatorio'],
                ['condicion' => requerido($datos['nombre']),       'mensaje' => 'El nombre es obligatorio'],
                ['condicion' => requerido($datos['marca']),        'mensaje' => 'La marca es obligatoria'],
                ['condicion' => $datos['id_Genero'] > 0,           'mensaje' => 'Debe seleccionar un género'],
                ['condicion' => $datos['id_Categoria'] > 0,        'mensaje' => 'Debe seleccionar una categoría'],
                ['condicion' => $datos['id_Proveedor'] > 0,        'mensaje' => 'Debe seleccionar un proveedor'],
            ];

            if ($accion === 'crear') {
                $datos['precio'] = $_POST['precio'] ?? '';
                $reglas[] = ['condicion' => mayorQueCero($datos['precio']), 'mensaje' => 'El precio debe ser mayor a cero'];
                $error = primerError($reglas);
                if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
                registrarProducto($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Producto registrado correctamente']);
            } else {
                $datos['id_Producto'] = (int)($_POST['id_Producto'] ?? 0);
                $error = primerError($reglas);
                if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
                editarProducto($pdo, $datos);
                echo json_encode(['ok' => true, 'mensaje' => 'Producto actualizado correctamente']);
            }
            break;

        case 'actualizar_precio':
            $precio = $_POST['precio'] ?? '';
            if (!mayorQueCero($precio)) { echo json_encode(['ok' => false, 'mensaje' => 'El precio debe ser mayor a cero']); break; }
            actualizarPrecioProducto($pdo, (int)($_POST['id'] ?? 0), $precio);
            echo json_encode(['ok' => true, 'mensaje' => 'Precio actualizado correctamente']);
            break;

        case 'marcar_agotado':
            marcarAgotado($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Producto marcado como agotado']);
            break;

        case 'descontinuar':
            descontinuarProducto($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Producto descontinuado correctamente']);
            break;

        case 'eliminar':
            eliminarProducto($pdo, (int)($_POST['id'] ?? 0));
            echo json_encode(['ok' => true, 'mensaje' => 'Producto eliminado correctamente']);
            break;

        case 'registrar_descuento':
            $datos = [
                'id_Producto'  => (int)($_POST['id_Producto'] ?? 0),
                'id_Tipo_Desc' => (int)($_POST['id_Tipo_Desc'] ?? 0),
                'valor'        => $_POST['valor'] ?? '',
                'fecha_inicio' => trim($_POST['fecha_inicio'] ?? ''),
                'fecha_fin'    => trim($_POST['fecha_fin'] ?? ''),
            ];
            $error = primerError([
                ['condicion' => $datos['id_Producto'] > 0,     'mensaje' => 'Debe seleccionar un producto'],
                ['condicion' => $datos['id_Tipo_Desc'] > 0,    'mensaje' => 'Debe seleccionar el tipo de descuento'],
                ['condicion' => mayorQueCero($datos['valor']), 'mensaje' => 'El valor del descuento debe ser mayor a cero'],
                ['condicion' => fechaValida($datos['fecha_inicio']), 'mensaje' => 'La fecha de inicio no es válida'],
                ['condicion' => fechaValida($datos['fecha_fin']),    'mensaje' => 'La fecha de fin no es válida'],
            ]);
            if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
            registrarDescuento($pdo, $datos);
            echo json_encode(['ok' => true, 'mensaje' => 'Descuento registrado correctamente']);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'mensaje' => 'Error de base de datos']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
