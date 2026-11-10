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
