<?php
// Pagina de gestion de empleados (CRUD). Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../funciones/empleados.php';
protegerPagina('Administrador');

$pdo = conectar();
$cargos = listarCargos($pdo);

$tituloPagina = 'Empleados';
$subtituloPagina = 'Registro y gestión del personal';
$itemActivo = 'empleados';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<div class="panel-grid">

    <!-- Formulario (sirve para crear y editar) -->
    <div class="card">
        <div class="card-titulo" id="form-titulo">Nuevo empleado</div>
        <form id="form-empleado" novalidate>
            <input type="hidden" name="id_Empleado" id="id_Empleado" value="" />
            <div class="form-grid">
                <div class="form-campo">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre" class="form-input" oninput="filtrarSoloLetras(this)" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Apellido</label>
                    <input type="text" name="apellido" id="apellido" class="form-input" oninput="filtrarSoloLetras(this)" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Número de documento</label>
                    <input type="text" name="documento" id="documento" class="form-input" oninput="filtrarSoloNumeros(this)" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" id="correo" class="form-input" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" id="usuario" class="form-input" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Cargo</label>
                    <select name="id_Cargo" id="id_Cargo" class="form-select">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($cargos as $c): ?>
                            <option value="<?= $c['id_Cargo'] ?>"><?= htmlspecialchars($c['Tipo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-campo" id="campo-contrasena">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="contrasena" id="contrasena" class="form-input" />
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
        <div class="card-titulo">Personal registrado</div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Nombre</th><th>Documento</th><th>Correo</th><th>Cargo</th>
                        <th>Usuario</th><th>Estado</th><th>Bloqueo</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-empleados">
                    <tr><td colspan="8" class="sin-datos">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
const API = '<?= $base ?>/api/empleados.php';

// Cargar la lista de empleados
async function cargarEmpleados() {
    const r = await enviarAccion(API, { accion: 'listar' });
    const tbody = document.getElementById('tabla-empleados');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="8" class="sin-datos">Error al cargar</td></tr>'; return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="8" class="sin-datos">Sin empleados</td></tr>'; return; }

    tbody.innerHTML = r.datos.map(e => {
        const estado = e.EstadoEmpleado === 'Activo'
            ? '<span class="badge badge-activo">Activo</span>'
            : '<span class="badge badge-inactivo">Inactivo</span>';
        const bloqueo = e.EstadoBloqueo === 'Bloqueado'
            ? '<span class="badge badge-bloqueado">Bloqueado</span>'
            : '<span class="badge badge-activo">Desbloqueado</span>';

        // Botones segun el estado
        let acciones = `<button class="btn-tabla" onclick="editar(${e.id_Empleado})">Editar</button>`;
        if (e.EstadoEmpleado === 'Activo')
            acciones += ` <button class="btn-tabla-peligro" onclick="cambiarEstado(${e.id_Empleado},'desactivar','${nombreSeguro(e)}')">Desactivar</button>`;
        else
            acciones += ` <button class="btn-tabla" onclick="cambiarEstado(${e.id_Empleado},'activar','')">Activar</button>`;
        if (e.EstadoBloqueo === 'Bloqueado')
            acciones += ` <button class="btn-tabla" onclick="desbloquear(${e.id_Empleado},'${nombreSeguro(e)}')">Desbloquear</button>`;
        acciones += ` <button class="btn-tabla-peligro" onclick="eliminar(${e.id_Empleado},'${nombreSeguro(e)}')">Eliminar</button>`;

        return `<tr>
            <td>${esc(e.Nombre)} ${esc(e.Apellido)}</td>
            <td>${esc(e.Num_Documento)}</td>
            <td>${esc(e.Correo)}</td>
            <td>${esc(e.Cargo)}</td>
            <td>${esc(e.Usuario)}</td>
            <td>${estado}</td>
            <td>${bloqueo}</td>
            <td><div class="acciones">${acciones}</div></td>
        </tr>`;
    }).join('');
}

function esc(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }
function nombreSeguro(e) { return (e.Nombre + ' ' + e.Apellido).replace(/'/g, ''); }

// Enviar el formulario (crear o editar)
document.getElementById('form-empleado').addEventListener('submit', async function (e) {
    e.preventDefault();
    const f = this;
    const editando = f.id_Empleado.value !== '';

    const reglas = [
        { condicion: esRequerido(f.nombre.value) && esSoloLetras(f.nombre.value), mensaje: 'El nombre solo puede contener letras' },
        { condicion: esRequerido(f.apellido.value) && esSoloLetras(f.apellido.value), mensaje: 'El apellido solo puede contener letras' },
        { condicion: esRequerido(f.documento.value) && esSoloNumeros(f.documento.value), mensaje: 'El documento solo puede contener números' },
        { condicion: esEmailValido(f.correo.value), mensaje: 'El correo no tiene un formato válido' },
        { condicion: esRequerido(f.usuario.value), mensaje: 'El usuario es obligatorio' },
        { condicion: esRequerido(f.id_Cargo.value), mensaje: 'Debe seleccionar un cargo' },
    ];
    if (!editando) {
        reglas.push({ condicion: tieneLongitudMinima(f.contrasena.value, 6), mensaje: 'La contraseña debe tener al menos 6 caracteres' });
    }
    const error = primerError(reglas);
    if (error) { alertaError(error); return; }

    const datos = {
        accion: editando ? 'editar' : 'crear',
        id_Empleado: f.id_Empleado.value,
        nombre: f.nombre.value, apellido: f.apellido.value, documento: f.documento.value,
        correo: f.correo.value, usuario: f.usuario.value, id_Cargo: f.id_Cargo.value,
        contrasena: f.contrasena.value,
    };
    const r = await enviarAccion(API, datos);
    if (r.ok) { notificarExito(r.mensaje); cancelarEdicion(); cargarEmpleados(); }
    else { alertaError(r.mensaje); }
});

// Cargar un empleado en el formulario para editar
async function editar(id) {
    const r = await enviarAccion(API, { accion: 'obtener', id });
    if (!r.ok) { alertaError(r.mensaje); return; }
    const e = r.datos;
    const f = document.getElementById('form-empleado');
    f.id_Empleado.value = e.id_Empleado;
    f.nombre.value = e.Nombre; f.apellido.value = e.Apellido; f.documento.value = e.Num_Documento;
    f.correo.value = e.Correo; f.usuario.value = e.Usuario; f.id_Cargo.value = e.id_Cargo;
    document.getElementById('form-titulo').textContent = 'Editar empleado';
    document.getElementById('btn-guardar').textContent = 'Guardar cambios';
    document.getElementById('btn-cancelar').classList.remove('oculto');
    document.getElementById('campo-contrasena').classList.add('oculto'); // no se cambia aqui
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarEdicion() {
    const f = document.getElementById('form-empleado');
    f.reset(); f.id_Empleado.value = '';
    document.getElementById('form-titulo').textContent = 'Nuevo empleado';
    document.getElementById('btn-guardar').textContent = 'Registrar';
    document.getElementById('btn-cancelar').classList.add('oculto');
    document.getElementById('campo-contrasena').classList.remove('oculto');
}

async function cambiarEstado(id, accion, nombre) {
    if (accion === 'desactivar') {
        const ok = await confirmar('¿Desactivar a ' + nombre + '? No podrá iniciar sesión ni realizar operaciones.');
        if (!ok) return;
    }
    const r = await enviarAccion(API, { accion, id });
    if (r.ok) { notificarExito(r.mensaje); cargarEmpleados(); } else { alertaError(r.mensaje); }
}

async function desbloquear(id, nombre) {
    const ok = await confirmar('¿Desbloquear la cuenta de ' + nombre + '? Se reiniciará el contador de intentos fallidos.');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'desbloquear', id });
    if (r.ok) { notificarExito(r.mensaje); cargarEmpleados(); } else { alertaError(r.mensaje); }
}

async function eliminar(id, nombre) {
    const ok = await confirmar('¿Eliminar a ' + nombre + '? Si tiene movimientos, solo se desactivará.', 'Eliminar empleado');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'eliminar', id });
    if (r.ok) { notificarExito(r.mensaje); cargarEmpleados(); } else { alertaError(r.mensaje); }
}

cargarEmpleados();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
