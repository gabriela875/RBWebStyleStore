<?php
// Pagina de login. Procesa el formulario y redirige al dashboard segun el cargo.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/proteger.php';
require_once __DIR__ . '/funciones/auth.php';

// Si ya hay sesion activa, ir directo al dashboard correspondiente
if (hayEmpleadoLogueado() && !verificarInactividad()) {
    header('Location: ' . dashboardDeCargo(empleadoActivoCargo()));
    exit;
}

$mensajeError = null;

// Aviso cuando la sesion se cerro por inactividad
if (isset($_GET['inactividad'])) {
    $mensajeError = 'Su sesión se cerró por inactividad. Inicie sesión nuevamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $contrasena === '') {
        $mensajeError = 'El usuario y la contraseña son obligatorios';
    } else {
        try {
            $pdo = conectar();
            $empleado = intentarLogin($pdo, $usuario, $contrasena);
            iniciarSesionEmpleado($empleado);
            header('Location: ' . dashboardDeCargo($empleado['Cargo']));
            exit;
        } catch (PDOException $e) {
            $mensajeError = 'Error de conexión con la base de datos';
        } catch (Exception $e) {
            $mensajeError = $e->getMessage();
        }
    }
}

$base = defined('URL_BASE') ? URL_BASE : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StyleStore — Acceso</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
</head>
<body>
    <div class="login-panel-izquierdo">
        <div class="login-marca">
            <div class="login-marca-nombre">Style<span>Store</span></div>
            <div class="login-marca-linea"></div>
            <div class="login-marca-slogan">Sistema de gestión</div>
        </div>
        <div class="login-decoracion">
            <div class="login-decoracion-circulo activo"></div>
            <div class="login-decoracion-circulo"></div>
            <div class="login-decoracion-circulo"></div>
        </div>
    </div>

    <div class="login-panel-derecho">
        <div class="login-form-contenedor">
            <h1 class="login-titulo">Bienvenido</h1>
            <p class="login-subtitulo">Ingresa tus credenciales para continuar</p>

            <?php if ($mensajeError): ?>
                <div class="login-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <form method="post" action="index.php">
                <div class="login-campo">
                    <label class="login-label">Usuario</label>
                    <input type="text" name="usuario" class="login-input"
                           placeholder="Ingresa tu usuario"
                           value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>" />
                </div>
                <div class="login-campo">
                    <label class="login-label">Contraseña</label>
                    <input type="password" name="contrasena" class="login-input" placeholder="••••••••" />
                </div>
                <button type="submit" class="login-btn">Ingresar</button>
            </form>

            <div class="login-enlaces">
                <a class="login-enlace" href="<?= $base ?>/registro.php">¿No tienes cuenta? Regístrate</a>
                <a class="login-enlace" href="<?= $base ?>/recuperar.php">¿Olvidaste tu contraseña?</a>
            </div>

            <div class="login-footer">StyleStore © 2026</div>
        </div>
    </div>
</body>
</html>
