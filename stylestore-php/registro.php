<?php
// Registro de usuario nuevo desde el login.
// La cuenta se crea como Vendedor / Inactivo; el Administrador la activa.
require_once __DIR__ . '/includes/sesion.php';
require_once __DIR__ . '/includes/validaciones.php';
require_once __DIR__ . '/funciones/auth.php';

$mensajeError = null;
$mensajeExito = null;
$datos = ['nombre' => '', 'apellido' => '', 'documento' => '', 'correo' => '', 'usuario' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'nombre'     => trim($_POST['nombre'] ?? ''),
        'apellido'   => trim($_POST['apellido'] ?? ''),
        'documento'  => trim($_POST['documento'] ?? ''),
        'correo'     => trim($_POST['correo'] ?? ''),
        'usuario'    => trim($_POST['usuario'] ?? ''),
        'contrasena' => $_POST['contrasena'] ?? '',
    ];

    // Validaciones en el servidor (mismas que en el cliente)
    $mensajeError = primerError([
        ['condicion' => requerido($datos['nombre']) && soloLetras($datos['nombre']),       'mensaje' => 'El nombre solo puede contener letras'],
        ['condicion' => requerido($datos['apellido']) && soloLetras($datos['apellido']),   'mensaje' => 'El apellido solo puede contener letras'],
        ['condicion' => requerido($datos['documento']) && soloNumeros($datos['documento']),'mensaje' => 'El documento solo puede contener números'],
        ['condicion' => emailValido($datos['correo']),                                     'mensaje' => 'El correo no tiene un formato válido'],
        ['condicion' => requerido($datos['usuario']),                                      'mensaje' => 'El usuario es obligatorio'],
        ['condicion' => longitudMinima($datos['contrasena'], 6),                           'mensaje' => 'La contraseña debe tener al menos 6 caracteres'],
    ]);

    if ($mensajeError === null) {
        try {
            $pdo = conectar();
            registrarUsuarioPublico($pdo, $datos);
            $mensajeExito = 'Cuenta creada. Un administrador debe activarla antes de que puedas iniciar sesión.';
            $datos = ['nombre' => '', 'apellido' => '', 'documento' => '', 'correo' => '', 'usuario' => ''];
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
    <title>StyleStore — Registro</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/login.css" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/vistas.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $base ?>/assets/js/notificaciones.js"></script>
    <script src="<?= $base ?>/assets/js/validaciones.js"></script>
</head>
<body class="login-simple">
    <div class="login-caja">
        <h1 class="login-titulo">Crear cuenta</h1>
        <p class="login-subtitulo">Regístrate para solicitar acceso como vendedor</p>

        <?php if ($mensajeExito): ?>
            <div class="mensaje-exito"><?= htmlspecialchars($mensajeExito) ?></div>
        <?php endif; ?>
        <?php if ($mensajeError): ?>
            <div class="login-error"><?= htmlspecialchars($mensajeError) ?></div>
        <?php endif; ?>

        <form method="post" action="registro.php" id="form-registro" novalidate>
            <div class="form-grid">
                <div class="form-campo">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" class="form-input" oninput="filtrarSoloLetras(this)"
                           value="<?= htmlspecialchars($datos['nombre']) ?>" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" class="form-input" oninput="filtrarSoloLetras(this)"
                           value="<?= htmlspecialchars($datos['apellido']) ?>" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Número de documento</label>
                    <input type="text" name="documento" class="form-input" oninput="filtrarSoloNumeros(this)"
                           value="<?= htmlspecialchars($datos['documento']) ?>" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-input"
                           value="<?= htmlspecialchars($datos['correo']) ?>" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-input"
                           value="<?= htmlspecialchars($datos['usuario']) ?>" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" class="form-input" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario">Registrarme</button>
                <a class="btn-secundario" href="<?= $base ?>/index.php">Volver al login</a>
            </div>
        </form>
    </div>

    <script>
    // Validacion en el cliente antes de enviar
    document.getElementById('form-registro').addEventListener('submit', function (e) {
        const f = this;
        const error = primerError([
            { condicion: esRequerido(f.nombre.value) && esSoloLetras(f.nombre.value), mensaje: 'El nombre solo puede contener letras' },
            { condicion: esRequerido(f.apellido.value) && esSoloLetras(f.apellido.value), mensaje: 'El apellido solo puede contener letras' },
            { condicion: esRequerido(f.documento.value) && esSoloNumeros(f.documento.value), mensaje: 'El documento solo puede contener números' },
            { condicion: esEmailValido(f.correo.value), mensaje: 'El correo no tiene un formato válido' },
            { condicion: esRequerido(f.usuario.value), mensaje: 'El usuario es obligatorio' },
            { condicion: tieneLongitudMinima(f.contrasena.value, 6), mensaje: 'La contraseña debe tener al menos 6 caracteres' },
        ]);
        if (error) { e.preventDefault(); alertaError(error); }
    });
    </script>
</body>
</html>
