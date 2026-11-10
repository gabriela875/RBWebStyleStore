<?php
// Consultas del Administrador: todas las ventas, anulaciones, vencidas y entradas.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Consultas';
$subtituloPagina = 'Reportes del sistema';
$itemActivo = 'consultas';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="card">
    <div class="card-titulo">Todas las ventas</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody id="t-ventas"><tr><td colspan="6" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Ventas pendientes vencidas (más de 24 horas)</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Total</th></tr></thead>
            <tbody id="t-vencidas"><tr><td colspan="5" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Registro de anulaciones</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>Venta #</th><th>Motivo</th><th>Fecha</th><th>Empleado</th></tr></thead>
            <tbody id="t-anulaciones"><tr><td colspan="4" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Entradas de mercancía</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>#</th><th>Fecha</th><th>Proveedor</th><th>Empleado</th></tr></thead>
            <tbody id="t-entradas"><tr><td colspan="4" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<script>
const API_C = '<?= $base ?>/api/consultas.php';
function escC(t){ const d=document.createElement('div'); d.textContent=t??''; return d.innerHTML; }
function dineroC(v){ return '$' + Number(v||0).toLocaleString('es-CO'); }
function badgeC(e){ const c = e==='Completada'?'badge-completada':e==='Anulada'?'badge-anulada':'badge-pendiente'; return `<span class="badge ${c}">${escC(e)}</span>`; }

async function llenar(accion, tbodyId, cols, filaFn) {
    const r = await enviarAccion(API_C, { accion });
    const tb = document.getElementById(tbodyId);
    if (!r.ok) { tb.innerHTML = `<tr><td colspan="${cols}" class="sin-datos">${escC(r.mensaje||'Error')}</td></tr>`; return; }
    if (r.datos.length === 0) { tb.innerHTML = `<tr><td colspan="${cols}" class="sin-datos">Sin registros</td></tr>`; return; }
    tb.innerHTML = r.datos.map(filaFn).join('');
}

llenar('todas_ventas', 't-ventas', 6, v => `<tr><td>${v.id_Venta}</td><td>${escC(v.Fecha_hora)}</td><td>${escC(v.Cliente)}</td><td>${escC(v.Empleado)}</td><td>${dineroC(v.Total)}</td><td>${badgeC(v.Estado)}</td></tr>`);
llenar('vencidas', 't-vencidas', 5, v => `<tr><td>${v.id_Venta}</td><td>${escC(v.Fecha_hora)}</td><td>${escC(v.Cliente)}</td><td>${escC(v.Empleado)}</td><td>${dineroC(v.Total)}</td></tr>`);
llenar('anulaciones', 't-anulaciones', 4, a => `<tr><td>${a.id_Venta}</td><td>${escC(a.Motivo)}</td><td>${escC(a.Fecha_hora)}</td><td>${escC(a.Empleado)}</td></tr>`);
llenar('entradas', 't-entradas', 4, e => `<tr><td>${e.id_Entrada}</td><td>${escC(e.Fecha)}</td><td>${escC(e.Proveedor)}</td><td>${escC(e.Empleado)}</td></tr>`);
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
