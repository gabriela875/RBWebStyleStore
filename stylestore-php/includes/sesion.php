<?php
// Manejo de la sesion del empleado que inicio sesion.
// Incluir este archivo al inicio de cada pagina protegida.

require_once __DIR__ . '/../config/conexion.php';

// Tiempo maximo de inactividad: 30 minutos (en segundos)
define('TIEMPO_INACTIVIDAD', 30 * 60);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guarda en la sesion los datos del empleado tras un login correcto
function iniciarSesionEmpleado($empleado) {
    $_SESSION['id_Empleado']  = $empleado['id_Empleado'];
    $_SESSION['usuario']      = $empleado['Usuario'];
    $_SESSION['nombre']       = $empleado['Nombre'];
    $_SESSION['apellido']     = $empleado['Apellido'];
    $_SESSION['cargo']        = $empleado['Cargo'];      // 'Administrador' | 'Vendedor' | 'Bodeguero'
    $_SESSION['ultima_actividad'] = time();
}

// Cierra la sesion por completo
function cerrarSesion() {
    $_SESSION = [];
    session_destroy();
}

// Verifica si hay un empleado logueado
function hayEmpleadoLogueado() {
    return isset($_SESSION['id_Empleado']);
}

// Cierra la sesion si pasaron mas de 30 minutos sin actividad.
// Devuelve true si la cerro por inactividad.
function verificarInactividad() {
    if (!hayEmpleadoLogueado()) {
        return false;
    }
    if (isset($_SESSION['ultima_actividad'])
        && (time() - $_SESSION['ultima_actividad']) > TIEMPO_INACTIVIDAD) {
        cerrarSesion();
        return true;
    }
    // Hubo actividad: renovar el contador
    $_SESSION['ultima_actividad'] = time();
    return false;
}

// Datos rapidos del empleado activo
function empleadoActivoId()     { return $_SESSION['id_Empleado'] ?? null; }
function empleadoActivoCargo()  { return $_SESSION['cargo'] ?? null; }
function empleadoActivoNombre() {
    return trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
}
