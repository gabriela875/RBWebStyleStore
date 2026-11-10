# PHPMailer (instalado sin Composer)

Esta carpeta contiene **PHPMailer 6.9.1**, la librería que usa el sistema para enviar el correo de
recuperación de contraseña. Se instaló **manualmente, sin Composer** (solo los archivos necesarios).

- Sitio oficial: https://github.com/PHPMailer/PHPMailer
- Licencia: GNU LGPL 2.1 (ver archivo `LICENSE`)

## Archivos
- `src/PHPMailer.php` — clase principal
- `src/SMTP.php` — cliente SMTP
- `src/Exception.php` — excepciones de la librería
- `cargar.php` — incluye los tres archivos anteriores (reemplaza al autoloader de Composer)

## Cómo se usa en el proyecto
Desde `funciones/recuperacion.php` se hace `require_once libs/PHPMailer/cargar.php` y luego se usa
la clase `PHPMailer\PHPMailer\PHPMailer`. No hace falta Composer ni `vendor/`.

## Cómo actualizar (opcional)
Si en el futuro quieres otra versión, descarga de la página oficial los archivos `PHPMailer.php`,
`SMTP.php` y `Exception.php` de la carpeta `src/` y reemplázalos aquí.
