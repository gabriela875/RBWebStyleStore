<?php
// Dashboard del Administrador.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Dashboard';
$subtituloPagina = 'Selecciona una opción para continuar';
$itemActivo = '';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="bienvenida-banda">
    <div class="bienvenida-nombre">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></div>
    <div class="bienvenida-fecha">Administrador — Panel de control</div>
</div>

<div class="card">
    <div class="card-titulo">Gestión</div>
    <div class="accesos-grid">
        <a class="acceso-card" href="<?= $base ?>/admin/empleados.php">
            <div class="acceso-titulo">Empleados</div>
            <div class="acceso-desc">Gestionar personal y accesos</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/admin/productos.php">
            <div class="acceso-titulo">Productos</div>
            <div class="acceso-desc">Catálogo y descuentos</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/admin/proveedores.php">
            <div class="acceso-titulo">Proveedores</div>
            <div class="acceso-desc">Gestionar proveedores</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/admin/clientes.php">
            <div class="acceso-titulo">Clientes</div>
            <div class="acceso-desc">Consultar y editar clientes</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/admin/inventario.php">
            <div class="acceso-titulo">Inventario</div>
            <div class="acceso-desc">Stock por combinación</div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Operaciones</div>
    <div class="accesos-grid">
        <a class="acceso-card" href="<?= $base ?>/admin/ventas.php">
            <div class="acceso-titulo">Ventas</div>
            <div class="acceso-desc">Ver y gestionar ventas</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/admin/consultas.php">
            <div class="acceso-titulo">Consultas</div>
            <div class="acceso-desc">Reportes del sistema</div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>
