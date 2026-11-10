<?php
// Historial y gestion de ventas (Administrador): ver todas, anular pendientes, ver anulaciones.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Ventas (historial)';
$subtituloPagina = 'Todas las ventas del sistema';
$itemActivo = 'ventas';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="card">
    <div class="card-titulo">Ventas registradas</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Total</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="tabla-ventas"><tr><td colspan="7" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-titulo">Registro de anulaciones</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead><tr><th>Venta #</th><th>Motivo</th><th>Fecha</th><th>Empleado</th></tr></thead>
            <tbody id="tabla-anulaciones"><tr><td colspan="4" class="sin-datos">Cargando...</td></tr></tbody>
        </table>
    </div>
</div>

<script>
const API_V = '<?= $base ?>/api/ventas.php';
function escV(t){ const d=document.createElement('div'); d.textContent=t??''; return d.innerHTML; }
function dineroV(v){ return '$' + Number(v||0).toLocaleString('es-CO'); }

function badgeVenta(estado) {
    const clase = estado === 'Completada' ? 'badge-completada' : estado === 'Anulada' ? 'badge-anulada' : 'badge-pendiente';
    return `<span class="badge ${clase}">${escV(estado)}</span>`;
}

async function cargarVentas() {
    const r = await enviarAccion(API_V, { accion: 'listar' });
    const tbody = document.getElementById('tabla-ventas');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="7" class="sin-datos">Error al cargar</td></tr>'; return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="7" class="sin-datos">Sin ventas</td></tr>'; return; }
    tbody.innerHTML = r.datos.map(v => {
        const acc = v.Estado === 'Pendiente'
            ? `<button class="btn-tabla-peligro" onclick="anular(${v.id_Venta})">Anular</button>` : '';
        return `<tr>
            <td>${v.id_Venta}</td><td>${escV(v.Fecha_hora)}</td><td>${escV(v.Cliente)}</td>
            <td>${escV(v.Empleado)}</td><td>${dineroV(v.Total)}</td><td>${badgeVenta(v.Estado)}</td>
            <td><div class="acciones">${acc}</div></td>
        </tr>`;
    }).join('');
}

async function cargarAnulaciones() {
    const r = await enviarAccion(API_V, { accion: 'anulaciones' });
    const tbody = document.getElementById('tabla-anulaciones');
    if (!r.ok) { tbody.innerHTML = `<tr><td colspan="4" class="sin-datos">${escV(r.mensaje||'Error')}</td></tr>`; return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="4" class="sin-datos">Sin anulaciones</td></tr>'; return; }
    tbody.innerHTML = r.datos.map(a => `<tr>
        <td>${a.id_Venta}</td><td>${escV(a.Motivo)}</td><td>${escV(a.Fecha_hora)}</td><td>${escV(a.Empleado)}</td>
    </tr>`).join('');
}

async function anular(idVenta) {
    const motivo = await pedirTexto('Motivo de anulación (mínimo 5 caracteres)');
    if (motivo === null) return;
    if (motivo.trim().length < 5) { alertaError('El motivo debe tener al menos 5 caracteres'); return; }
    const r = await enviarAccion(API_V, { accion: 'anular', id_Venta: idVenta, motivo });
    if (r.ok) { notificarExito(r.mensaje); cargarVentas(); cargarAnulaciones(); } else { alertaError(r.mensaje); }
}

cargarVentas();
cargarAnulaciones();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
