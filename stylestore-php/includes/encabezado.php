<?php
// Encabezado comun: header + menu lateral segun el cargo.
// Antes de incluirlo, la pagina debe definir:
//   $tituloPagina   (string) titulo que se muestra arriba del contenido
//   $subtituloPagina(string, opcional)
//   $itemActivo     (string, opcional) nombre del item del menu que va resaltado
// Requiere que ya se haya llamado a protegerPagina().

require_once __DIR__ . '/proteger.php';

$cargo  = empleadoActivoCargo();
$nombre = empleadoActivoNombre();
$base   = defined('URL_BASE') ? URL_BASE : '';
$activo = $itemActivo ?? '';

// Menus por rol: [etiqueta, url, clave_activo], y los encabezados de seccion.
// Se respeta el mismo menu del proyecto original (plantilla.xhtml).
$menus = [
    'Administrador' => [
        ['seccion', 'Gestión'],
        ['Clientes',            $base . '/admin/clientes.php',     'clientes'],
        ['Empleados',           $base . '/admin/empleados.php',    'empleados'],
        ['Proveedores',         $base . '/admin/proveedores.php',  'proveedores'],
        ['Productos',           $base . '/admin/productos.php',    'productos'],
        ['Inventario',          $base . '/admin/inventario.php',   'inventario'],
        ['seccion', 'Operaciones'],
        ['Ventas (historial)',  $base . '/admin/ventas.php',       'ventas'],
        ['Nueva Venta',         $base . '/admin/venta-nueva.php',  'nueva-venta'],
        ['Entrada Mercancía',   $base . '/admin/mercancia.php',    'mercancia'],
        ['seccion', 'Reportes'],
        ['Consultas',           $base . '/admin/consultas.php',    'consultas'],
    ],
    'Vendedor' => [
        ['seccion', 'Operaciones'],
        ['Nueva Venta',         $base . '/vendedor/ventas.php',    'nueva-venta'],
        ['seccion', 'Reportes'],
        ['Consultas',           $base . '/vendedor/consultas.php', 'consultas'],
    ],
    'Bodeguero' => [
        ['seccion', 'Abastecimiento'],
        ['Entrada Mercancía',   $base . '/bodeguero/mercancia.php','mercancia'],
        ['Inventario',          $base . '/bodeguero/inventario.php','inventario'],
        ['seccion', 'Reportes'],
        ['Consultas',           $base . '/bodeguero/consultas.php','consultas'],
    ],
];
$menu = $menus[$cargo] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StyleStore</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/estilos.css" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/dashboard.css" />
    <link rel="stylesheet" href="<?= $base ?>/assets/css/vistas.css" />
    <!-- Notificaciones (SweetAlert2 y Toastify) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="<?= $base ?>/assets/js/notificaciones.js" ></script>
    <script src="<?= $base ?>/assets/js/validaciones.js" ></script>
</head>
<body>
<div class="layout">

    <header class="header">
        <div class="header-logo">Style<span>Store</span></div>
        <div class="header-info">
            <div>
                <div class="header-empleado"><?= htmlspecialchars($nombre) ?></div>
                <div class="header-cargo"><?= htmlspecialchars($cargo) ?></div>
            </div>
            <form method="post" action="<?= $base ?>/logout.php">
                <button type="submit" class="btn-logout">Cerrar sesión</button>
            </form>
        </div>
    </header>

    <nav class="sidebar">
        <?php foreach ($menu as $item): ?>
            <?php if ($item[0] === 'seccion'): ?>
                <span class="sidebar-seccion"><?= htmlspecialchars($item[1]) ?></span>
            <?php else: ?>
                <a class="sidebar-item <?= ($activo === $item[2]) ? 'activo' : '' ?>"
                   href="<?= $item[1] ?>"><?= htmlspecialchars($item[0]) ?></a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <main class="contenido">
        <?php if (!empty($tituloPagina)): ?>
            <h1 class="pagina-titulo"><?= htmlspecialchars($tituloPagina) ?></h1>
        <?php endif; ?>
        <?php if (!empty($subtituloPagina)): ?>
            <p class="pagina-subtitulo"><?= htmlspecialchars($subtituloPagina) ?></p>
        <?php endif; ?>
