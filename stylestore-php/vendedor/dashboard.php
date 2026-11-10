<?php
// Dashboard del Vendedor.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Vendedor');

$tituloPagina = 'Dashboard';
$subtituloPagina = 'Selecciona una opción para continuar';
$itemActivo = '';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="bienvenida-banda">
    <div class="bienvenida-nombre">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></div>
    <div class="bienvenida-fecha">Vendedor — Panel de control</div>
</div>

<div class="card">
    <div class="card-titulo">Operaciones</div>
    <div class="accesos-grid">
        <a class="acceso-card" href="<?= $base ?>/vendedor/ventas.php">
            <div class="acceso-titulo">Nueva Venta</div>
            <div class="acceso-desc">Registrar una venta</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/vendedor/consultas.php">
            <div class="acceso-titulo">Consultas</div>
            <div class="acceso-desc">Mis reportes</div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>
