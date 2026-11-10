<?php
// Dashboard del Bodeguero.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Bodeguero');

$tituloPagina = 'Dashboard';
$subtituloPagina = 'Selecciona una opción para continuar';
$itemActivo = '';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="bienvenida-banda">
    <div class="bienvenida-nombre">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?></div>
    <div class="bienvenida-fecha">Bodeguero — Panel de control</div>
</div>

<div class="card">
    <div class="card-titulo">Abastecimiento</div>
    <div class="accesos-grid">
        <a class="acceso-card" href="<?= $base ?>/bodeguero/mercancia.php">
            <div class="acceso-titulo">Entrada Mercancía</div>
            <div class="acceso-desc">Registrar entradas</div>
        </a>
        <a class="acceso-card" href="<?= $base ?>/bodeguero/inventario.php">
            <div class="acceso-titulo">Inventario</div>
            <div class="acceso-desc">Stock y estado de productos</div>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Reportes</div>
    <div class="accesos-grid">
        <a class="acceso-card" href="<?= $base ?>/bodeguero/consultas.php">
            <div class="acceso-titulo">Consultas</div>
            <div class="acceso-desc">Mis reportes</div>
        </a>
    </div>
</div>

<?php require __DIR__ . '/../includes/pie.php'; ?>
