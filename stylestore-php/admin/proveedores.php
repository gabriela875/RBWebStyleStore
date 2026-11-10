<?php
// Pagina de gestion de proveedores (CRUD en una sola pagina). Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Proveedores';
$subtituloPagina = 'Registro y gestión de proveedores';
$itemActivo = 'proveedores';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="panel-grid">

    <!-- Formulario (crear / editar) -->
    <div class="card">
        <div class="card-titulo" id="form-titulo">Nuevo proveedor</div>
        <form id="form-proveedor" novalidate>
            <input type="hidden" name="id_Proveedor" id="id_Proveedor" value="" />
            <div class="form-grid">
                <div class="form-campo form-grid-full">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-input" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" class="form-input" oninput="filtrarSoloNumeros(this)" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo" class="form-input" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario" id="btn-guardar">Registrar</button>
                <button type="button" class="btn-secundario oculto" id="btn-cancelar" onclick="cancelarEdicion()">Cancelar</button>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-titulo">Proveedores registrados</div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Acciones</th></tr>
                </thead>
                <tbody id="tabla-proveedores">
                    <tr><td colspan="4" class="sin-datos">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const API = '<?= $base ?>/api/proveedores.php';

function esc(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }

async function cargarProveedores() {
    const r = await enviarAccion(API, { accion: 'listar' });
    const tbody = document.getElementById('tabla-proveedores');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="4" class="sin-datos">Error al cargar</td></tr>'; return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="4" class="sin-datos">Sin proveedores</td></tr>'; return; }

    tbody.innerHTML = r.datos.map(p => `<tr>
        <td>${esc(p.Nombre)}</td>
        <td>${esc(p.Telefono)}</td>
        <td>${esc(p.Correo)}</td>
        <td><div class="acciones">
            <button class="btn-tabla" title="Editar" onclick="editar(${p.id_Proveedor})">✎ Editar</button>
            <button class="btn-tabla-peligro" title="Eliminar" onclick="eliminar(${p.id_Proveedor}, '${esc(p.Nombre).replace(/'/g,'')}')">🗑 Eliminar</button>
        </div></td>
    </tr>`).join('');
}

document.getElementById('form-proveedor').addEventListener('submit', async function (e) {
    e.preventDefault();
    const f = this;
    const editando = f.id_Proveedor.value !== '';

    const error = primerError([
        { condicion: esRequerido(f.nombre.value), mensaje: 'El nombre es obligatorio' },
        { condicion: esRequerido(f.telefono.value) && esSoloNumeros(f.telefono.value), mensaje: 'El teléfono solo puede contener números' },
        { condicion: esEmailValido(f.correo.value), mensaje: 'El correo no tiene un formato válido' },
    ]);
    if (error) { alertaError(error); return; }

    const datos = {
        accion: editando ? 'editar' : 'crear',
        id_Proveedor: f.id_Proveedor.value,
        nombre: f.nombre.value, telefono: f.telefono.value, correo: f.correo.value,
    };
    const r = await enviarAccion(API, datos);
    if (r.ok) { notificarExito(r.mensaje); cancelarEdicion(); cargarProveedores(); }
    else { alertaError(r.mensaje); }
});

async function editar(id) {
    const r = await enviarAccion(API, { accion: 'obtener', id });
    if (!r.ok) { alertaError(r.mensaje); return; }
    const p = r.datos;
    const f = document.getElementById('form-proveedor');
    f.id_Proveedor.value = p.id_Proveedor;
    f.nombre.value = p.Nombre; f.telefono.value = p.Telefono; f.correo.value = p.Correo;
    document.getElementById('form-titulo').textContent = 'Editar proveedor';
    document.getElementById('btn-guardar').textContent = 'Actualizar';
    document.getElementById('btn-cancelar').classList.remove('oculto');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarEdicion() {
    const f = document.getElementById('form-proveedor');
    f.reset(); f.id_Proveedor.value = '';
    document.getElementById('form-titulo').textContent = 'Nuevo proveedor';
    document.getElementById('btn-guardar').textContent = 'Registrar';
    document.getElementById('btn-cancelar').classList.add('oculto');
}

async function eliminar(id, nombre) {
    const ok = await confirmar('¿Dar de baja al proveedor ' + nombre + '?', 'Eliminar proveedor');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'eliminar', id });
    if (r.ok) { notificarExito(r.mensaje); cargarProveedores(); } else { alertaError(r.mensaje); }
}

cargarProveedores();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
