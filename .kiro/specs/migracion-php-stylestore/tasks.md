# Plan de implementación — Migración de StyleStore a PHP

Reglas transversales: trabajar en la rama `migracion-php-stylestore`, un commit por tarea con
mensaje claro en español, sin hacer push. Cada tarea es pequeña para poder probarla en XAMPP.
Antes de cada módulo, leer su `service`/`repository`/`bean` en `referencia-jsf/` y traducir reglas
y mensajes tal cual. Al terminar cada módulo, actualizar `docs/GUIA_CODIGO.md`.

- [x] 0. Reorganizar el repositorio
  - Mover con `git mv` todo el proyecto JSF (pom.xml, src/, .classpath, .project, .settings, .metadata) a `referencia-jsf/`
  - Crear la estructura de carpetas de `stylestore-php/` con los archivos base vacíos/placeholder
  - Crear `.gitignore` (target/, *.class, .settings, .classpath, .project, vendor/, config/conexion.php)
  - Crear `config/conexion.example.php` sin credenciales reales
  - Crear `README.md` (qué es, carpetas, cómo correr en XAMPP)
  - Commit en español y mostrar el árbol de carpetas resultante (sin push)
  - _Requisitos: 0_

- [x] 1. Base de datos corregida
  - [x] 1.1 Crear `stylestore-php/database/bd_stylestore.sql` con las 7 correcciones (INSERT empleados, estado_bloqueo, detalle_entrada, Correo, password_reset, vista, datos base)
  - [x] 1.2 Revisar el resto del modelo en 3FN y reportar cualquier otra violación antes de cambiarla
  - [x] 1.3 Crear `database/CAMBIOS_BD.md` documentando cada cambio y su motivo
  - [x] 1.4 Crear `database/seed.php` que inserta un usuario por rol con `password_hash`
  - _Requisitos: 1_

- [x] 2. Infraestructura base PHP
  - [x] 2.1 `config/conexion.php` (PDO) a partir del example
  - [x] 2.2 `includes/sesion.php` (sesión + inactividad 30 min)
  - [x] 2.3 `includes/proteger.php` (verificación de autenticación y rol)
  - [x] 2.4 `includes/validaciones.php` (letras, números, email, fecha, requeridos, longitud)
  - [x] 2.5 `includes/encabezado.php` y `includes/pie.php` (menú por rol)
  - [x] 2.6 Copiar y adaptar CSS desde `referencia-jsf` a `assets/css`; crear `assets/js/notificaciones.js` y `assets/js/validaciones.js`
  - _Requisitos: 2, 8, 10_

- [x] 3. Login y dashboards por rol
  - [x] 3.1 Leer `LoginBean`, `AuthService`, `SessionBean`, `EmpleadoRepository` en referencia-jsf
  - [x] 3.2 `funciones/auth.php::iniciarSesion` (bloqueo 5 intentos, inactivo, mensajes originales, password_verify)
  - [x] 3.3 `index.php` (login) y cierre de sesión
  - [x] 3.4 `admin/dashboard.php`, `vendedor/dashboard.php`, `bodeguero/dashboard.php` con su menú
  - [x] 3.5 Proteger cada dashboard por rol
  - _Requisitos: 2_

- [x] 4. Registro de usuario
  - [x] 4.1 `registro.php` + `funciones/auth.php::registrar` (cargo Vendedor, estado Inactivo, unicidad doc/usuario/correo)
  - _Requisitos: 3_

- [x] 5. Recuperar contraseña
  - [x] 5.1 Instalar PHPMailer sin Composer en `libs/PHPMailer`
  - [x] 5.2 `recuperar.php` + `solicitarRecuperacion` (token aleatorio, guarda hash, expira 30 min, un uso)
  - [x] 5.3 Envío con PHPMailer; fallback que muestra el enlace en modo desarrollo
  - [x] 5.4 `restablecer.php` + validación de token + nueva contraseña (mín. 6)
  - _Requisitos: 4_

- [x] 6. CRUD de Usuarios (empleados) — Administrador
  - [x] 6.1 Leer `EmpleadoBean`, `EmpleadoService`, `EmpleadoRepository`
  - [x] 6.2 `funciones/empleados.php` (crear, listar, editar, activar/desactivar, desbloquear, eliminar con reglas)
  - [x] 6.3 `admin/empleados.php` + `api/empleados.php` (AJAX) con validaciones cliente+servidor
  - _Requisitos: 5, 8_

- [x] 7. CRUD de Proveedores (AJAX) — Administrador
  - [x] 7.1 Leer `ProveedorBean`, `ProveedorService`, `ProveedorRepository`
  - [x] 7.2 `funciones/proveedores.php` (reglas y mensajes originales)
  - [x] 7.3 `admin/proveedores.php` + `api/proveedores.php` + JS (una sola página, sin recargar)
  - _Requisitos: 6, 8_

- [x] 8. CRUD de Productos (AJAX) + descuentos + estados
  - [x] 8.1 Leer `ProductoBean`, `ProductoService`, `ProductoRepository`
  - [x] 8.2 `funciones/productos.php` (código único, precio>0, actualizar precio, agotado, descontinuar, eliminar, descuentos)
  - [x] 8.3 `admin/productos.php` + `api/productos.php` + JS; acceso de Bodeguero solo a estados
  - _Requisitos: 7, 8_

- [x] 9. Clientes — Administrador
  - [x] 9.1 Leer `ClienteBean`, `ClienteService`, `ClienteRepository`
  - [x] 9.2 `funciones/clientes.php` + `admin/clientes.php` + `api/clientes.php` (documento no editable)
  - _Requisitos: 9_

- [x] 10. Inventario y ajustes
  - [x] 10.1 Leer `InventarioBean`, `InventarioService`, `InventarioRepository`
  - [x] 10.2 `funciones/inventario.php` (stock total/reservado/disponible, ajuste solo Admin, no negativo ni bajo reservado)
  - [x] 10.3 `admin/inventario.php` y `bodeguero/inventario.php` + `api/inventario.php`
  - _Requisitos: 9_

- [x] 11. Entrada de mercancía (transacción)
  - [x] 11.1 Leer `AbastecimientoBean`, `AbastecimientoService`, `AbastecimientoRepository`
  - [x] 11.2 `funciones/mercancia.php` (proveedor coincide, no descontinuados, crea combinaciones, transacción PDO)
  - [x] 11.3 `bodeguero/mercancia.php` (+admin) + `api/mercancia.php`
  - _Requisitos: 9, 10_

- [x] 12. Ventas (transacción): pagos múltiples y anulación
  - [x] 12.1 Leer `VentaBean`, `VentaService`, `VentaRepository`
  - [x] 12.2 `funciones/ventas.php` (reserva de stock, pagos COP, suma exacta, completar inmutable, anular con motivo ≥5, transacción PDO)
  - [x] 12.3 `vendedor/ventas.php` y `admin/ventas.php` + `api/ventas.php`
  - _Requisitos: 9, 10_

- [x] 13. Consultas por rol
  - [x] 13.1 Leer `ConsultaBean`, `ConsultaService`
  - [x] 13.2 `funciones/consultas.php` + `admin/consultas.php`, `vendedor/consultas.php`, `bodeguero/consultas.php` (anulaciones solo Admin)
  - _Requisitos: 9_
