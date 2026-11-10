<?php
// Plantilla de conexion. Copia este archivo como "conexion.php" y ajusta los valores.
// En XAMPP por defecto: usuario "root" y contrasena vacia.

// Datos de conexion a la base de datos
define('BD_HOST', 'localhost');
define('BD_PUERTO', 3306);     // XAMPP usa 3306 por defecto; cambialo si MySQL corre en otro puerto (ej. 3307)
define('BD_NOMBRE', 'bd_stylestore');
define('BD_USUARIO', 'root');
define('BD_CONTRASENA', ''); // XAMPP: vacia por defecto

// Modo desarrollo: si es true, muestra el enlace de recuperacion en pantalla
// cuando el envio de correo falla. En produccion debe ser false.
define('MODO_DESARROLLO', true);

// Configuracion del correo para PHPMailer (recuperar contrasena).
// Usa una contrasena de aplicacion de Gmail (no tu contrasena normal).
define('CORREO_HOST', 'smtp.gmail.com');
define('CORREO_PUERTO', 587);
define('CORREO_USUARIO', 'tucorreo@gmail.com');
define('CORREO_CONTRASENA', 'contrasena-de-aplicacion');
define('CORREO_REMITENTE', 'tucorreo@gmail.com');
define('CORREO_NOMBRE', 'StyleStore');

// URL base del sitio (para armar el enlace de recuperacion)
define('URL_BASE', 'http://localhost/stylestore-php');

// Devuelve una conexion PDO lista para usar
function conectar() {
    $dsn = 'mysql:host=' . BD_HOST . ';port=' . BD_PUERTO . ';dbname=' . BD_NOMBRE . ';charset=utf8mb4';
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, BD_USUARIO, BD_CONTRASENA, $opciones);
}
