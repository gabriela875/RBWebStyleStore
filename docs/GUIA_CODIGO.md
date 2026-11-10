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

---

## Login y acceso por rol

### `funciones/auth.php`
Es el corazón del inicio de sesión. Traduce la lógica del `AuthService` original:
- `buscarEmpleadoPorUsuario()`: busca al empleado por su usuario y trae también el nombre de su
  cargo y sus estados (activo/inactivo, bloqueado/desbloqueado) con una consulta preparada.
- `intentarLogin()`: hace las verificaciones en orden y, si algo falla, lanza el **mismo mensaje**
  que el proyecto original: usuario no existe, cuenta bloqueada, empleado inactivo, contraseña
  incorrecta. La contraseña se comprueba con `password_verify`.
- `registrarIntentoFallido()`: suma 1 al contador; al llegar a **5 intentos** bloquea la cuenta.
- `desbloquearCuenta()`: reinicia los intentos y quita el bloqueo (solo lo usa el Administrador).

### `index.php` (login)
La pantalla de inicio de sesión (diseño de dos paneles, igual al original). Cuando se envía el
formulario, llama a `intentarLogin()`. Si el login es correcto, guarda la sesión y **redirige al
dashboard del cargo** (Administrador, Vendedor o Bodeguero). Si falla, muestra el mensaje de error.
También tiene enlaces a registro y a recuperar contraseña, y avisa si la sesión se cerró por
inactividad.

### `logout.php`
Cierra la sesión y vuelve al login. Es el destino del botón "Cerrar sesión" del encabezado.

### `admin/dashboard.php`, `vendedor/dashboard.php`, `bodeguero/dashboard.php`
Son los paneles de inicio de cada rol. Cada uno está **protegido**: si entra alguien sin el cargo
correcto, se le redirige a su propio dashboard. Muestran un saludo y tarjetas de acceso rápido a los
módulos que ese rol puede usar (igual que en el proyecto original).

> Flujo del login: `index.php` → `funciones/auth.php` (verifica) → guarda sesión
> (`includes/sesion.php`) → redirige al dashboard según el cargo.

---

## Registro de usuario

### `registro.php`
Página pública (se llega desde el login). El visitante llena nombre, apellido, documento, correo,
usuario y contraseña. Valida en el cliente y en el servidor (solo letras, solo números, email,
contraseña ≥ 6). Si todo está bien, llama a `registrarUsuarioPublico()` de `funciones/auth.php`,
que crea la cuenta con **cargo Vendedor** y **estado Inactivo**. El mensaje final le avisa que un
administrador debe activarla antes de poder entrar.

---

## Recuperar contraseña

### `libs/PHPMailer/`
Es la librería **PHPMailer** (instalada sin Composer) que envía el correo. `cargar.php` incluye sus
tres archivos; así se usa sin necesidad de `vendor/`.

### `funciones/recuperacion.php`
La lógica de recuperación:
- `generarRecuperacion()`: busca al empleado por su correo; si existe, crea un **token aleatorio**,
  guarda **solo su hash** en la tabla `password_reset` con vencimiento a 30 minutos y un solo uso.
  Luego intenta enviar el correo con PHPMailer. Si no hay correo configurado o falla y estamos en
  **modo desarrollo**, devuelve el enlace para mostrarlo en pantalla. Por seguridad, nunca revela si
  el correo existe o no.
- `validarToken()`: comprueba que el token (por su hash) exista, no esté usado y no haya expirado.
- `restablecerConToken()`: cambia la contraseña (con `password_hash`) y marca el token como usado,
  todo dentro de una transacción.

### `recuperar.php`
Pantalla donde el usuario escribe su correo. Si está en modo desarrollo y no hay correo, muestra el
enlace directamente.

### `restablecer.php`
Pantalla a la que lleva el enlace. Valida el token; si sirve, pide la nueva contraseña (dos veces,
mínimo 6 caracteres) y la guarda. Si el token no sirve o expiró, ofrece pedir uno nuevo.

---

## CRUD de Usuarios (empleados) — Administrador

### `funciones/empleados.php`
Contiene todas las operaciones sobre empleados (traducidas del `EmpleadoService` original):
- `listarEmpleados()` / `buscarEmpleado()` / `listarCargos()`: consultas para la tabla y el formulario.
- `registrarEmpleado()`: valida documento, usuario y correo únicos; crea al empleado Activo y
  Desbloqueado con la contraseña cifrada.
- `editarEmpleado()`: actualiza los datos validando que no choquen con otros empleados.
- `desactivarEmpleado()`: **no deja desactivar** si el empleado tiene ventas pendientes (mensaje del
  original). `activarEmpleado()` lo reactiva.
- `eliminarEmpleado()`: **borra de verdad solo si no tiene ventas, entradas ni ajustes**; si los
  tiene, en su lugar lo **desactiva** (y avisa de ello).
- El desbloqueo reutiliza `desbloquearCuenta()` de `auth.php`.

### `api/empleados.php`
Recibe las acciones AJAX (`listar`, `obtener`, `crear`, `editar`, `activar`, `desactivar`,
`desbloquear`, `eliminar`) y responde en JSON. Está **protegido solo para Administrador**: cualquier
otro rol recibe un error. Valida de nuevo los datos en el servidor antes de guardar.

### `admin/empleados.php`
La página con dos partes: un **formulario** (que sirve para crear y para editar) y la **tabla** del
personal. Todo funciona **sin recargar**: el JavaScript llama a `api/empleados.php` con `fetch`,
muestra los mensajes con SweetAlert2/Toastify y vuelve a cargar la tabla. Los botones cambian según
el estado del empleado (Activar/Desactivar, Desbloquear si está bloqueado, Editar, Eliminar).

> Flujo del módulo: `admin/empleados.php` (pantalla) → `api/empleados.php` (recibe la acción) →
> `funciones/empleados.php` (consulta y reglas) → base de datos.

---

## CRUD de Proveedores — Administrador

### `funciones/proveedores.php`
Operaciones sobre proveedores (traducidas del `ProveedorService` original):
- `listarProveedores()` / `buscarProveedor()`: para la tabla y la edición.
- `registrarProveedor()`: valida **nombre único** y **correo único** (mensajes del original).
- `editarProveedor()`: valida unicidad contra los demás proveedores.
- `eliminarProveedor()`: **no permite** eliminar si el proveedor tiene productos asociados.

### `api/proveedores.php`
Recibe las acciones AJAX (`listar`, `obtener`, `crear`, `editar`, `eliminar`) en JSON. Protegido
solo para Administrador. Valida en el servidor: nombre obligatorio, teléfono solo números, correo
con formato válido.

### `admin/proveedores.php`
Una sola página con el formulario (crear/editar) y la tabla, con botones de **editar** y
**eliminar** que funcionan sin recargar (fetch). Los mensajes se muestran con notificaciones.

---

## CRUD de Productos — Administrador (y Bodeguero para estados)

### `funciones/productos.php`
Operaciones sobre productos (traducidas del `ProductoService` original):
- `listarProductos()` / `buscarProducto()` / `catalogosProducto()`: datos para la tabla y los
  selectores (categorías, géneros, proveedores, tipos de descuento).
- `registrarProducto()`: valida **código único** y **precio mayor a cero**; lo crea Disponible.
- `editarProducto()`: cambia nombre, marca, género, categoría y proveedor (no toca estado ni precio).
- `actualizarPrecioProducto()`: cambia solo el precio base (debe ser > 0).
- `marcarAgotado()`: solo si está Disponible (mensajes del original).
- `descontinuarProducto()`: acción permanente; además **desactiva el descuento activo** del producto
  (dentro de una transacción).
- `eliminarProducto()`: solo si está **Descontinuado** y **sin ventas**.
- `registrarDescuento()` / `buscarDescuentoActivo()`: un solo descuento activo por producto, con
  fechas válidas y el valor que no puede igualar ni superar el precio (porcentaje < 100).

### `api/productos.php`
Recibe todas las acciones AJAX en JSON. El **Administrador** puede hacer todo; el **Bodeguero** solo
`marcar_agotado` y `descontinuar` (el resto se le niega). Valida los datos en el servidor.

### `admin/productos.php`
Una sola página con varias secciones: formulario de producto (crear/editar), **actualizar precio**,
**registrar descuento**, y el **catálogo** en tabla. Los botones de cada fila cambian según el
estado (Marcar agotado si está disponible, Descontinuar, Eliminar si está descontinuado). Todo por
fetch, con notificaciones.

---

## Validaciones y notificaciones (requisito transversal)
Cada formulario valida **en el cliente** (con `assets/js/validaciones.js`) antes de enviar, y **en
el servidor** (con `includes/validaciones.php`) dentro de cada `api/`. Las reglas: solo letras en
nombres/apellidos, solo números en documento/teléfono, email válido, fechas válidas, campos
obligatorios y contraseña mínima de 6. Todos los mensajes se muestran con **SweetAlert2** (ventana)
o **Toastify** (aviso de esquina); **nunca** con `alert()`.
