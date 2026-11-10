<?php
// Protege paginas y endpoints por autenticacion y rol.
// Incluir despues de sesion.php.

require_once __DIR__ . '/sesion.php';

// Devuelve la URL base para redirigir al login (sube segun la carpeta)
function urlLogin() {
    return (defined('URL_BASE') ? URL_BASE : '') . '/index.php';
}

// Protege una PAGINA: exige sesion y, opcionalmente, uno o varios cargos.
// Si no cumple, redirige al login. Si expiro por inactividad, avisa con un parametro.
function protegerPagina($cargosPermitidos = null) {
    if (verificarInactividad()) {
        header('Location: ' . urlLogin() . '?inactividad=1');
        exit;
    }
    if (!hayEmpleadoLogueado()) {
        header('Location: ' . urlLogin());
        exit;
    }
    if ($cargosPermitidos !== null) {
        $cargos = is_array($cargosPermitidos) ? $cargosPermitidos : [$cargosPermitidos];
        if (!in_array(empleadoActivoCargo(), $cargos, true)) {
            // Rol incorrecto: lo mandamos a su propio dashboard
            header('Location: ' . dashboardDeCargo(empleadoActivoCargo()));
            exit;
        }
    }
}

// Protege un ENDPOINT de api/: exige sesion y cargo, respondiendo JSON si falla.
function protegerApi($cargosPermitidos = null) {
    header('Content-Type: application/json; charset=utf-8');
    if (verificarInactividad()) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'Sesión expirada por inactividad', 'expirado' => true]);
        exit;
    }
    if (!hayEmpleadoLogueado()) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'mensaje' => 'No ha iniciado sesión', 'expirado' => true]);
        exit;
    }
    if ($cargosPermitidos !== null) {
        $cargos = is_array($cargosPermitidos) ? $cargosPermitidos : [$cargosPermitidos];
        if (!in_array(empleadoActivoCargo(), $cargos, true)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'mensaje' => 'No tiene permiso para esta acción']);
            exit;
        }
    }
}

// URL del dashboard segun el cargo
function dashboardDeCargo($cargo) {
    $base = defined('URL_BASE') ? URL_BASE : '';
    switch ($cargo) {
        case 'Administrador': return $base . '/admin/dashboard.php';
        case 'Vendedor':      return $base . '/vendedor/dashboard.php';
        case 'Bodeguero':     return $base . '/bodeguero/dashboard.php';
        default:              return $base . '/index.php';
    }
}
