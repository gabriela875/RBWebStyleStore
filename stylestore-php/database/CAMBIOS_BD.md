# Cambios en la base de datos

Este documento explica qué se cambió respecto al script original (`docs/bd_stylestore.sql`) y por
qué. El diseño general se mantiene: **no se renombraron tablas ni columnas** y **no se cambió la
estructura general**. Solo se aplicaron las correcciones solicitadas y una normalización.

---

## 1. INSERT de empleados corregido

**Problema:** el INSERT original estaba desalineado:

```sql
INSERT INTO empleados VALUES
(1,'12345678','Admin','StyleStore','admin','admin123', FALSE, 0, 1, 1);
```

La tabla `empleados` tiene estas columnas en orden:
`id_Empleado, Num_Documento, Nombre, Apellido, Usuario, Contrasena, Intentos_fallidos,
id_Cargo, id_Estado_Emp, id_Estado_Bloq`.

Al escribir los valores sin nombrar columnas, quedaban mal asignados:
- `Intentos_fallidos` recibía `FALSE` (0) en vez de un número de intentos.
- `id_Cargo` quedaba en `0`, que **no existe** en la tabla `cargo` → rompe la llave foránea.
- Además, como ahora se agregó la columna `Correo`, el INSERT por posición ya no sirve.

**Solución:** el usuario administrador (y uno por cada rol) ahora se crea con **`database/seed.php`**,
que inserta nombrando las columnas y con valores válidos: `Intentos_fallidos = 0`,
`id_Cargo = 1` (Administrador), `id_Estado_Emp = 1` (Activo), `id_Estado_Bloq = 1` (Desbloqueado).
Esto además permite guardar la contraseña con `password_hash` (ver cambio 7).

---

## 2. `estado_bloqueo.Nombre` pasa a VARCHAR(20) y se llena con datos

**Problema:** `Nombre` era `VARCHAR(10)`, insuficiente para la palabra `"Desbloqueado"` (12
caracteres). Además la tabla **no tenía ningún INSERT**, así que la llave foránea
`empleados.id_Estado_Bloq` no tenía a qué apuntar.

**Solución:**
- `Nombre VARCHAR(20)`.
- Se insertan las dos filas que el código original usa por nombre exacto:
  `(1, 'Desbloqueado')` y `(2, 'Bloqueado')`.

---

## 3. Se quitó `id_Proveedor` de `detalle_entrada` (normalización 3FN)

**Problema:** `detalle_entrada` tenía `id_Proveedor`, pero cada `detalle_entrada` pertenece a una
`entrada_mercancia`, y **la entrada ya tiene su `id_Proveedor`**. Guardarlo también en el detalle
es un dato redundante que depende de `id_Entrada` (no de la clave del detalle) → **viola la 3FN** y
permite inconsistencias (que el proveedor del detalle no coincida con el de la entrada).

**Solución:** se eliminó la columna `id_Proveedor` y su llave foránea de `detalle_entrada`. El
proveedor se obtiene desde `entrada_mercancia`.

---

## 4. Nueva columna `empleados.Correo` (única)

**Motivo:** se necesita un correo por empleado para la recuperación de contraseña.

**Solución:** `Correo VARCHAR(100) NOT NULL UNIQUE` en `empleados`.

---

## 5. Nueva tabla `password_reset`

**Motivo:** manejar la recuperación de contraseña con tokens seguros.

```sql
CREATE TABLE password_reset (
    id_Reset INT PRIMARY KEY AUTO_INCREMENT,
    id_Empleado INT NOT NULL,
    Token_hash VARCHAR(255) NOT NULL,   -- se guarda solo el hash del token, no el token
    Expira DATETIME NOT NULL,           -- vence a los 30 minutos
    Usado TINYINT(1) NOT NULL DEFAULT 0,-- un solo uso
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado)
);
```

---

## 6. Vista `vista_inventario_productos`

**Motivo:** el módulo de inventario del proyecto original la usa. Se creó tal cual la define el
enunciado (une inventario con producto, categoría, color, talla y proveedor).

---

## 7. Contraseñas con `password_hash`

**Motivo:** el script original guardaba la contraseña en texto plano (`admin123`). Por seguridad,
ahora se guardan cifradas con `password_hash` y se verifican con `password_verify`.

**Solución:** `empleados.Contrasena` se amplió a `VARCHAR(255)` (el hash es más largo que el texto)
y los usuarios de prueba se crean con `database/seed.php`.

---

## Revisión de 3FN del resto del modelo

Se revisaron todas las tablas. Además de `detalle_entrada` (ya corregida), **no se encontraron
otras violaciones de 3FN que requieran cambiar la estructura.** Observaciones:

- **Tablas de catálogo** (cargo, categoria, talla, color, tipo_documento, genero, tipo_descuento,
  metodo_pago, estado_*): son `id + nombre`. Correctas.
- **`producto`, `inventario`, `venta`, `clientes`, `proveedor`, `descuento`, `ajuste_inventario`,
  `anulacion_venta`, `entrada_mercancia`**: todos sus atributos dependen únicamente de su clave
  primaria. Correctas.
- **`detalle_venta.Descuento_aplicado` y `pago.Cambio`**: son valores que podrían "calcularse",
  pero se guardan a propósito como **registro histórico** del momento de la venta (el precio o el
  descuento pueden cambiar después). Esto es una decisión de diseño correcta y **no se considera
  violación de 3FN**, porque no es un dato que dependa de otro atributo no clave de la misma fila.
  Se dejaron igual.

> Si más adelante aparece alguna otra situación que parezca violar la 3FN, se reportará **antes** de
> cambiarla, tal como se acordó.
