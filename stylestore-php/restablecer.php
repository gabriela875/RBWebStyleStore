<?php
// Definir una nueva contrasena usando el token recibido por el enlace.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/validaciones.php';
require_once __DIR__ . '/funciones/recuperacion.php';

$mensajeError = null;
$mensajeExito = null;
$tokenValido = false;

// El token llega por GET (al abrir el enlace) y se reenvia por POST (al guardar)
$token = $_POST['token'] ?? $_GET['token'] ?? '';

try {
    $pdo = conectar();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nueva  = $_POST['contrasena'] ?? '';
        $repite = $_POST['contrasena2'] ?? '';

        if (!longitudMinima($nueva, 6)) {
            $mensajeError = 'La contraseña debe tener al menos 6 caracteres';
            $tokenValido = validarToken($pdo, $token) !== null;
        } elseif ($nueva !== $repite) {
            $mensajeError = 'Las contraseñas no coinciden';
            $tokenValido = validarToken($pdo, $token) !== null;
        } else {
            restablecerConToken($pdo, $token, $nueva);
            $mensajeExito = 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.';
            $tokenValido = false; // ya se uso
        }
    } else {
        // Primera carga: validar que el token sirva
        $tokenValido = validarToken($pdo, $token) !== null;
        if (!$tokenValido) {
            $mensajeError = 'El enlace no es válido o ya expiró. Solicita uno nuevo.';
        }
    }
} catch (PDOException $e) {
    $mensajeError = 'Error de conexión con la base de datos';
} catch (Exception $e) {
    $mensajeError = $e->getMessage();
    $tokenValido = false;
}

$base = defined('URL_BASE') ? URL_BASE : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StyleStore — Nueva contraseña</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/vistas.css" />
</head>
<body class="login-simple">
    <div class="login-caja">
        <h1 class="login-titulo">Nueva contraseña</h1>

        <?php if ($mensajeExito): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($mensajeExito) ?></div>
            <div class="btn-grupo">
                <a class="btn-primario" href="<?= $base ?>/index.php">Ir al login</a>
            </div>
        <?php else: ?>
            <?php if ($mensajeError): ?>
                <div class="login-error"><?= htmlspecialchars($mensajeError) ?></div>
            <?php endif; ?>

            <?php if ($tokenValido): ?>
                <p class="login-subtitulo">Escribe tu nueva contraseña</p>
                <form method="post" action="restablecer.php" id="form-restablecer" novalidate>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
                    <div class="form-campo">
                        <label class="form-label">Nueva contraseña</label>
                        <input type="password" name="contrasena" class="form-input" />
                    </div>
                    <div class="form-campo">
                        <label class="form-label">Repetir contraseña</label>
                        <input type="password" name="contrasena2" class="form-input" />
                    </div>
                    <div class="btn-grupo">
                        <button type="submit" class="btn-primario">Guardar contraseña</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="btn-grupo">
                    <a class="btn-secundario" href="<?= $base ?>/recuperar.php">Solicitar nuevo enlace</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base ?>/assets/js/notificaciones.js"></script>
    <script src="<?= $base ?>/assets/js/validaciones.js"></script>
    <script>
    const f = document.getElementById('form-restablecer');
    if (f) {
        f.addEventListener('submit', function (e) {
            const error = primerError([
                { condicion: tieneLongitudMinima(f.contrasena.value, 6), mensaje: 'La contraseña debe tener al menos 6 caracteres' },
                { condicion: f.contrasena.value === f.contrasena2.value, mensaje: 'Las contraseñas no coinciden' },
            ]);
            if (error) { e.preventDefault(); alertaError(error); }
        });
    }
    </script>
</body>
</html>
