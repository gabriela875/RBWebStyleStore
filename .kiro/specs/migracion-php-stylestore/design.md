# Diseño — Migración de StyleStore a PHP

## Visión general

Reescritura de StyleStore (JSF) a **PHP 8 nativo + PDO + MySQL**, HTML/CSS/JS simple, sin
frameworks, para XAMPP. El proyecto JSF original se conserva intacto en `referencia-jsf/` como
material de consulta de reglas de negocio, flujos y estilos. El nuevo proyecto vive en
`stylestore-php/`.

Principio rector: **se traduce la lógica, no la estructura Java.** No hay beans, services,
repositories ni clases de entidad. En su lugar: funciones PHP por módulo, páginas por rol,
endpoints AJAX y includes comunes.

---

## Estructura del repositorio (después de la Tarea 0)

```
RBWebStyleStore/
├── .gitignore                      # ignora target/, *.class, .settings, vendor/, config/conexion.php
├── README.md                       # qué es, carpetas y cómo correr en XAMPP
├── docs/                           # material de referencia (NO se modifica, salvo GUIA_CODIGO.md)
│   ├── Manual_Usuario_StyleStore.pdf
│   ├── bd_stylestore.sql           # SQL original de referencia
│   ├── requisitos.md
│   └── GUIA_CODIGO.md              # se va llenando por módulo (para la sustentación)
│
├── referencia-jsf/                 # proyecto JSF original movido con git mv (solo consulta)
│   ├── pom.xml
│   ├── src/main/java/{bean,service,repository,model,util}
│   └── src/main/webapp/{admin,vendedor,bodeguero,resources/css,...}
│
└── stylestore-php/                 # NUEVO proyecto PHP
    ├── index.php                   # login
    ├── registro.php                # registro de usuario nuevo
    ├── recuperar.php               # solicitar enlace de recuperación
    ├── restablecer.php             # definir nueva contraseña con token
    ├── config/
    │   ├── conexion.example.php     # plantilla versionada (sin credenciales reales)
    │   └── conexion.php             # real, IGNORADO por git
    ├── includes/
    │   ├── sesion.php               # inicia sesión PHP, control de inactividad (30 min)
    │   ├── proteger.php             # verifica autenticación y rol de la página
    │   ├── validaciones.php         # validar letras, números, email, fecha, requeridos, longitud
    │   ├── encabezado.php           # cabecera + menú lateral según rol
    │   └── pie.php                  # pie común
    ├── funciones/
    │   ├── auth.php                 # login, intentos, bloqueo, registro, recuperación
    │   ├── empleados.php            # CRUD de empleados
    │   ├── proveedores.php          # CRUD de proveedores
    │   ├── productos.php            # CRUD de productos + precio + descuentos + estados
    │   ├── clientes.php             # consulta/edición de clientes
    │   ├── inventario.php           # consulta de stock, ajustes
    │   ├── mercancia.php            # entrada de mercancía (transacción)
    │   ├── ventas.php               # ventas, pagos, anulación (transacción)
    │   └── consultas.php            # reportes por rol
    ├── api/
    │   ├── proveedores.php          # acciones AJAX → JSON
    │   ├── productos.php
    │   ├── empleados.php
    │   ├── ... (uno por módulo con AJAX)
    ├── admin/
    │   ├── dashboard.php
    │   ├── empleados.php  proveedores.php  productos.php
    │   ├── inventario.php  clientes.php  ventas.php  consultas.php
    ├── vendedor/
    │   ├── dashboard.php  ventas.php  consultas.php
    ├── bodeguero/
    │   ├── dashboard.php  inventario.php  mercancia.php  consultas.php
    ├── assets/
    │   ├── css/   (login.css, dashboard.css, estilos.css, vistas.css adaptados del original)
    │   └── js/    (validaciones.js, notificaciones.js, y un js por módulo AJAX)
    ├── libs/
    │   └── PHPMailer/               # instalado manualmente sin Composer
    └── database/
        ├── bd_stylestore.sql        # script final (entrega)
        ├── CAMBIOS_BD.md            # qué se cambió y por qué
        └── seed.php                 # inserta un usuario por rol con password_hash
```

### Convención de los endpoints `api/`
Cada archivo de `api/` recibe un parámetro `accion` por POST, valida sesión y rol con
`includes/proteger.php`, llama a una función de `funciones/`, y responde `echo json_encode([...])`
con `{ ok: true/false, mensaje, datos }`. El JS del front usa `fetch` y muestra el resultado con
SweetAlert2 / Toastify.

---

## Capa de datos

- **Conexión** (`config/conexion.php`): una función `conectar()` que devuelve un objeto PDO con
  `ERRMODE_EXCEPTION`, charset `utf8mb4`, hacia la BD `bd_stylestore` en `localhost`.
  `conexion.example.php` es idéntico pero con credenciales de ejemplo (`root` / sin pass para XAMPP).
- **Todas** las consultas usan sentencias preparadas (`$pdo->prepare()` + parámetros).
- **Transacciones** PDO (`beginTransaction/commit/rollBack`) en ventas y entradas de mercancía.

### Cambios en la base de datos (detalle)

Se parte de `docs/bd_stylestore.sql` y se aplican SOLO estas correcciones (documentadas en
`database/CAMBIOS_BD.md`):

1. **INSERT de empleados roto.** El original:
   `INSERT INTO empleados VALUES (1,'12345678','Admin','StyleStore','admin','admin123', FALSE, 0, 1, 1);`
   La tabla tiene 10 columnas y el INSERT desalinea los valores: `Intentos_fallidos=FALSE`,
   `id_Cargo=0` (FK inexistente), y falta un valor. Se corrige con valores válidos
   (`Intentos_fallidos=0`, `id_Cargo=1` Administrador, `id_Estado_Emp=1` Activo, `id_Estado_Bloq=1`
   Desbloqueado) y contraseña con `password_hash` (vía `seed.php`).
2. **estado_bloqueo.Nombre → VARCHAR(20)** y se insertan `(1,'Desbloqueado'),(2,'Bloqueado')`
   (el código original compara con esos nombres exactos).
3. **detalle_entrada**: se elimina `id_Proveedor` y su FK (redundante con `entrada_mercancia`, rompe 3FN).
4. **empleados.Correo VARCHAR(100) UNIQUE** para recuperación de contraseña.
5. **Tabla password_reset**: `id_Reset PK AI`, `id_Empleado FK`, `Token_hash VARCHAR(255)`,
   `Expira DATETIME`, `Usado TINYINT(1)`.
6. **Vista vista_inventario_productos** exactamente como la define el enunciado.
7. **seed.php**: inserta un empleado por rol (admin/vendedor/bodeguero) con `password_hash`.

**Revisión 3FN del resto del modelo:** las demás tablas se revisarán durante la Tarea 1; cualquier
otra posible violación se reportará al usuario ANTES de cambiarla (no se tocará sin aprobación).

---

## Autenticación y sesión

- **Login** (`funciones/auth.php::iniciarSesion`): busca empleado por usuario; verifica bloqueo,
  estado activo y `password_verify`. En fallo suma intento; a los 5 bloquea permanentemente.
  Mensajes idénticos al original (`AuthService.java`):
  - "El usuario no existe en el sistema"
  - "La cuenta está bloqueada. Contacte al Administrador para desbloquearla"
  - "El empleado está inactivo y no puede iniciar sesión"
  - "Contraseña incorrecta"
  - "La cuenta ha sido bloqueada por demasiados intentos fallidos"
- **Sesión** (`includes/sesion.php`): guarda `id_Empleado`, `usuario`, `cargo`, y marca de tiempo
  de última actividad. En cada carga comprueba inactividad > 30 min y cierra sesión.
- **Protección** (`includes/proteger.php`): `exigirRol('Administrador')`, etc. Redirige al login
  si no hay sesión o deniega si el rol no corresponde. Cada página de rol y cada endpoint `api/` lo usa.
- Contraseñas con `password_hash(PASSWORD_DEFAULT)` y `password_verify`.

### Recuperación de contraseña
- `recuperar.php` → `funciones/auth.php::solicitarRecuperacion($correo)`: genera token aleatorio
  (`bin2hex(random_bytes(32))`), guarda `hash('sha256', token)` en `password_reset` con `Expira`
  = ahora + 30 min, `Usado=0`. Envía enlace con PHPMailer (`libs/PHPMailer`).
  Si el envío falla y `MODO_DESARROLLO` está activo, muestra el enlace en pantalla.
- `restablecer.php` → valida token (hash coincide, no expirado, no usado), pide nueva contraseña
  (mínimo 6), guarda con `password_hash`, marca `Usado=1`.

---

## Diseño visual (del CSS original)

> Nota: la paleta real del CSS original NO es café; es **rosa/magenta** sobre crema, con tipografía
> serif. Se conserva tal cual para respetar el diseño original:
> - Fondo crema `#FBF4EB` / `#FAFAF8`, acento magenta `#C43670`, rosa `#F283AF` / `#FBD9E5`, dorado `#F3CC97`.
> - Tipografías: **Cormorant Garamond** (serif, títulos/marca) y **Jost** (cuerpo).
>
> Se copian `login.css`, `dashboard.css`, `estilos.css`, `vistas.css` desde
> `referencia-jsf/src/main/webapp/resources/css` a `stylestore-php/assets/css` y se adaptan las
> clases/estructura al HTML de las nuevas páginas conservando colores y tipografía.

Cada rol mantiene su propio dashboard y menú lateral, igual que en el original.

---

## Notificaciones y validación en cliente

- `assets/js/notificaciones.js`: envoltorio sobre SweetAlert2/Toastify (`exito()`, `error()`,
  `confirmar()`). Nunca `alert()`.
- `assets/js/validaciones.js`: valida en cliente letras, números, email, fecha, requeridos y
  longitud mínima de contraseña. Las mismas reglas existen en `includes/validaciones.php` (servidor).

---

## Orden de implementación (resumen; detalle en tasks.md)

0. Reorganización del repo (Tarea 0).
1. Base de datos corregida + seed.
2. Conexión, sesión, protección, validaciones, encabezado/pie, assets base.
3. Login + logout + protección por rol + dashboards.
4. Registro de usuario.
5. Recuperar contraseña (PHPMailer).
6. CRUD de Usuarios/empleados.
7. CRUD de Proveedores (AJAX).
8. CRUD de Productos (AJAX) + descuentos + estados.
9. Clientes.
10. Inventario + ajustes.
11. Entrada de mercancía (transacción).
12. Ventas: pagos múltiples, anulación (transacción).
13. Consultas por rol.

Cada tarea termina con: prueba en XAMPP por parte del usuario, actualización de
`docs/GUIA_CODIGO.md`, y un commit en español (sin push).

---

## Estrategia de referencia por módulo

Antes de escribir cada módulo se leen en `referencia-jsf/` sus `service`, `repository` y `bean`,
y se traducen tal cual las validaciones, mensajes y reglas (contrastados con el manual). Si algo no
es claro, se consulta al usuario antes de cambiarlo.
