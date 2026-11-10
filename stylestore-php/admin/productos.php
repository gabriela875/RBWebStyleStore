<?php
// Pagina de gestion de productos (CRUD + precio + descuentos). Solo Administrador.
require_once __DIR__ . '/../includes/proteger.php';
require_once __DIR__ . '/../funciones/productos.php';
protegerPagina('Administrador');

$pdo = conectar();
$cat = catalogosProducto($pdo);

$tituloPagina = 'Productos';
$subtituloPagina = 'Catálogo y descuentos';
$itemActivo = 'productos';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
?>

<!-- Formulario nuevo / editar producto -->
<div class="card">
    <div class="card-titulo" id="form-titulo">Nuevo producto</div>
    <form id="form-producto" novalidate>
        <input type="hidden" name="id_Producto" id="id_Producto" value="" />
        <div class="form-grid">
            <div class="form-campo">
                <label class="form-label">Código único</label>
                <input type="text" name="codigo" id="codigo" class="form-input" />
            </div>
            <div class="form-campo">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-input" />
            </div>
            <div class="form-campo">
                <label class="form-label">Marca</label>
                <input type="text" name="marca" id="marca" class="form-input" />
            </div>
            <div class="form-campo" id="campo-precio">
                <label class="form-label">Precio base (COP)</label>
                <input type="number" name="precio" id="precio" class="form-input" min="1" step="0.01" />
            </div>
            <div class="form-campo">
                <label class="form-label">Categoría</label>
                <select name="id_Categoria" id="id_Categoria" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($cat['categorias'] as $c): ?>
                        <option value="<?= $c['id_Categoria'] ?>"><?= htmlspecialchars($c['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-campo">
                <label class="form-label">Género</label>
                <select name="id_Genero" id="id_Genero" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($cat['generos'] as $g): ?>
                        <option value="<?= $g['id_Genero'] ?>"><?= htmlspecialchars($g['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-campo">
                <label class="form-label">Proveedor</label>
                <select name="id_Proveedor" id="id_Proveedor" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($cat['proveedores'] as $pr): ?>
                        <option value="<?= $pr['id_Proveedor'] ?>"><?= htmlspecialchars($pr['Nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="btn-grupo">
            <button type="submit" class="btn-primario" id="btn-guardar">Registrar</button>
            <button type="button" class="btn-secundario oculto" id="btn-cancelar" onclick="cancelarEdicion()">Cancelar</button>
        </div>
    </form>
</div>

<div class="panel-grid">
    <!-- Actualizar precio -->
    <div class="card">
        <div class="card-titulo">Actualizar precio de producto</div>
        <form id="form-precio" novalidate>
            <div class="form-campo">
                <label class="form-label">Producto</label>
                <select id="precio-producto" class="form-select"></select>
            </div>
            <div class="form-campo">
                <label class="form-label">Nuevo precio (COP)</label>
                <input type="number" id="precio-nuevo" class="form-input" min="1" step="0.01" />
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario">Actualizar precio</button>
            </div>
        </form>
    </div>

    <!-- Registrar descuento -->
    <div class="card">
        <div class="card-titulo">Registrar descuento</div>
        <form id="form-descuento" novalidate>
            <div class="form-campo">
                <label class="form-label">Producto</label>
                <select id="desc-producto" class="form-select"></select>
            </div>
            <div class="form-grid">
                <div class="form-campo">
                    <label class="form-label">Tipo</label>
                    <select id="desc-tipo" class="form-select">
                        <option value="">Seleccionar...</option>
                        <?php foreach ($cat['tiposDesc'] as $t): ?>
                            <option value="<?= $t['id_Tipo_Desc'] ?>"><?= htmlspecialchars($t['Nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Valor</label>
                    <input type="number" id="desc-valor" class="form-input" min="0.01" step="0.01" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" id="desc-inicio" class="form-input" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" id="desc-fin" class="form-input" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-primario">Registrar descuento</button>
            </div>
        </form>
    </div>
</div>

<!-- Catalogo -->
<div class="card">
    <div class="card-titulo">Catálogo de productos</div>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Código</th><th>Nombre</th><th>Marca</th><th>Precio</th>
                    <th>Categoría</th><th>Género</th><th>Proveedor</th><th>Estado</th><th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-productos">
                <tr><td colspan="9" class="sin-datos">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const API = '<?= $base ?>/api/productos.php';
let productosCache = [];

function esc(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }
function dinero(v) { return '$' + Number(v).toLocaleString('es-CO'); }

function badgeEstado(estado) {
    const clase = estado === 'Disponible' ? 'badge-disponible'
        : estado === 'Agotado' ? 'badge-agotado' : 'badge-descontinuado';
    return `<span class="badge ${clase}">${esc(estado)}</span>`;
}

async function cargarProductos() {
    const r = await enviarAccion(API, { accion: 'listar' });
    const tbody = document.getElementById('tabla-productos');
    if (!r.ok) { tbody.innerHTML = '<tr><td colspan="9" class="sin-datos">Error al cargar</td></tr>'; return; }
    productosCache = r.datos;

    // Rellenar selectores de precio y descuento (solo no descontinuados tienen sentido)
    const opciones = r.datos.map(p => `<option value="${p.id_Producto}">${esc(p.Nombre)} (${esc(p.Codigo)})</option>`).join('');
    document.getElementById('precio-producto').innerHTML = '<option value="">Seleccionar...</option>' + opciones;
    document.getElementById('desc-producto').innerHTML = '<option value="">Seleccionar...</option>' + opciones;

    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="9" class="sin-datos">Sin productos</td></tr>'; return; }

    tbody.innerHTML = r.datos.map(p => {
        let acciones = `<button class="btn-tabla" onclick="editar(${p.id_Producto})">Editar</button>`;
        if (p.Estado === 'Disponible')
            acciones += ` <button class="btn-tabla-peligro" onclick="marcarAgotado(${p.id_Producto})">Marcar agotado</button>`;
        if (p.Estado !== 'Descontinuado')
            acciones += ` <button class="btn-tabla-peligro" onclick="descontinuar(${p.id_Producto})">Descontinuar</button>`;
        if (p.Estado === 'Descontinuado')
            acciones += ` <button class="btn-tabla-peligro" onclick="eliminar(${p.id_Producto})">Eliminar</button>`;

        return `<tr>
            <td>${esc(p.Codigo)}</td>
            <td>${esc(p.Nombre)}</td>
            <td>${esc(p.Marca)}</td>
            <td>${dinero(p.Precio_base)}</td>
            <td>${esc(p.Categoria)}</td>
            <td>${esc(p.Genero)}</td>
            <td>${esc(p.Proveedor)}</td>
            <td>${badgeEstado(p.Estado)}</td>
            <td><div class="acciones">${acciones}</div></td>
        </tr>`;
    }).join('');
}

// Crear / editar producto
document.getElementById('form-producto').addEventListener('submit', async function (e) {
    e.preventDefault();
    const f = this;
    const editando = f.id_Producto.value !== '';
    const reglas = [
        { condicion: esRequerido(f.codigo.value), mensaje: 'El código es obligatorio' },
        { condicion: esRequerido(f.nombre.value), mensaje: 'El nombre es obligatorio' },
        { condicion: esRequerido(f.marca.value), mensaje: 'La marca es obligatoria' },
        { condicion: esRequerido(f.id_Categoria.value), mensaje: 'Debe seleccionar una categoría' },
        { condicion: esRequerido(f.id_Genero.value), mensaje: 'Debe seleccionar un género' },
        { condicion: esRequerido(f.id_Proveedor.value), mensaje: 'Debe seleccionar un proveedor' },
    ];
    if (!editando) reglas.push({ condicion: esMayorQueCero(f.precio.value), mensaje: 'El precio debe ser mayor a cero' });
    const error = primerError(reglas);
    if (error) { alertaError(error); return; }

    const datos = {
        accion: editando ? 'editar' : 'crear',
        id_Producto: f.id_Producto.value,
        codigo: f.codigo.value, nombre: f.nombre.value, marca: f.marca.value,
        id_Categoria: f.id_Categoria.value, id_Genero: f.id_Genero.value, id_Proveedor: f.id_Proveedor.value,
        precio: f.precio.value,
    };
    const r = await enviarAccion(API, datos);
    if (r.ok) { notificarExito(r.mensaje); cancelarEdicion(); cargarProductos(); } else { alertaError(r.mensaje); }
});

async function editar(id) {
    const r = await enviarAccion(API, { accion: 'obtener', id });
    if (!r.ok) { alertaError(r.mensaje); return; }
    const p = r.datos;
    const f = document.getElementById('form-producto');
    f.id_Producto.value = p.id_Producto;
    f.codigo.value = p.Codigo; f.nombre.value = p.Nombre; f.marca.value = p.Marca;
    f.id_Categoria.value = p.id_Categoria; f.id_Genero.value = p.id_Genero; f.id_Proveedor.value = p.id_Proveedor;
    document.getElementById('form-titulo').textContent = 'Editar producto';
    document.getElementById('btn-guardar').textContent = 'Guardar cambios';
    document.getElementById('btn-cancelar').classList.remove('oculto');
    document.getElementById('campo-precio').classList.add('oculto'); // el precio se cambia en su seccion
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarEdicion() {
    const f = document.getElementById('form-producto');
    f.reset(); f.id_Producto.value = '';
    document.getElementById('form-titulo').textContent = 'Nuevo producto';
    document.getElementById('btn-guardar').textContent = 'Registrar';
    document.getElementById('btn-cancelar').classList.add('oculto');
    document.getElementById('campo-precio').classList.remove('oculto');
}

// Actualizar precio
document.getElementById('form-precio').addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = document.getElementById('precio-producto').value;
    const precio = document.getElementById('precio-nuevo').value;
    const error = primerError([
        { condicion: esRequerido(id), mensaje: 'Debe seleccionar un producto' },
        { condicion: esMayorQueCero(precio), mensaje: 'El precio debe ser mayor a cero' },
    ]);
    if (error) { alertaError(error); return; }
    const r = await enviarAccion(API, { accion: 'actualizar_precio', id, precio });
    if (r.ok) { notificarExito(r.mensaje); this.reset(); cargarProductos(); } else { alertaError(r.mensaje); }
});

// Registrar descuento
document.getElementById('form-descuento').addEventListener('submit', async function (e) {
    e.preventDefault();
    const datos = {
        accion: 'registrar_descuento',
        id_Producto: document.getElementById('desc-producto').value,
        id_Tipo_Desc: document.getElementById('desc-tipo').value,
        valor: document.getElementById('desc-valor').value,
        fecha_inicio: document.getElementById('desc-inicio').value,
        fecha_fin: document.getElementById('desc-fin').value,
    };
    const error = primerError([
        { condicion: esRequerido(datos.id_Producto), mensaje: 'Debe seleccionar un producto' },
        { condicion: esRequerido(datos.id_Tipo_Desc), mensaje: 'Debe seleccionar el tipo de descuento' },
        { condicion: esMayorQueCero(datos.valor), mensaje: 'El valor debe ser mayor a cero' },
        { condicion: esFechaValida(datos.fecha_inicio), mensaje: 'La fecha de inicio no es válida' },
        { condicion: esFechaValida(datos.fecha_fin), mensaje: 'La fecha de fin no es válida' },
        { condicion: datos.fecha_fin >= datos.fecha_inicio, mensaje: 'La fecha de fin no puede ser anterior a la de inicio' },
    ]);
    if (error) { alertaError(error); return; }
    const r = await enviarAccion(API, datos);
    if (r.ok) { notificarExito(r.mensaje); this.reset(); } else { alertaError(r.mensaje); }
});

async function marcarAgotado(id) {
    const ok = await confirmar('¿Marcar este producto como agotado?');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'marcar_agotado', id });
    if (r.ok) { notificarExito(r.mensaje); cargarProductos(); } else { alertaError(r.mensaje); }
}

async function descontinuar(id) {
    const ok = await confirmar('Descontinuar es permanente: el producto no podrá volver a venderse y se elimina su descuento activo. ¿Continuar?', 'Descontinuar producto');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'descontinuar', id });
    if (r.ok) { notificarExito(r.mensaje); cargarProductos(); } else { alertaError(r.mensaje); }
}

async function eliminar(id) {
    const ok = await confirmar('¿Eliminar definitivamente este producto descontinuado?', 'Eliminar producto');
    if (!ok) return;
    const r = await enviarAccion(API, { accion: 'eliminar', id });
    if (r.ok) { notificarExito(r.mensaje); cargarProductos(); } else { alertaError(r.mensaje); }
}

cargarProductos();
</script>

<?php require __DIR__ . '/../includes/pie.php'; ?>
