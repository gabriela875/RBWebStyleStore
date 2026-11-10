<?php
// Endpoint AJAX del modulo de ventas. Devuelve JSON. Administrador y Vendedor.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../includes/validaciones.php';
require_once __DIR__ . '/../includes/errores.php';
require_once __DIR__ . '/../funciones/ventas.php';
require_once __DIR__ . '/../funciones/clientes.php';
require_once __DIR__ . '/../funciones/productos.php';
require_once __DIR__ . '/../funciones/inventario.php';

protegerApi(['Administrador', 'Vendedor']);

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$cargo  = empleadoActivoCargo();
$yo     = empleadoActivoId();

try {
    $pdo = conectar();

    switch ($accion) {

        // --- Datos para armar una venta ---
        case 'catalogos':
            echo json_encode([
                'ok' => true,
                'productos' => listarProductos($pdo),
                'tallas'    => tallas($pdo),
                'colores'   => colores($pdo),
                'metodos'   => $pdo->query("SELECT id_Metodo_Pago, Nombre FROM metodo_pago ORDER BY id_Metodo_Pago")->fetchAll(),
                'tiposDoc'  => listarTiposDocumento($pdo),
            ]);
            break;

        // --- Vincular cliente: buscar por documento o crear ---
        case 'buscar_cliente':
            $c = buscarClientePorDocumento($pdo, trim($_POST['documento'] ?? ''));
            echo $c
                ? json_encode(['ok' => true, 'datos' => $c])
                : json_encode(['ok' => false, 'mensaje' => 'No existe un cliente con ese documento']);
            break;

        case 'crear_cliente':
            $datos = [
                'id_Tipo_Doc' => (int)($_POST['id_Tipo_Doc'] ?? 0),
                'documento'   => trim($_POST['documento'] ?? ''),
                'nombre'      => trim($_POST['nombre'] ?? ''),
                'apellido'    => trim($_POST['apellido'] ?? ''),
                'correo'      => trim($_POST['correo'] ?? ''),
            ];
            $error = primerError([
                ['condicion' => $datos['id_Tipo_Doc'] > 0,                                        'mensaje' => 'Debe seleccionar el tipo de documento'],
                ['condicion' => requerido($datos['documento']) && soloNumeros($datos['documento']),'mensaje' => 'El documento solo puede contener números'],
                ['condicion' => requerido($datos['nombre']) && soloLetras($datos['nombre']),       'mensaje' => 'El nombre solo puede contener letras'],
                ['condicion' => requerido($datos['apellido']) && soloLetras($datos['apellido']),   'mensaje' => 'El apellido solo puede contener letras'],
                ['condicion' => $datos['correo'] === '' || emailValido($datos['correo']),          'mensaje' => 'El correo no tiene un formato válido'],
            ]);
            if ($error) { echo json_encode(['ok' => false, 'mensaje' => $error]); break; }
            $idCliente = registrarCliente($pdo, $datos);
            echo json_encode(['ok' => true, 'mensaje' => 'Cliente registrado', 'id_Cliente' => $idCliente]);
            break;

        // --- Crear la venta (estado pendiente) ---
        case 'crear_venta':
            $idCliente = (int)($_POST['id_Cliente'] ?? 0);
            if ($idCliente <= 0) { echo json_encode(['ok' => false, 'mensaje' => 'Debe vincular un cliente']); break; }
            $idVenta = crearVenta($pdo, $yo, $idCliente);
            echo json_encode(['ok' => true, 'id_Venta' => $idVenta]);
            break;

        // --- Buscar la combinacion de inventario por producto/talla/color ---
        case 'buscar_inventario':
            $inv = buscarCombinacion($pdo,
                (int)($_POST['id_Producto'] ?? 0), (int)($_POST['id_Talla'] ?? 0), (int)($_POST['id_Color'] ?? 0));
            echo $inv
                ? json_encode(['ok' => true, 'datos' => ['id_Inventario' => $inv['id_Inventario'], 'Disponible' => $inv['Stock'] - $inv['Stock_Reservado']]])
                : json_encode(['ok' => false, 'mensaje' => 'No existe inventario para esa combinación']);
            break;

        case 'agregar_detalle':
            agregarDetalle($pdo, (int)$_POST['id_Venta'], (int)$_POST['id_Inventario'], (int)$_POST['cantidad']);
            echo json_encode(['ok' => true, 'mensaje' => 'Producto agregado', 'venta' => resumenVenta($pdo, (int)$_POST['id_Venta'])]);
            break;

        case 'quitar_detalle':
            quitarDetalle($pdo, (int)$_POST['id_Venta'], (int)$_POST['id_Detalle']);
            echo json_encode(['ok' => true, 'mensaje' => 'Producto quitado', 'venta' => resumenVenta($pdo, (int)$_POST['id_Venta'])]);
            break;

        case 'registrar_pago':
            registrarPago($pdo, (int)$_POST['id_Venta'], (int)$_POST['id_Metodo_Pago'],
                $_POST['valor'] ?? '', trim($_POST['referencia'] ?? ''));
            echo json_encode(['ok' => true, 'mensaje' => 'Pago registrado', 'venta' => resumenVenta($pdo, (int)$_POST['id_Venta'])]);
            break;

        case 'resumen':
            echo json_encode(['ok' => true, 'venta' => resumenVenta($pdo, (int)$_POST['id_Venta'])]);
            break;

        case 'completar':
            completarVenta($pdo, (int)$_POST['id_Venta']);
            echo json_encode(['ok' => true, 'mensaje' => 'Venta completada correctamente']);
            break;

        case 'anular':
            // El vendedor solo anula ventas propias; el admin cualquiera
            $idVenta = (int)$_POST['id_Venta'];
            if ($cargo === 'Vendedor') {
                $v = buscarVenta($pdo, $idVenta);
                if (!$v) { echo json_encode(['ok' => false, 'mensaje' => 'Venta no encontrada']); break; }
                if ((int)$v['id_Empleado'] !== $yo) {
                    echo json_encode(['ok' => false, 'mensaje' => 'Solo puede anular sus propias ventas']); break;
                }
            }
            anularVenta($pdo, $idVenta, $_POST['motivo'] ?? '', $yo);
            echo json_encode(['ok' => true, 'mensaje' => 'Venta anulada correctamente']);
            break;

        // --- Historial ---
        case 'listar':
            // El admin ve todas; el vendedor solo las suyas
            $ventas = ($cargo === 'Administrador') ? listarVentas($pdo) : listarVentas($pdo, $yo);
            echo json_encode(['ok' => true, 'datos' => $ventas]);
            break;

        case 'anulaciones':
            if ($cargo !== 'Administrador') { echo json_encode(['ok' => false, 'mensaje' => 'Solo el Administrador consulta las anulaciones']); break; }
            echo json_encode(['ok' => true, 'datos' => listarAnulaciones($pdo)]);
            break;

        default:
            echo json_encode(['ok' => false, 'mensaje' => 'Acción no válida']);
    }

} catch (PDOException $e) {
    echo json_encode(respuestaErrorBD($e, 'ventas/' . $accion));
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}

// Resumen de una venta (cabecera + detalles + pagos) para refrescar la pantalla
function resumenVenta($pdo, $idVenta) {
    return [
        'cabecera' => buscarVenta($pdo, $idVenta),
        'detalles' => detallesVenta($pdo, $idVenta),
        'pagos'    => pagosVenta($pdo, $idVenta),
        'suma_pagos' => sumaPagos($pdo, $idVenta),
    ];
}
