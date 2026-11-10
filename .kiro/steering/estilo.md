# Estilo de código — Migración PHP de StyleStore

Estas reglas aplican SIEMPRE al código de la carpeta `stylestore-php/`.

## Lenguaje y enfoque
- PHP 8 nativo con PDO. Nada de frameworks (sin Laravel, Symfony, etc.).
- Debe correr en XAMPP (Apache + MySQL/MariaDB).
- Frontend: HTML, CSS y JavaScript simple. Para notificaciones usar SweetAlert2 o Toastify, **nunca** `alert()`.

## NO traer conceptos de Java
- Prohibido crear beans, services, repositories, DTOs, getters/setters o clases de entidad.
- Se traduce la LÓGICA a PHP simple, no la ESTRUCTURA del proyecto Java.
- Evitar lo avanzado que cuesta entender: namespaces, autoloaders, inyección de dependencias, clases abstractas, patrones de diseño complejos.

## Organización de archivos
- `funciones/` → un archivo por módulo con sus consultas y reglas (ej. `funciones/proveedores.php`). Son funciones PHP sueltas, no clases.
- `admin/`, `vendedor/`, `bodeguero/` → páginas HTML+PHP por rol.
- `api/` → acciones AJAX que reciben POST y devuelven JSON.
- `includes/` → sesión, verificación de rol, validaciones, encabezado y pie comunes.
- `config/` → conexión PDO (`conexion.php` real ignorado por git; `conexion.example.php` versionado).
- `assets/css`, `assets/js` → estilos y scripts.
- `libs/` → librerías de terceros sin Composer (ej. PHPMailer).
- `database/` → script SQL final, cambios y seed.

## Reglas de código
- Consultas SIEMPRE preparadas con PDO (nunca concatenar valores en SQL).
- Funciones reutilizables para validar datos y verificar rol.
- Sesiones PHP para autenticación; `password_hash` / `password_verify` para contraseñas.
- Transacciones PDO donde haga falta (ventas, entradas de mercancía).
- Nombres en español, igual que el proyecto original (variables, funciones, archivos).
- Comentarios cortos en español SOLO donde se explique una regla de negocio o algo no obvio. No comentar lo evidente.

## Fidelidad al original
- Para cada módulo, leer primero su `service`, `repository` y `bean` en `referencia-jsf/` y traducir las reglas tal cual: mismas validaciones, mismos mensajes, mismas reglas del manual de usuario.
- Si algo del original no es claro, preguntar antes de cambiarlo.
- Conservar el diseño visual original (paleta y tipografía del CSS de `referencia-jsf`).

## Documentación para sustentación
- Al terminar cada módulo, agregar a `docs/GUIA_CODIGO.md` una explicación en lenguaje simple de qué hace cada archivo y cómo se conectan (página → función → api).
