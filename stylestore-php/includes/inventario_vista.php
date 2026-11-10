<?php
// Vista compartida del modulo de inventario (la usan admin y bodeguero).
// Antes de incluir: $cargo ya protegido; se asume $puedeAjustar (bool) y $base.
// Tambien necesita $pdo con los productos para el selector.
require_once __DIR__ . '/../funciones/productos.php';

$pdoVista = conectar();
$productosLista = listarProductos($pdoVista);
?>

<!-- Consulta de stock por producto -->
<div class="card">
    <div class="card-titulo">Consultar stock por producto</div>
    <form id="form-consulta" novalidate>
        <div class="form-grid">
            <div class="form-campo">
                <label class="form-label">Producto</label>
                <select id="inv-producto" class="form-select">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($productosLista as $p): ?>
                        <option value="<?= $p['id_Producto'] ?>"><?= htmlspecialchars($p['Nombre'] . ' (' . $p['Codigo'] . ')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-campo" style="align-self:end;">
                <button type="submit" class="btn-primario">Buscar</button>
            </div>
        </div>
    </form>
    <p class="pagina-subtitulo" style="margin-top:12px;">
        Stock disponible = Stock total menos Stock reservado. El reservado son unidades en ventas pendientes.
    </p>
    <div class="tabla-contenedor">
        <table class="tabla">
            <thead>
                <tr><th>ID Inventario</th><th>Talla</th><th>Color</th><th>Stock total</th><th>Reservado</th><th>Disponible</th></tr>
            </thead>
            <tbody id="tabla-inventario">
                <tr><td colspan="6" class="sin-datos">Selecciona un producto y pulsa Buscar</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php if ($puedeAjustar): ?>
<!-- Ajuste manual (solo Administrador) -->
<div class="card">
    <div class="card-titulo">Registrar ajuste manual de inventario</div>
    <form id="form-ajuste" novalidate>
        <div class="form-grid">
            <div class="form-campo">
                <label class="form-label">ID de inventario</label>
                <input type="number" id="aj-id" class="form-input" min="1" />
            </div>
            <div class="form-campo">
                <label class="form-label">Cantidad ajustada (+ suma / - resta)</label>
                <input type="number" id="aj-cantidad" class="form-input" />
            </div>
            <div class="form-campo form-grid-full">
                <label class="form-label">Motivo</label>
                <input type="text" id="aj-motivo" class="form-input" />
            </div>
        </div>
        <div class="btn-grupo">
            <button type="submit" class="btn-primario">Registrar ajuste</button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
const API_INV = '<?= $base ?>/api/inventario.php';
const PUEDE_AJUSTAR = <?= $puedeAjustar ? 'true' : 'false' ?>;

function escInv(t) { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; }

document.getElementById('form-consulta').addEventListener('submit', async function (e) {
    e.preventDefault();
    const id = document.getElementById('inv-producto').value;
    if (!esRequerido(id)) { alertaError('Debe seleccionar un producto'); return; }
    const r = await enviarAccion(API_INV, { accion: 'consultar', id_Producto: id });
    const tbody = document.getElementById('tabla-inventario');
    if (!r.ok) { alertaError(r.mensaje); return; }
    if (r.datos.length === 0) { tbody.innerHTML = '<tr><td colspan="6" class="sin-datos">Este producto no tiene inventario registrado</td></tr>'; return; }
    tbody.innerHTML = r.datos.map(i => `<tr>
        <td>${i.id_Inventario}</td>
        <td>${escInv(i.Talla)}</td>
        <td>${escInv(i.Color)}</td>
        <td>${i.Stock}</td>
        <td>${i.Stock_Reservado}</td>
        <td>${i.Disponible}</td>
    </tr>`).join('');
});

const formAjuste = document.getElementById('form-ajuste');
if (formAjuste) {
    formAjuste.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = document.getElementById('aj-id').value;
        const cantidad = document.getElementById('aj-cantidad').value;
        const motivo = document.getElementById('aj-motivo').value;
        const error = primerError([
            { condicion: esRequerido(id), mensaje: 'Debe indicar el ID de inventario' },
            { condicion: esRequerido(cantidad) && parseInt(cantidad) !== 0, mensaje: 'La cantidad ajustada debe ser un número distinto de cero' },
            { condicion: esRequerido(motivo), mensaje: 'El motivo es obligatorio' },
        ]);
        if (error) { alertaError(error); return; }
        const r = await enviarAccion(API_INV, { accion: 'ajustar', id_Inventario: id, cantidad, motivo });
        if (r.ok) {
            notificarExito(r.mensaje);
            this.reset();
            // Si el producto consultado coincide, refrescar
            document.getElementById('form-consulta').dispatchEvent(new Event('submit'));
        } else { alertaError(r.mensaje); }
    });
}
</script>
