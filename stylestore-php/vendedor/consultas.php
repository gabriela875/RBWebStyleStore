<?php
// Consultas del Vendedor: sus propias ventas.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Vendedor');

$tituloPagina = 'Consultas';
$subtituloPagina = 'Mis ventas';
$itemActivo = 'consultas';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="card">
    <div class="card-titulo">Mis ventas</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
            <tbody id="t-ventas"><tr><td colspan="5" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<script>
const API_C = '<?= $base ?>/api/consultas.php';
function escC(t){ const d=document.createElement('div'); d.textContent=t??''; return d.innerHTML; }
function dineroC(v){ return '$' + Number(v||0).toLocaleString('es-CO'); }
function badgeC(e){ const c = e==='Completada'?'badge-completada':e==='Anulada'?'badge-anulada':'badge-pendiente'; return `<span class="badge ${c}">${escC(e)}</span>`; }

(async () => {
    const r = await enviarAccion(API_C, { accion: 'mis_ventas' });
    const tb = document.getElementById('t-ventas');
    if (!r.ok) { tb.innerHTML = `<tr><td colspan="5" class="sin-datos">${escC(r.mensaje||'Error')}</td></tr>`; return; }
    if (r.datos.length === 0) { tb.innerHTML = '<tr><td colspan="5" class="sin-datos">No tienes ventas registradas</td></tr>'; return; }
    tb.innerHTML = r.datos.map(v => `<tr><td>${v.id_Venta}</td><td>${escC(v.Fecha_hora)}</td><td>${escC(v.Cliente)}</td><td>${dineroC(v.Total)}</td><td>${badgeC(v.Estado)}</td></tr>`).join('');
})();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
