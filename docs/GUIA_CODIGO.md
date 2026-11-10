# Guía del código — StyleStore PHP

Este documento explica, en lenguaje simple, qué hace cada archivo del proyecto PHP y cómo se
conectan entre sí. Se va llenando a medida que se construye cada módulo, para que puedas explicarlo
en la sustentación.

> Idea general del proyecto: **las páginas** (en `admin/`, `vendedor/`, `bodeguero/`) muestran la
> interfaz. Cuando el usuario hace algo (guardar, editar, borrar) sin recargar, el JavaScript llama
> a un archivo de **`api/`** que responde en formato JSON. Ese archivo de `api/` usa las
> **funciones** de `funciones/` para hacer el trabajo de verdad (consultas y reglas). Las piezas
> comunes (sesión, roles, validaciones, encabezado, pie) están en **`includes/`**.
>
> Resumen del flujo: **página → (JavaScript fetch) → api/ → funciones/ → base de datos**.

---

## Base de datos (`stylestore-php/database/`)

### `bd_stylestore.sql`
Es el script que crea la base de datos `bd_stylestore` con todas sus tablas, las llaves foráneas,
los datos de catálogo (cargos, categorías, tallas, colores, estados, etc.) y la vista
`vista_inventario_productos`. Se importa una sola vez desde phpMyAdmin. Está basado en el script
original, con las correcciones explicadas en `CAMBIOS_BD.md`.

**Tablas principales:** `empleados` (los usuarios del sistema con su cargo y estado),
`clientes`, `proveedor`, `producto`, `inventario` (stock por talla y color), `venta`,
`detalle_venta`, `pago`, `anulacion_venta`, `entrada_mercancia`, `detalle_entrada`,
`ajuste_inventario`, `descuento`, `password_reset` (para recuperar contraseña). El resto son
tablas de catálogo (listas fijas como cargos, estados, métodos de pago, etc.).

### `seed.php`
Crea los usuarios de prueba (uno por rol). Se abre una sola vez en el navegador. Guarda las
contraseñas cifradas con `password_hash`. **Se debe borrar después de usarlo.** Usa la conexión de
`config/conexion.php`. Usuarios que crea:
- `admin` / `admin123` (Administrador)
- `vendedor` / `vendedor123` (Vendedor)
- `bodeguero` / `bodeguero123` (Bodeguero)

### `CAMBIOS_BD.md`
Explica, uno por uno, los cambios hechos al script original (INSERT de empleados arreglado, campo
Correo, tabla de recuperación, normalización de `detalle_entrada`, etc.) y por qué. También deja
constancia de que el resto del modelo ya está en 3FN.

---

## Configuración (`stylestore-php/config/`)

### `conexion.example.php`
Plantilla de configuración. Se copia como `conexion.php` (que no se sube al repositorio). Define
los datos de la base de datos, el correo para recuperación de contraseña, el modo desarrollo y la
función `conectar()`, que devuelve la conexión PDO que usan todas las funciones para hablar con la
base de datos.

---

## Piezas comunes (`stylestore-php/includes/`)

Son archivos que se reutilizan en muchas páginas, para no repetir código.

### `sesion.php`
Maneja la sesión del empleado. Al iniciar sesión guarda en `$_SESSION` su id, usuario, nombre,
apellido y cargo. Controla el **cierre por inactividad a los 30 minutos**: cada vez que se carga una
página protegida, compara la hora actual con la última actividad; si pasaron más de 30 minutos,
cierra la sesión. También tiene funciones rápidas para saber quién está logueado y con qué cargo.

### `proteger.php`
Es el "portero" del sistema. Tiene dos funciones principales:
- `protegerPagina($cargos)`: se pone al inicio de cada página. Si no hay sesión, redirige al login;
  si el cargo no tiene permiso, lo manda a su propio dashboard.
- `protegerApi($cargos)`: igual, pero para los archivos de `api/`; en vez de redirigir, responde un
  JSON de error. Así ninguna acción se puede ejecutar sin el rol correcto.

### `validaciones.php`
Funciones de validación **en el servidor**: solo letras, solo números, email válido, fecha válida,
campo obligatorio, longitud mínima (contraseñas) y mayor que cero (precios). Son las mismas reglas
que en `assets/js/validaciones.js`, para validar dos veces (cliente y servidor).

### `encabezado.php` y `pie.php`
Arman la estructura visual común: el `encabezado.php` dibuja la barra superior (logo, nombre del
empleado, botón de cerrar sesión) y el **menú lateral según el cargo** (cada rol ve solo sus
opciones, igual que en el proyecto original). El `pie.php` cierra las etiquetas. Cada página del
sistema empieza incluyendo el encabezado y termina incluyendo el pie.

---

## Estilos y JavaScript (`stylestore-php/assets/`)

### `css/estilos.css`, `css/dashboard.css`, `css/login.css`, `css/vistas.css`
Son los estilos copiados del proyecto original y adaptados. Conservan el diseño: **paleta rosa
(`#C43670`) sobre crema (`#FBF4EB`)** y tipografías serif (Cormorant Garamond) y Jost. Los colores
están definidos como **variables CSS** (`:root`) para reutilizarlos. `estilos.css` tiene el layout
general (header, menú, tarjetas, formularios, tablas, botones); `dashboard.css` las tarjetas del
panel; `login.css` la pantalla de inicio de sesión; `vistas.css` las tablas y formularios de los
módulos.

### `js/notificaciones.js`
Envoltorio sobre **SweetAlert2** y **Toastify** para mostrar mensajes bonitos (éxito, error,
confirmaciones, pedir un texto). Nunca se usa `alert()`.

### `js/validaciones.js`
Validaciones **en el cliente** (las mismas que en el servidor) y un ayudante `enviarAccion()` que
hace las peticiones AJAX con `fetch` a los archivos de `api/` y devuelve el JSON de respuesta.
