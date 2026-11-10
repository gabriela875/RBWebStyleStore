# StyleStore

StyleStore es un sistema para administrar una tienda de ropa: maneja el inicio de sesión por
cargos, los empleados, los proveedores, los productos, el inventario (por talla y color), las
entradas de mercancía, las ventas y los clientes.

Este repositorio contiene **dos versiones** del mismo sistema:

- **`referencia-jsf/`** — La versión original hecha en Java (JSF + JPA + PrimeFaces). Se conserva
  **solo como material de consulta** para entender las reglas de negocio y las pantallas. No se
  ejecuta ni se modifica.
- **`stylestore-php/`** — La nueva versión reescrita en **PHP 8 + MySQL**, pensada para correr en
  **XAMPP**. Esta es la que se desarrolla y se ejecuta.

---

## ¿Qué hay en cada carpeta?

```
RBWebStyleStore/
├── README.md              → este archivo
├── docs/                  → documentación y material de referencia
│   ├── Manual_Usuario_StyleStore.pdf   (manual de usuario: roles y reglas)
│   ├── bd_stylestore.sql               (SQL original, solo referencia)
│   ├── requisitos.md                   (enunciado del proyecto)
│   ├── GUIA_EJECUCION.md               (cómo correr el proyecto en XAMPP, paso a paso)
│   └── GUIA_CODIGO.md                  (explicación simple de cada archivo, para sustentar)
│
├── referencia-jsf/        → proyecto Java original (solo consulta, no se ejecuta)
│
└── stylestore-php/        → proyecto PHP (el que se ejecuta)
    ├── index.php          → pantalla de login
    ├── registro.php       → registro de usuario nuevo
    ├── recuperar.php      → solicitar recuperación de contraseña
    ├── restablecer.php    → definir nueva contraseña con un enlace
    ├── config/            → conexión a la base de datos
    ├── includes/          → piezas comunes (sesión, roles, validaciones, encabezado, pie)
    ├── funciones/         → la lógica de cada módulo (un archivo por módulo)
    ├── api/               → acciones AJAX que devuelven JSON (sin recargar la página)
    ├── admin/             → páginas del Administrador
    ├── vendedor/          → páginas del Vendedor
    ├── bodeguero/         → páginas del Bodeguero
    ├── assets/            → css/ (estilos) y js/ (JavaScript)
    ├── libs/              → librerías externas (PHPMailer, sin Composer)
    └── database/          → script SQL final, cambios de la BD y usuarios de prueba (seed)
```

---

## Cómo ejecutar la versión PHP en XAMPP (resumen)

> La guía detallada paso a paso, pensada para principiantes y con solución de problemas comunes,
> está en **`docs/GUIA_EJECUCION.md`**. Aquí va el resumen:

1. **Instala XAMPP** y, desde su panel, inicia **Apache** y **MySQL**.
2. **Coloca el proyecto** dentro de `C:\xampp\htdocs` (por ejemplo `C:\xampp\htdocs\stylestore-php`),
   o crea un enlace con `mklink /J`. La URL final será `http://localhost/stylestore-php`.
3. **Crea la base de datos:** abre phpMyAdmin (`http://localhost/phpmyadmin`) e **importa**
   `stylestore-php/database/bd_stylestore.sql`.
4. **Configura la conexión:** copia `stylestore-php/config/conexion.example.php` como
   `stylestore-php/config/conexion.php`. En XAMPP el usuario es `root` y la contraseña va vacía.
5. **Crea los usuarios de prueba:** abre en el navegador
   `http://localhost/stylestore-php/database/seed.php`. Luego **borra o bloquea** ese archivo.
6. **Abre la aplicación** en `http://localhost/stylestore-php` e inicia sesión.

---

## Tecnologías

- PHP 8 nativo con PDO (sin frameworks)
- MySQL / MariaDB (XAMPP)
- HTML, CSS y JavaScript simple
- SweetAlert2 / Toastify para notificaciones
- PHPMailer para el correo de recuperación de contraseña
