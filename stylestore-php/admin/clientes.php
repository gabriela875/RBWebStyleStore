<?php
// Pagina de clientes (consultar y editar). Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../funciones/clientes.php';
protegerPagina('Administrador');

$pdo = conectar();
$tiposDoc = listarTiposDocumento($pdo);

$tituloPagina = 'Clientes';
$subtituloPagina = 'Consultar y editar clientes';
$itemActivo = 'clientes';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="panel-grid">

    <!-- Formulario de edicion (los clientes se crean en una venta) -->
    <div class="card">
        <div class="card-titulo">Editar cliente</div>
        <form id="form-cliente" novalidate>
            <input type="hidden" name="id_Cliente" id="id_Cliente" value="" />
            <div class="form-grid">
                <div class="form-campo">
                    <label class="form-label">Documento</label>
                    <!-- El documento no se puede modificar (regla del manual) -->
                    <input type="text" id="documento" class="form-input" disabled />
                </div>
                <div class="form-campo">
                    <label class="form-label">Tipo de documento</label>
                    <select name="id_Tipo_Doc" id="id_Tipo_Doc" class="form-select">
                        <?php foreach ($tiposDoc as $t): ?>
                            <option value="<?= $t['id_Tipo_Doc'] ?>"><?= htmlspecialchars($t['Nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-input" oninput="filtrarSoloLetras(this)" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" id="apellido" class="form-input" oninput="filtrarSoloLetras(this)" />
                </div>
                <div class="form-campo form-grid-full">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo" class="form-input" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario" id="btn-guardar" disabled>Guardar cambios</button>
                <button type="button" class="btn-secundario oculto" id="btn-cancelar" onclick="cancelarEdicion()">Cancelar</button>
            </div>
            <p class="pagina-subtitulo" id="aviso-seleccion">Selecciona un cliente de la tabla para editarlo.</p>
        </form>
    </div>

    <!-- Tabla -->
    <div class="card">
        <div class="card-titulo">Clientes registrados</div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr><th>Documento</th><th>Tipo</th><th>Nombre</th><th>Correo</th><th>Acciones</th></tr>
                </thead>
                <tbody id="tabla-clientes">
                    <tr><td colspan="5" class="sin-datos">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const API = '<?= $base ?>/api/clientes.php';

function esc(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }

async function cargarClientes() {
    const r = await enviarAccion(API, { accion: 'listar' });
    const tbody = document.getElementById('tabla-clientes');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="5" class="sin-datos">Error al cargar</td></tr>'; return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="5" class="sin-datos">Sin clientes registrados</td></tr>'; return; }

    tbody.innerHTML = r.datos.map(c => `<tr>
        <td>${esc(c.Num_Documento)}</td>
        <td>${esc(c.TipoDocumento)}</td>
        <td>${esc(c.Nombre)} ${esc(c.Apellido)}</td>
        <td>${esc(c.Correo)}</td>
        <td><div class="acciones"><button class="btn-tabla" onclick="editar(${c.id_Cliente})">Editar</button></div></td>
    </tr>`).join('');
}

async function editar(id) {
    const r = await enviarAccion(API, { accion: 'obtener', id });
    if (!r.ok) { alertaError(r.mensaje); return; }
    const c = r.datos;
    const f = document.getElementById('form-cliente');
    f.id_Cliente.value = c.id_Cliente;
    document.getElementById('documento').value = c.Num_Documento;
    f.id_Tipo_Doc.value = c.id_Tipo_Doc;
    f.nombre.value = c.Nombre; f.apellido.value = c.Apellido; f.correo.value = c.Correo ?? '';
    document.getElementById('btn-guardar').disabled = false;
    document.getElementById('btn-cancelar').classList.remove('oculto');
    document.getElementById('aviso-seleccion').classList.add('oculto');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarEdicion() {
    const f = document.getElementById('form-cliente');
    f.reset(); f.id_Cliente.value = ''; document.getElementById('documento').value = '';
    document.getElementById('btn-guardar').disabled = true;
    document.getElementById('btn-cancelar').classList.add('oculto');
    document.getElementById('aviso-seleccion').classList.remove('oculto');
}

document.getElementById('form-cliente').addEventListener('submit', async function (e) {
    e.preventDefault();
    const f = this;
    if (f.id_Cliente.value === '') { alertaError('Selecciona un cliente primero'); return; }
    const error = primerError([
        { condicion: esRequerido(f.id_Tipo_Doc.value), mensaje: 'Debe seleccionar el tipo de documento' },
        { condicion: esRequerido(f.nombre.value) && esSoloLetras(f.nombre.value), mensaje: 'El nombre solo puede contener letras' },
        { condicion: esRequerido(f.apellido.value) && esSoloLetras(f.apellido.value), mensaje: 'El apellido solo puede contener letras' },
        { condicion: f.correo.value === '' || esEmailValido(f.correo.value), mensaje: 'El correo no tiene un formato válido' },
    ]);
    if (error) { alertaError(error); return; }

    const datos = {
        accion: 'editar', id_Cliente: f.id_Cliente.value, id_Tipo_Doc: f.id_Tipo_Doc.value,
        nombre: f.nombre.value, apellido: f.apellido.value, correo: f.correo.value,
    };
    const r = await enviarAccion(API, datos);
    if (r.ok) { notificarExito(r.mensaje); cancelarEdicion(); cargarClientes(); } else { alertaError(r.mensaje); }
});

cargarClientes();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
