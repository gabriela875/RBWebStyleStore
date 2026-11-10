# Requisitos — Migración de StyleStore a PHP

## Introducción

StyleStore es un sistema de gestión de ventas presenciales de ropa, originalmente hecho en JSF
(Java EE + JPA + PrimeFaces). Este documento define los requisitos para reescribirlo en **PHP 8
nativo con PDO + MySQL**, HTML, CSS y JavaScript simple, sin frameworks, corriendo en XAMPP.

La lógica de negocio, validaciones y mensajes se toman del proyecto original (`referencia-jsf/`)
y del manual de usuario (`docs/Manual_Usuario_StyleStore.pdf`). Se traduce la lógica, no la
estructura de clases Java.

### Roles del sistema (del manual)
- **Administrador**: acceso completo (empleados, proveedores, productos, inventario, ventas, clientes, consultas).
- **Vendedor**: registro de ventas y clientes; sus consultas.
- **Bodeguero**: entrada de mercancía, inventario y estado de productos; sus consultas.

---

## Requisito 0 — Reorganización del repositorio

**Historia:** Como desarrollador, quiero un repositorio ordenado que separe el proyecto JSF de
referencia del nuevo proyecto PHP, para trabajar la migración sin perder el material original.

#### Criterios de aceptación
1. CUANDO se inicie el trabajo ENTONCES se hará en la rama `migracion-php-stylestore` sin tocar `main` y sin hacer push.
2. CUANDO se reorganice ENTONCES todo el proyecto JSF (pom.xml, src/, archivos de configuración de la raíz) se moverá con `git mv` a `referencia-jsf/` sin borrar ni modificar su contenido.
3. CUANDO se cree la estructura PHP ENTONCES existirá `stylestore-php/` con: `index.php`, `registro.php`, `recuperar.php`, `restablecer.php`, `config/`, `includes/`, `funciones/`, `api/`, `admin/`, `vendedor/`, `bodeguero/`, `assets/css`, `assets/js`, `libs/`, `database/`.
4. CUANDO se configure git ENTONCES habrá un `.gitignore` que ignore `target/`, `*.class`, `.settings`, `.classpath`, `.project`, `vendor/` y `config/conexion.php`; y existirá `config/conexion.example.php` sin contraseñas reales.
5. CUANDO se documente ENTONCES existirá un `README.md` en lenguaje simple con: qué es el proyecto, qué hay en cada carpeta y cómo ejecutar la versión PHP en XAMPP.
6. CUANDO termine la reorganización ENTONCES habrá un único commit en español, se mostrará el árbol de carpetas y NO se hará push.

---

## Requisito 1 — Base de datos corregida en 3FN

**Historia:** Como desarrollador, quiero el script SQL original corregido y en 3FN, para que el
sistema PHP funcione con datos coherentes.

#### Criterios de aceptación
1. CUANDO se entregue el script ENTONCES estará en `stylestore-php/database/bd_stylestore.sql` conservando nombres de tablas y columnas del original.
2. CUANDO se corrija el INSERT de empleados ENTONCES `id_Cargo` y `id_Estado_Bloq` tendrán valores válidos y existirán los datos de `estado_bloqueo`.
3. CUANDO se ajuste `estado_bloqueo` ENTONCES `Nombre` será `VARCHAR(20)` y tendrá las filas `'Desbloqueado'` y `'Bloqueado'`.
4. CUANDO se normalice `detalle_entrada` ENTONCES se quitará `id_Proveedor` (redundante con `entrada_mercancia`).
5. CUANDO se habilite la recuperación de contraseña ENTONCES `empleados` tendrá una columna `Correo` única.
6. CUANDO se requiera recuperar contraseña ENTONCES existirá la tabla `password_reset (id_Reset, id_Empleado, Token_hash, Expira, Usado)`.
7. CUANDO el inventario consulte productos ENTONCES existirá la vista `vista_inventario_productos` definida en el enunciado.
8. CUANDO se creen usuarios de prueba ENTONCES existirá `database/seed.php` que inserta un usuario por rol con `password_hash`.
9. CUANDO se cambie la estructura ENTONCES los cambios quedarán documentados en `stylestore-php/database/CAMBIOS_BD.md` con qué y por qué.

---

## Requisito 2 — Login con perfiles

**Historia:** Como empleado, quiero iniciar sesión según mi cargo, para acceder a mi panel correspondiente.

#### Criterios de aceptación
1. CUANDO ingrese usuario y contraseña correctos ENTONCES se iniciará sesión y se redirigirá al dashboard del cargo (Administrador, Vendedor o Bodeguero).
2. CUANDO la contraseña sea incorrecta ENTONCES se sumará un intento fallido y se mostrará "Contraseña incorrecta".
3. CUANDO se acumulen 5 intentos fallidos ENTONCES la cuenta quedará bloqueada permanentemente y solo el Administrador podrá desbloquearla.
4. CUANDO el empleado esté inactivo ENTONCES no podrá iniciar sesión (mensaje del original).
5. CUANDO la cuenta esté bloqueada ENTONCES se mostrará el mensaje del original indicando contactar al Administrador.
6. CUANDO pasen 30 minutos de inactividad ENTONCES la sesión se cerrará automáticamente.
7. CUANDO se acceda a una página o acción sin el rol adecuado ENTONCES se denegará el acceso.
8. CUANDO las contraseñas se almacenen ENTONCES se usará `password_hash` y la verificación `password_verify`.

---

## Requisito 3 — Registro de usuario

**Historia:** Como visitante, quiero registrarme desde el login, para solicitar una cuenta de vendedor.

#### Criterios de aceptación
1. CUANDO complete nombre, apellido, documento, correo, usuario y contraseña ENTONCES se creará una cuenta con cargo **Vendedor** y estado **Inactivo**.
2. CUANDO el documento, usuario o correo ya existan ENTONCES se mostrará un mensaje de error y no se creará la cuenta.
3. CUANDO la cuenta quede inactiva ENTONCES solo el Administrador podrá activarla antes de poder iniciar sesión.

---

## Requisito 4 — Recuperar contraseña

**Historia:** Como empleado, quiero recuperar mi contraseña por correo, para recuperar el acceso.

#### Criterios de aceptación
1. CUANDO ingrese un correo registrado ENTONCES se generará un token aleatorio, se guardará solo su hash, expirará en 30 minutos y será de un solo uso.
2. CUANDO el token exista ENTONCES se enviará un enlace por correo usando PHPMailer instalado en `libs/PHPMailer` sin Composer.
3. CUANDO el envío de correo falle en local y el modo desarrollo esté activo ENTONCES se mostrará el enlace en pantalla.
4. CUANDO abra el enlace con un token válido y no usado ENTONCES podrá definir una nueva contraseña (mínimo 6 caracteres).
5. CUANDO el token esté expirado, usado o sea inválido ENTONCES se rechazará el restablecimiento.

---

## Requisito 5 — CRUD de Usuarios (empleados) — solo Administrador

**Historia:** Como Administrador, quiero gestionar empleados, para controlar el acceso al sistema.

#### Criterios de aceptación
1. CUANDO cree un empleado ENTONCES se validará documento y usuario únicos y contraseña mínima de 6 caracteres.
2. CUANDO liste empleados ENTONCES se verán con su cargo, estado y estado de bloqueo.
3. CUANDO edite un empleado ENTONCES se actualizarán sus datos.
4. CUANDO active o desactive un empleado ENTONCES cambiará su estado; NO se podrá desactivar si tiene ventas pendientes (deben anularse primero).
5. CUANDO desbloquee una cuenta ENTONCES se reiniciarán los intentos fallidos y el estado de bloqueo.
6. CUANDO elimine un empleado ENTONCES se borrará de verdad solo si no tiene ventas, entradas ni ajustes; si los tiene, solo se desactivará.
7. CUANDO cualquier acción se ejecute ENTONCES estará protegida para rol Administrador.

---

## Requisito 6 — CRUD de Proveedores (una sola página, AJAX) — solo Administrador

**Historia:** Como Administrador, quiero gestionar proveedores en una sola página sin recargar.

#### Criterios de aceptación
1. CUANDO registre un proveedor ENTONCES se validará nombre único, correo único y formato de correo ("Ya existe un proveedor con ese nombre" / "...con ese correo").
2. CUANDO edite un proveedor ENTONCES se cargará el formulario con sus datos y se validará unicidad contra otros proveedores.
3. CUANDO elimine un proveedor ENTONCES se rechazará si tiene productos asociados ("No se puede eliminar un proveedor con productos asociados").
4. CUANDO consulte ENTONCES se listarán los proveedores ordenados por nombre.
5. CUANDO use las acciones ENTONCES serán vía fetch/AJAX a `api/` devolviendo JSON, sin recargar la página.

---

## Requisito 7 — CRUD de Productos (una sola página, AJAX)

**Historia:** Como Administrador (y Bodeguero para estados), quiero gestionar el catálogo de productos.

#### Criterios de aceptación
1. CUANDO registre un producto ENTONCES se validará código único y precio mayor a cero, con categoría, proveedor y género.
2. CUANDO edite un producto ENTONCES se modificarán nombre, marca, género, categoría y proveedor (el estado no se cambia desde ahí).
3. CUANDO actualice el precio ENTONCES se hará desde la sección de precio y no afectará ventas completadas.
4. CUANDO marque agotado ENTONCES solo aplicará a productos Disponibles; el sistema también marca agotado cuando todas las combinaciones talla/color llegan a stock cero.
5. CUANDO descontinúe un producto ENTONCES será permanente, se eliminará su descuento activo y el stock queda inmovilizado.
6. CUANDO elimine un producto ENTONCES solo si está Descontinuado y no participó en ninguna venta.
7. CUANDO registre un descuento ENTONCES solo uno activo por producto, el valor no puede igualar ni superar el precio base, con fechas válidas.
8. CUANDO el Bodeguero acceda ENTONCES solo podrá marcar agotado/descontinuado.

---

## Requisito 8 — Validaciones en cliente y servidor

**Historia:** Como usuario, quiero validaciones claras, para ingresar datos correctos.

#### Criterios de aceptación
1. CUANDO escriba nombres o apellidos ENTONCES solo se aceptarán letras.
2. CUANDO escriba documento o teléfono ENTONCES solo se aceptarán números.
3. CUANDO escriba un correo ENTONCES se validará formato de email.
4. CUANDO escriba una fecha ENTONCES se validará que sea una fecha válida.
5. CUANDO un campo sea obligatorio ENTONCES no podrá quedar vacío.
6. CUANDO escriba una contraseña ENTONCES tendrá mínimo 6 caracteres.
7. CUANDO se muestre un mensaje ENTONCES será con SweetAlert2 o Toastify, nunca con `alert()`.
8. CUANDO exista validación en cliente ENTONCES también existirá la misma validación en servidor.

---

## Requisito 9 — Módulos restantes (clientes, inventario, entrada de mercancía, ventas, consultas)

**Historia:** Como empleado, quiero los módulos operativos según mi rol, para operar la tienda.

#### Criterios de aceptación
1. CUANDO el Administrador gestione clientes ENTONCES podrá editar tipo de documento, nombre, apellido y correo; el documento no se puede modificar.
2. CUANDO se consulte inventario ENTONCES se mostrará stock total, reservado y disponible (disponible = total − reservado) por combinación talla/color; ajustes manuales solo del Administrador y el stock no queda negativo ni por debajo de lo reservado.
3. CUANDO se registre entrada de mercancía ENTONCES el proveedor de la entrada debe coincidir con el del producto, no se reciben productos descontinuados, se crean combinaciones talla/color nuevas, y se usa transacción PDO.
4. CUANDO se registre una venta ENTONCES se reserva stock en estado pendiente, se aceptan pagos múltiples en COP, la suma debe cubrir exactamente el total, al completar se descuenta el stock y la venta queda inmutable; se usa transacción PDO.
5. CUANDO se anule una venta ENTONCES solo si está pendiente, con motivo obligatorio (mínimo 5 caracteres), liberando el stock reservado y dejando los pagos sin efecto; queda registrado con fecha, hora y empleado.
6. CUANDO se hagan consultas ENTONCES cada rol verá las consultas que le corresponden (p. ej. anulaciones solo el Administrador).

---

## Requisito 10 — Acceso a datos seguro

#### Criterios de aceptación
1. CUANDO se acceda a la base de datos ENTONCES se usarán consultas preparadas PDO en todo el sistema.
2. CUANDO se manejen operaciones multi-tabla críticas (ventas, entradas) ENTONCES se usarán transacciones PDO.
