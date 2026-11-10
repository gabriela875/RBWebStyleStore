<?php
// Vista compartida de "Nueva venta" (la usan vendedor y admin). Requiere $base.
?>

<!-- Paso 1: Cliente -->
<div class="card">
    <div class="card-titulo">Paso 1 — Vincular cliente</div>
    <div class="form-grid">
        <div class="form-campo">
            <label class="form-label">Documento del cliente</label>
            <input type="text" id="cli-documento" class="form-input" oninput="filtrarSoloNumeros(this)" />
        </div>
        <div class="form-campo" style="align-self:end;">
            <button type="button" class="btn-secundario" onclick="buscarCliente()">Buscar cliente</button>
        </div>
    </div>

    <div id="cli-info" class="mensaje-exito oculto"></div>

    <!-- Alta rapida si no existe -->
    <div id="cli-nuevo" class="oculto">
        <hr class="separador" />
        <p class="pagina-subtitulo">El cliente no existe. Regístralo:</p>
        <div class="form-grid">
            <div class="form-campo">
                <label class="form-label">Tipo de documento</label>
                <select id="cli-tipo" class="form-select"></select>
            </div>
            <div class="form-campo">
                <label class="form-label">Nombre</label>
                <input type="text" id="cli-nombre" class="form-input" oninput="filtrarSoloLetras(this)" />
            </div>
            <div class="form-campo">
                <label class="form-label">Apellido</label>
                <input type="text" id="cli-apellido" class="form-input" oninput="filtrarSoloLetras(this)" />
            </div>
            <div class="form-campo">
                <label class="form-label">Correo (opcional)</label>
                <input type="email" id="cli-correo" class="form-input" />
            </div>
        </div>
        <div class="btn-grupo">
            <button type="button" class="btn-secundario" onclick="crearCliente()">Registrar cliente</button>
        </div>
    </div>
</div>

<!-- Paso 2 y 3 (se muestran cuando hay venta creada) -->
<div id="venta-area" class="oculto">
    <div class="panel-grid">
        <!-- Agregar productos -->
        <div class="card">
            <div class="card-titulo">Paso 2 — Agregar productos</div>
            <div class="form-grid">
                <div class="form-campo form-grid-full">
                    <label class="form-label">Producto</label>
                    <select id="v-producto" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Talla</label>
                    <select id="v-talla" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Color</label>
                    <select id="v-color" class="form-select"><option value="">Seleccionar...</option></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Cantidad</label>
                    <input type="number" id="v-cantidad" class="form-input" min="1" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="button" class="btn-secundario" onclick="agregarProducto()">Agregar</button>
            </div>
        </div>

        <!-- Pago -->
        <div class="card">
            <div class="card-titulo">Paso 3 — Registrar pago (COP)</div>
            <div class="form-grid">
                <div class="form-campo">
                    <label class="form-label">Método</label>
                    <select id="v-metodo" class="form-select"></select>
                </div>
                <div class="form-campo">
                    <label class="form-label">Valor</label>
                    <input type="number" id="v-valor" class="form-input" min="1" step="0.01" />
                </div>
                <div class="form-campo form-grid-full">
                    <label class="form-label">Referencia (tarjeta/transferencia)</label>
                    <input type="text" id="v-referencia" class="form-input" />
                </div>
            </div>
            <div class="btn-grupo">
                <button type="button" class="btn-secundario" onclick="agregarPago()">Agregar pago</button>
            </div>
        </div>
    </div>

    <!-- Resumen de la venta -->
    <div class="card">
        <div class="card-titulo">Productos en esta venta</div>
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead><tr><th>Producto</th><th>Talla</th><th>Color</th><th>Cant.</th><th>Precio</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="v-detalles"><tr><td colspan="7" class="sin-datos">Sin productos</td></tr></tbody>
            </table>
        </div>
        <hr class="separador" />
        <div class="tabla-contenedor">
            <table class="tabla">
                <thead><tr><th>Pago</th><th>Método</th><th>Referencia</th><th>Cambio</th></tr></thead>
                <tbody id="v-pagos"><tr><td colspan="4" class="sin-datos">Sin pagos</td></tr></tbody>
            </table>
        </div>
        <hr class="separador" />
        <p><strong>Total:</strong> <span id="v-total">$0</span> &nbsp;|&nbsp;
           <strong>Pagado:</strong> <span id="v-pagado">$0</span></p>
        <div class="btn-grupo">
            <button type="button" class="btn-primario" onclick="completarVenta()">Completar venta</button>
            <button type="button" class="btn-peligro" onclick="anularVenta()">Anular venta</button>
        </div>
    </div>
</div>

<script>
const API_V = '<?= $base ?>/api/ventas.php';
let idVentaActual = null;
let idClienteActual = null;
let cat = null;

function escV(t){ const d=document.createElement('div'); d.textContent=t??''; return d.innerHTML; }
function dineroV(v){ return '$' + Number(v || 0).toLocaleString('es-CO'); }

async function initVenta() {
    const r = await enviarAccion(API_V, { accion: 'catalogos' });
    if (!r.ok) { alertaError(r.mensaje || 'Error al cargar'); return; }
    cat = r;
    document.getElementById('cli-tipo').innerHTML = r.tiposDoc.map(t => `<option value="${t.id_Tipo_Doc}">${escV(t.Nombre)}</option>`).join('');
    document.getElementById('v-producto').innerHTML = '<option value="">Seleccionar...</option>' +
        r.productos.map(p => `<option value="${p.id_Producto}">${escV(p.Nombre)} (${escV(p.Codigo)})</option>`).join('');
    document.getElementById('v-talla').innerHTML = '<option value="">Seleccionar...</option>' +
        r.tallas.map(t => `<option value="${t.id_Talla}">${escV(t.Nombre)}</option>`).join('');
    document.getElementById('v-color').innerHTML = '<option value="">Seleccionar...</option>' +
        r.colores.map(c => `<option value="${c.id_Color}">${escV(c.Nombre)}</option>`).join('');
    document.getElementById('v-metodo').innerHTML = r.metodos.map(m => `<option value="${m.id_Metodo_Pago}">${escV(m.Nombre)}</option>`).join('');
}

async function buscarCliente() {
    const doc = document.getElementById('cli-documento').value.trim();
    if (!esRequerido(doc)) { alertaError('Ingresa el documento'); return; }
    const r = await enviarAccion(API_V, { accion: 'buscar_cliente', documento: doc });
    if (r.ok) {
        idClienteActual = r.datos.id_Cliente;
        mostrarClienteVinculado(r.datos.Nombre + ' ' + r.datos.Apellido);
        document.getElementById('cli-nuevo').classList.add('oculto');
        await crearVenta();
    } else {
        // No existe: ofrecer alta
        document.getElementById('cli-nuevo').classList.remove('oculto');
        document.getElementById('cli-info').classList.add('oculto');
    }
}

async function crearCliente() {
    const datos = {
        accion: 'crear_cliente',
        id_Tipo_Doc: document.getElementById('cli-tipo').value,
        documento: document.getElementById('cli-documento').value.trim(),
        nombre: document.getElementById('cli-nombre').value,
        apellido: document.getElementById('cli-apellido').value,
        correo: document.getElementById('cli-correo').value,
    };
    const error = primerError([
        { condicion: esRequerido(datos.documento) && esSoloNumeros(datos.documento), mensaje: 'El documento solo puede contener números' },
        { condicion: esRequerido(datos.nombre) && esSoloLetras(datos.nombre), mensaje: 'El nombre solo puede contener letras' },
        { condicion: esRequerido(datos.apellido) && esSoloLetras(datos.apellido), mensaje: 'El apellido solo puede contener letras' },
        { condicion: datos.correo === '' || esEmailValido(datos.correo), mensaje: 'El correo no tiene un formato válido' },
    ]);
    if (error) { alertaError(error); return; }
    const r = await enviarAccion(API_V, datos);
    if (r.ok) {
        idClienteActual = r.id_Cliente;
        mostrarClienteVinculado(datos.nombre + ' ' + datos.apellido);
        document.getElementById('cli-nuevo').classList.add('oculto');
        await crearVenta();
    } else { alertaError(r.mensaje); }
}

function mostrarClienteVinculado(nombre) {
    const info = document.getElementById('cli-info');
    info.textContent = 'Cliente vinculado: ' + nombre;
    info.classList.remove('oculto');
}

async function crearVenta() {
    if (idVentaActual) return; // ya hay una venta en curso
    const r = await enviarAccion(API_V, { accion: 'crear_venta', id_Cliente: idClienteActual });
    if (r.ok) { idVentaActual = r.id_Venta; document.getElementById('venta-area').classList.remove('oculto'); refrescar(); }
    else { alertaError(r.mensaje); }
}

async function agregarProducto() {
    const idProducto = document.getElementById('v-producto').value;
    const idTalla = document.getElementById('v-talla').value;
    const idColor = document.getElementById('v-color').value;
    const cantidad = document.getElementById('v-cantidad').value;
    const error = primerError([
        { condicion: esRequerido(idProducto), mensaje: 'Debe seleccionar un producto' },
        { condicion: esRequerido(idTalla), mensaje: 'Debe seleccionar la talla' },
        { condicion: esRequerido(idColor), mensaje: 'Debe seleccionar el color' },
        { condicion: esMayorQueCero(cantidad), mensaje: 'La cantidad debe ser mayor a cero' },
    ]);
    if (error) { alertaError(error); return; }

    // Buscar la combinacion de inventario
    const inv = await enviarAccion(API_V, { accion: 'buscar_inventario', id_Producto: idProducto, id_Talla: idTalla, id_Color: idColor });
    if (!inv.ok) { alertaError(inv.mensaje); return; }

    const r = await enviarAccion(API_V, { accion: 'agregar_detalle', id_Venta: idVentaActual, id_Inventario: inv.datos.id_Inventario, cantidad });
    if (r.ok) { notificarExito(r.mensaje); pintarResumen(r.venta); document.getElementById('v-cantidad').value=''; }
    else { alertaError(r.mensaje); }
}

async function quitarDetalle(idDetalle) {
    const r = await enviarAccion(API_V, { accion: 'quitar_detalle', id_Venta: idVentaActual, id_Detalle: idDetalle });
    if (r.ok) { notificarExito(r.mensaje); pintarResumen(r.venta); } else { alertaError(r.mensaje); }
}

async function agregarPago() {
    const datos = {
        accion: 'registrar_pago', id_Venta: idVentaActual,
        id_Metodo_Pago: document.getElementById('v-metodo').value,
        valor: document.getElementById('v-valor').value,
        referencia: document.getElementById('v-referencia').value,
    };
    if (!esMayorQueCero(datos.valor)) { alertaError('El valor del pago debe ser mayor a cero'); return; }
    const r = await enviarAccion(API_V, datos);
    if (r.ok) { notificarExito(r.mensaje); pintarResumen(r.venta);
        document.getElementById('v-valor').value=''; document.getElementById('v-referencia').value=''; }
    else { alertaError(r.mensaje); }
}

async function refrescar() {
    const r = await enviarAccion(API_V, { accion: 'resumen', id_Venta: idVentaActual });
    if (r.ok) pintarResumen(r.venta);
}

function pintarResumen(v) {
    const td = document.getElementById('v-detalles');
    td.innerHTML = v.detalles.length === 0
        ? '<tr><td colspan="7" class="sin-datos">Sin productos</td></tr>'
        : v.detalles.map(d => `<tr>
            <td>${escV(d.Producto)}</td><td>${escV(d.Talla)}</td><td>${escV(d.Color)}</td>
            <td>${d.Cantidad}</td><td>${dineroV(d.Precio_unitario)}</td><td>${dineroV(d.Subtotal)}</td>
            <td><button class="btn-tabla-peligro" onclick="quitarDetalle(${d.id_Detalle_Venta})">Quitar</button></td>
        </tr>`).join('');

    const tp = document.getElementById('v-pagos');
    tp.innerHTML = v.pagos.length === 0
        ? '<tr><td colspan="4" class="sin-datos">Sin pagos</td></tr>'
        : v.pagos.map(p => `<tr>
            <td>${dineroV(p.Valor)}</td><td>${escV(p.Metodo)}</td>
            <td>${escV(p.Referencia || '-')}</td><td>${p.Cambio ? dineroV(p.Cambio) : '-'}</td>
        </tr>`).join('');

    document.getElementById('v-total').textContent = dineroV(v.cabecera.Total);
    document.getElementById('v-pagado').textContent = dineroV(v.suma_pagos);
}

async function completarVenta() {
    const ok = await confirmar('Al completar, el stock se descuenta y la venta queda inmutable. ¿Continuar?', 'Completar venta');
    if (!ok) return;
    const r = await enviarAccion(API_V, { accion: 'completar', id_Venta: idVentaActual });
    if (r.ok) { await alertaExito(r.mensaje); location.reload(); } else { alertaError(r.mensaje); }
}

async function anularVenta() {
    const motivo = await pedirTexto('Motivo de anulación (mínimo 5 caracteres)');
    if (motivo === null) return;
    if (motivo.trim().length < 5) { alertaError('El motivo debe tener al menos 5 caracteres'); return; }
    const r = await enviarAccion(API_V, { accion: 'anular', id_Venta: idVentaActual, motivo });
    if (r.ok) { await alertaExito(r.mensaje); location.reload(); } else { alertaError(r.mensaje); }
}

initVenta();
</script>
