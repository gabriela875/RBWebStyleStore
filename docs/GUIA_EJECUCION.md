# Guía de ejecución — StyleStore PHP en XAMPP (Windows)

Esta guía está pensada para alguien que **nunca ha ejecutado un proyecto PHP**. Sigue los pasos en
orden. Si algo falla, mira la sección **"Problemas comunes"** al final.

---

## Paso 1 — Instalar XAMPP y encender Apache y MySQL

1. Descarga XAMPP desde su página oficial (`https://www.apachefriends.org`) e instálalo. Déjalo en
   la carpeta que propone por defecto: `C:\xampp`.
2. Abre el programa **XAMPP Control Panel**.
3. Verás una lista de módulos. Haz clic en **Start** en:
   - **Apache** (el servidor web que ejecuta PHP).
   - **MySQL** (la base de datos).
4. Cuando ambos estén encendidos, sus nombres se verán resaltados en verde.

---

## Paso 2 — Dónde poner el proyecto y cuál es la URL

Apache solo puede mostrar lo que esté dentro de la carpeta `C:\xampp\htdocs`. Tienes dos opciones:

### Opción A (la más simple): copiar la carpeta
Copia la carpeta **`stylestore-php`** de este repositorio y pégala dentro de `C:\xampp\htdocs`.
Debe quedar así:

```
C:\xampp\htdocs\stylestore-php\index.php
```

La URL para abrirlo en el navegador será:

```
http://localhost/stylestore-php
```

### Opción B (avanzada): enlace con mklink
Si no quieres copiar y prefieres que apunte directo a tu repositorio, abre el **Símbolo del sistema
como Administrador** y crea un enlace de carpeta (junction):

```
mklink /J C:\xampp\htdocs\stylestore-php "C:\ruta\a\tu\repo\RBWebStyleStore\stylestore-php"
```

Cambia `C:\ruta\a\tu\repo` por la ruta real donde clonaste el repositorio. La URL sigue siendo
`http://localhost/stylestore-php`.

---

## Paso 3 — Crear la base de datos (importar el SQL)

1. Con Apache y MySQL encendidos, abre en el navegador:
   ```
   http://localhost/phpmyadmin
   ```
2. En el menú de arriba, haz clic en la pestaña **Importar** (Import).
3. Haz clic en **Seleccionar archivo** y elige:
   ```
   stylestore-php\database\bd_stylestore.sql
   ```
4. Baja hasta el final y haz clic en **Continuar** (Go).
5. Si todo salió bien, verás a la izquierda una base de datos llamada **`bd_stylestore`** con sus
   tablas. El script crea la base de datos solo si no existe, así que no necesitas crearla a mano.

---

## Paso 4 — Configurar la conexión a la base de datos

1. Ve a la carpeta `stylestore-php\config`.
2. Verás un archivo llamado `conexion.example.php`. **Haz una copia** y renómbrala exactamente a:
   ```
   conexion.php
   ```
3. Ábrela con el Bloc de notas. Para XAMPP, los valores por defecto ya son correctos:
   - Usuario: `root`
   - Contraseña: **vacía** (deja las comillas sin nada: `''`)

   ```php
   define('BD_USUARIO', 'root');
   define('BD_CONTRASENA', '');
   ```
4. Guarda el archivo. (Este `conexion.php` no se sube al repositorio por seguridad; por eso existe
   la plantilla `conexion.example.php`.)

---

## Paso 5 — Crear los usuarios de prueba (seed)

Las contraseñas se guardan cifradas, así que no se pueden escribir a mano en la base de datos. Para
crear usuarios de prueba, abre en el navegador:

```
http://localhost/stylestore-php/database/seed.php
```

Esto crea un usuario por cada rol. Las credenciales de prueba son:

| Rol           | Usuario     | Contraseña   |
|---------------|-------------|--------------|
| Administrador | `admin`     | `admin123`   |
| Vendedor      | `vendedor`  | `vendedor123`|
| Bodeguero     | `bodeguero` | `bodeguero123`|

> ⚠️ **Importante:** después de ejecutar el seed una vez, **borra el archivo `seed.php`** o
> renómbralo (por ejemplo a `seed.php.off`) para que nadie más pueda ejecutarlo. **Recuérdalo.**

Luego abre `http://localhost/stylestore-php` e inicia sesión con cualquiera de esos usuarios.

---

## Paso 6 — Configurar el correo (recuperar contraseña)

El sistema puede enviar un enlace de recuperación de contraseña por correo usando **PHPMailer**.
Esto es opcional para probar; si no lo configuras, funciona el **modo desarrollo** (ver abajo).

### Para enviar correos reales con Gmail
Gmail no deja usar tu contraseña normal; necesitas una **contraseña de aplicación**:

1. Entra a tu cuenta de Google → **Seguridad**.
2. Activa la **Verificación en dos pasos** (es requisito).
3. Busca **Contraseñas de aplicaciones**, crea una nueva (nombre libre, p. ej. "StyleStore") y
   copia la contraseña de 16 letras que te da.
4. Abre `stylestore-php\config\conexion.php` y completa:
   ```php
   define('CORREO_USUARIO', 'tucorreo@gmail.com');
   define('CORREO_CONTRASENA', 'las-16-letras-de-la-contrasena-de-aplicacion');
   define('CORREO_REMITENTE', 'tucorreo@gmail.com');
   ```

### Modo desarrollo (sin configurar correo)
En `conexion.php` existe esta línea:
```php
define('MODO_DESARROLLO', true);
```
Cuando está en `true` y el envío del correo falla (o no lo configuraste), el sistema **muestra el
enlace de recuperación directamente en la pantalla**, para que puedas probar el flujo sin correo.
En un entorno real, cámbiala a `false`.

---

## Problemas comunes y su solución

### Apache no inicia (puerto 80 ocupado)
Suele pasar si tienes Skype, IIS u otro programa usando el puerto 80.
- En el XAMPP Control Panel, haz clic en **Config** (de Apache) → **httpd.conf**.
- Busca `Listen 80` y cámbialo por `Listen 8080`. Busca también `ServerName localhost:80` y ponlo
  en `:8080`.
- Reinicia Apache. Ahora la URL será `http://localhost:8080/stylestore-php`.
- Alternativa rápida: cierra el programa que usa el puerto 80 (por ejemplo, desactiva en Skype la
  opción de usar los puertos 80/443).

### MySQL no inicia (puerto 3306 ocupado)
Pasa si ya tienes otro MySQL instalado como servicio de Windows.
- Abre **Servicios** de Windows (`services.msc`), busca un servicio llamado `MySQL` y detenlo.
- O cambia el puerto en XAMPP: **Config** (de MySQL) → **my.ini**, cambia `port=3306` por `3307`.
  Si lo cambias, pon ese mismo puerto en `conexion.php`:
  `define('BD_HOST', 'localhost:3307');`

### La página sale en blanco
Casi siempre es un error de PHP que no se está mostrando. Para verlo:
- Abre `C:\xampp\php\php.ini`, busca `display_errors` y ponlo en `On`:
  ```
  display_errors = On
  ```
- Busca también `error_reporting` y déjalo en `error_reporting = E_ALL`.
- Reinicia Apache. Vuelve a cargar la página: ahora verás el mensaje de error que indica la causa.
- Otra fuente útil es el registro de errores de Apache: `C:\xampp\apache\logs\error.log`.

### Error "could not find driver"
Significa que falta activar la extensión de MySQL para PDO.
- Abre `C:\xampp\php\php.ini`, busca la línea `;extension=pdo_mysql` y **quítale el punto y coma**
  del inicio para que quede `extension=pdo_mysql`.
- Guarda y **reinicia Apache**.

### Error de conexión a la base de datos
- Verifica que **MySQL esté encendido** en el panel de XAMPP.
- Revisa que en `conexion.php` el nombre de la base sea `bd_stylestore` y el usuario `root` con
  contraseña vacía.
- Confirma que importaste el SQL (Paso 3) y que la base `bd_stylestore` aparece en phpMyAdmin.
- Si cambiaste el puerto de MySQL, asegúrate de reflejarlo en `BD_HOST`.
