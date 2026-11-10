<?php
// Vista compartida de entrada de mercancia (la usan admin y bodeguero).
// Requiere $base definido.
?>

<div class="card">
    <div class="card-titulo">Registrar entrada de mercancía</div>
    <div class="form-campo">
        <label class="form-label">Proveedor de la entrada</label>
        <select id="me-proveedor" class="form-select"><option value="">Cargando...</option></select>
    </div>
</div>

<div class="panel-grid">
    <!-- Agregar producto -->
    <div class="card">
        <div class="card-titulo">Agregar producto</div>
        <form id="form-item" novalidate>
            <div class="form-grid">
                <div class="form-campo form-grid-full">
                    <label class="form-label">Producto</label>
                    <select id="me-producto" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Talla</label>
                    <select id="me-talla" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Color</label>
                    <select id="me-color" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Cantidad</label>
                    <input type="number" id="me-cantidad" class="form-input" min="1" />
                </div>
                <div class="form-campo">
                    <label class="form-label">Precio unitario (COP)</label>
                    <input type="number" id="me-precio" class="form-input" min="1" step="0.01" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="submit" class="btn-secundario">Agregar producto</button>
            </div>
        </form>
    </div>

    <!-- Productos acumulados -->
    <div class="card">
        <div class="card-titulo">Productos de esta entrada</div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead>
                    <tr><th>Producto</th><th>Talla</th><th>Color</th><th>Cantidad</th><th>Precio</th><th></th></tr>
                </thead>
                <tbody id="tabla-items">
                    <tr><td colspan="6" class="sin-datos">Aún no has agregado productos</td></tr>
                </tbody>
            </table>
        </div>
        <div class="btn-grupo">
            <button type="button" class="btn-primario" id="btn-registrar-entrada" onclick="registrarEntrada()">Registrar entrada completa</button>
        </div>
    </div>
</div>

<script>
const API_ME = '<?= $base ?>/api/mercancia.php';
let productosME = [];     // productos con su proveedor
let items = [];           // detalles acumulados

function escME(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }

async function cargarCatalogosME() {
    const r = await enviarAccion(API_ME, { accion: 'catalogos' });
    if (!r.ok) { alertaError(r.mensaje || 'Error al cargar'); return; }
    productosME = r.productos;

    document.getElementById('me-proveedor').innerHTML = '<option value="">Seleccionar...</option>' +
        r.proveedores.map(p => `<option value="${p.id_Proveedor}">${escME(p.Nombre)}</option>`).join('');
    document.getElementById('me-producto').innerHTML = '<option value="">Seleccionar...</option>' +
        r.productos.map(p => `<option value="${p.id_Producto}" data-prov="${p.id_Proveedor}">${escME(p.Nombre)} (${escME(p.Codigo)})</option>`).join('');
    document.getElementById('me-talla').innerHTML = '<option value="">Seleccionar...</option>' +
        r.tallas.map(t => `<option value="${t.id_Talla}">${escME(t.Nombre)}</option>`).join('');
    document.getElementById('me-color').innerHTML = '<option value="">Seleccionar...</option>' +
        r.colores.map(c => `<option value="${c.id_Color}">${escME(c.Nombre)}</option>`).join('');
}

// Agregar un producto a la tabla acumulada (validaciones en cliente)
document.getElementById('form-item').addEventListener('submit', function (e) {
    e.preventDefault();
    const sel = document.getElementById('me-producto');
    const idProducto = sel.value;
    const idTalla = document.getElementById('me-talla').value;
    const idColor = document.getElementById('me-color').value;
    const cantidad = document.getElementById('me-cantidad').value;
    const precio = document.getElementById('me-precio').value;
    const idProveedor = document.getElementById('me-proveedor').value;

    const error = primerError([
        { condicion: esRequerido(idProveedor), mensaje: 'Primero selecciona el proveedor de la entrada' },
        { condicion: esRequerido(idProducto), mensaje: 'Debe seleccionar un producto' },
        { condicion: esRequerido(idTalla), mensaje: 'Debe seleccionar la talla' },
        { condicion: esRequerido(idColor), mensaje: 'Debe seleccionar el color' },
        { condicion: esMayorQueCero(cantidad), mensaje: 'La cantidad debe ser mayor a cero' },
        { condicion: esMayorQueCero(precio), mensaje: 'El precio unitario debe ser mayor a cero' },
    ]);
    if (error) { alertaError(error); return; }

    // Validar en cliente que el producto sea del proveedor elegido (el servidor tambien lo valida)
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.prov !== idProveedor) {
        alertaError('El producto no pertenece al proveedor seleccionado'); return;
    }

    const prod = productosME.find(p => String(p.id_Producto) === idProducto);
    const talla = document.getElementById('me-talla');
    const color = document.getElementById('me-color');
    items.push({
        id_Producto: idProducto, id_Talla: idTalla, id_Color: idColor,
        cantidad: cantidad, precio_unitario: precio,
        _nombre: prod ? prod.Nombre : idProducto,
        _talla: talla.options[talla.selectedIndex].text,
        _color: color.options[color.selectedIndex].text,
    });
    pintarItems();
    this.reset();
});

function pintarItems() {
    const tbody = document.getElementById('tabla-items');
    if (items.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="sin-datos">Aún no has agregado productos</td></tr>'; return; }
    tbody.innerHTML = items.map((it, idx) => `<tr>
        <td>${escME(it._nombre)}</td>
        <td>${escME(it._talla)}</td>
        <td>${escME(it._color)}</td>
        <td>${escME(it.cantidad)}</td>
        <td>$${Number(it.precio_unitario).toLocaleString('es-CO')}</td>
        <td><button class="btn-tabla-peligro" onclick="quitarItem(${idx})">Quitar</button></td>
    </tr>`).join('');
}

function quitarItem(idx) { items.splice(idx, 1); pintarItems(); }

// Al cambiar el proveedor, limpiamos los items (deben ser del mismo proveedor)
document.getElementById('me-proveedor').addEventListener('change', function () {
    if (items.length > 0) { items = []; pintarItems(); notificarExito('Se limpió la lista al cambiar de proveedor'); }
});

async function registrarEntrada() {
    const idProveedor = document.getElementById('me-proveedor').value;
    if (!esRequerido(idProveedor)) { alertaError('Debe seleccionar el proveedor'); return; }
    if (items.length === 0) { alertaError('Debe agregar al menos un producto a la entrada'); return; }

    // Enviar solo los campos que el servidor necesita
    const detalles = items.map(it => ({
        id_Producto: it.id_Producto, id_Talla: it.id_Talla, id_Color: it.id_Color,
        cantidad: it.cantidad, precio_unitario: it.precio_unitario,
    }));
    const r = await enviarAccion(API_ME, { accion: 'registrar', id_Proveedor: idProveedor, detalles: JSON.stringify(detalles) });
    if (r.ok) {
        alertaExito(r.mensaje);
        items = []; pintarItems();
        document.getElementById('me-proveedor').value = '';
    } else { alertaError(r.mensaje); }
}

cargarCatalogosME();
</script>
