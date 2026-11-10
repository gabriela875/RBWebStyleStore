<?php
// Solicitar recuperacion de contrasena: el usuario ingresa su correo.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/validaciones.php';
require_once __DIR__ . '/funciones/recuperacion.php';

$mensajeError = null;
$mensajeExito = null;
$enlaceDev = null; // en modo desarrollo, enlace mostrado en pantalla

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');

    if (!emailValido($correo)) {
        $mensajeError = 'El correo no tiene un formato válido';
    } else {
        try {
            $pdo = conectar();
            $r = generarRecuperacion($pdo, $correo);

            // Por seguridad, siempre decimos lo mismo (exista o no el correo)
            $mensajeExito = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.';

            if ($r['estado'] === 'mostrar') {
                // Modo desarrollo: no hay correo configurado, mostramos el enlace
                $enlaceDev = $r['enlace'];
            } elseif ($r['estado'] === 'error') {
                $mensajeError = 'No se pudo enviar el correo. Configura el correo o activa el modo desarrollo.';
                $mensajeExito = null;
            }
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
    <title>StyleStore — Recuperar contraseña</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/vistas.css" />
</head>
<body class="login-simple">
    <div class="login-caja">
        <h1 class="login-titulo">Recuperar contraseña</h1>
        <p class="login-subtitulo">Ingresa tu correo y te enviaremos un enlace</p>

        <?php if ($mensajeExito): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if ($mensajeError): ?>
            <div class="login-error"><?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <?php if ($enlaceDev): ?>
            <div class="login-info-dev">
                <strong>Modo desarrollo (sin correo configurado):</strong><br />
                Usa este enlace para restablecer tu contraseña:<br />
                <a href="<?= htmlspecialchars($enlaceDev) ?>"><?= htmlspecialchars($enlaceDev) ?></a>
            </div>
        <?php endif; ?>

        <form method="post" action="recuperar.php">
            <div class="form-campo">
                <label class="form-label">Correo</label>
                <input type="email" name="correo" class="form-input"
                       value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required />
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario">Enviar enlace</button>
                <a class="btn-secundario" href="<?= $base ?>/index.php">Volver al login</a>
            </div>
        </form>
    </div>
</body>
</html>
