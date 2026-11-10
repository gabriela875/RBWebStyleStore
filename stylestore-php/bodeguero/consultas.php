<?php
// Consultas del Bodeguero: entradas de mercancia.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Bodeguero');

$tituloPagina = 'Consultas';
$subtituloPagina = 'Entradas de mercancía';
$itemActivo = 'consultas';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

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

(async () => {
    const r = await enviarAccion(API_C, { accion: 'entradas' });
    const tb = document.getElementById('t-entradas');
    if (!r.ok) { tb.innerHTML = `<tr><td colspan="4" class="sin-datos">${escC(r.mensaje||'Error')}</td></tr>`; return; }
    if (r.datos.length === 0) { tb.innerHTML = '<tr><td colspan="4" class="sin-datos">Sin entradas registradas</td></tr>'; return; }
    tb.innerHTML = r.datos.map(e => `<tr><td>${e.id_Entrada}</td><td>${escC(e.Fecha)}</td><td>${escC(e.Proveedor)}</td><td>${escC(e.Empleado)}</td></tr>`).join('');
})();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
