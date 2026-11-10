<?php
// Logica de recuperacion de contrasena.
// El token se guarda SOLO como hash; expira a los 30 minutos y es de un solo uso.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../libs/PHPMailer/cargar.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

define('MINUTOS_EXPIRA_TOKEN', 30);

// Genera un token de recuperacion para un correo. Devuelve un arreglo:
//   ['estado' => 'enviado'|'mostrar'|'noexiste', 'enlace' => '...']
// Por seguridad, si el correo no existe NO se revela: se responde igual que si existiera,
// pero internamente marcamos 'noexiste' para no crear token.
function generarRecuperacion($pdo, $correo) {
    // Buscar empleado activo por correo
    $stmt = $pdo->prepare("SELECT id_Empleado, Nombre FROM empleados WHERE Correo = :correo");
    $stmt->execute([':correo' => $correo]);
    $empleado = $stmt->fetch();

    if (!$empleado) {
        return ['estado' => 'noexiste', 'enlace' => null];
    }

    // Token aleatorio: se envia el token en claro, se guarda solo su hash
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $expira = date('Y-m-d H:i:s', time() + MINUTOS_EXPIRA_TOKEN * 60);

    $pdo->prepare("INSERT INTO password_reset (id_Empleado, Token_hash, Expira, Usado)
                   VALUES (:id, :hash, :expira, 0)")
        ->execute([':id' => $empleado['id_Empleado'], ':hash' => $tokenHash, ':expira' => $expira]);

    $base = defined('URL_BASE') ? URL_BASE : '';
    $enlace = $base . '/restablecer.php?token=' . $token;

    // Intentar enviar el correo
    $enviado = enviarCorreoRecuperacion($correo, $empleado['Nombre'], $enlace);

    if ($enviado) {
        return ['estado' => 'enviado', 'enlace' => null];
    }

    // Si fallo el envio y estamos en modo desarrollo, devolvemos el enlace para mostrarlo
    if (defined('MODO_DESARROLLO') && MODO_DESARROLLO) {
        return ['estado' => 'mostrar', 'enlace' => $enlace];
    }

    // En produccion sin correo: no se puede continuar
    return ['estado' => 'error', 'enlace' => null];
}

// Envia el correo con PHPMailer. Devuelve true si se envio, false si fallo.
function enviarCorreoRecuperacion($para, $nombre, $enlace) {
    // Si no hay configuracion de correo, no intentamos (fallara a modo desarrollo)
    if (!defined('CORREO_USUARIO') || CORREO_USUARIO === '' || CORREO_USUARIO === 'tucorreo@gmail.com') {
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = CORREO_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = CORREO_USUARIO;
        $mail->Password   = CORREO_CONTRASENA;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = CORREO_PUERTO;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(CORREO_REMITENTE, CORREO_NOMBRE);
        $mail->addAddress($para, $nombre);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de contraseña - StyleStore';
        $mail->Body = '<p>Hola ' . htmlspecialchars($nombre) . ',</p>'
            . '<p>Solicitaste restablecer tu contraseña. Haz clic en el siguiente enlace '
            . '(válido por 30 minutos y de un solo uso):</p>'
            . '<p><a href="' . $enlace . '">' . $enlace . '</a></p>'
            . '<p>Si no fuiste tú, ignora este mensaje.</p>';
        $mail->AltBody = 'Para restablecer tu contraseña abre este enlace (válido 30 min): ' . $enlace;

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        return false; // el modo desarrollo mostrara el enlace en pantalla
    }
}

// Valida un token recibido por URL. Devuelve el id_Reset/id_Empleado si es valido, o null.
function validarToken($pdo, $token) {
    if (!$token) {
        return null;
    }
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare("SELECT id_Reset, id_Empleado FROM password_reset
                           WHERE Token_hash = :hash AND Usado = 0 AND Expira > NOW()");
    $stmt->execute([':hash' => $tokenHash]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// Cambia la contrasena usando un token valido y lo marca como usado (un solo uso).
function restablecerConToken($pdo, $token, $nuevaContrasena) {
    $reset = validarToken($pdo, $token);
    if (!$reset) {
        throw new Exception('El enlace no es válido o ya expiró. Solicita uno nuevo.');
    }

    $pdo->beginTransaction();
    try {
        // Actualizar la contrasena del empleado
        $pdo->prepare("UPDATE empleados SET Contrasena = :hash WHERE id_Empleado = :id")
            ->execute([
                ':hash' => password_hash($nuevaContrasena, PASSWORD_DEFAULT),
                ':id'   => $reset['id_Empleado'],
            ]);
        // Marcar el token como usado
        $pdo->prepare("UPDATE password_reset SET Usado = 1 WHERE id_Reset = :id")
            ->execute([':id' => $reset['id_Reset']]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
