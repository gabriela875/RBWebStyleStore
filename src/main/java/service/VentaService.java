package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.*;
import model.Venta.EstadoVenta;
import repository.VentaRepository;
import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.List;

@ApplicationScoped
public class VentaService {

    @Inject
    private VentaRepository ventaRepo;

    @Inject
    private InventarioService inventarioService;

    @Inject
    private ProductoService productoService;

    // ── Venta ──────────────────────────────────────────────────────────────

    // Crear una nueva venta en estado pendiente
    public Venta crearVenta(Empleado empleado, Cliente cliente) {

        Venta venta = new Venta();
        venta.setEmpleado(empleado);
        venta.setCliente(cliente);
        venta.setFechaHora(LocalDateTime.now());
        venta.setEstado(EstadoVenta.pendiente);
        venta.setTotal(BigDecimal.ZERO);

        ventaRepo.guardar(venta);
        return venta;
    }

    // Agregar detalle de venta de forma sencilla
    public void agregarDetalle(int idVenta, Inventario inventario, int cantidad) {

        Venta venta = ventaRepo.buscarPorId(idVenta);

        // Solo ventas pendientes
        if (venta.getEstado() != EstadoVenta.pendiente) {
            throw new IllegalStateException(
                "Solo se pueden agregar productos a ventas pendientes");
        }

        // Reservar stock
        inventarioService.reservarStock(inventario.getIdInventario(), cantidad);

        // Obtener precio final con descuento si existe
        BigDecimal precioFinal = productoService
            .calcularPrecioFinal(inventario.getProducto().getIdProducto());

        // Obtener descuento aplicado si existe
        Descuento descuento = null;
        // El precio base del producto
        BigDecimal precioBase = inventario.getProducto().getPrecioBase();
        // Si el precio final es menor al base habia descuento
        BigDecimal descuentoAplicado = null;
        if (precioFinal.compareTo(precioBase) < 0) {
            descuentoAplicado = precioBase.subtract(precioFinal);
        }

        // Crear el detalle
        DetalleVenta detalle = new DetalleVenta();
        detalle.setVenta(venta);
        detalle.setInventario(inventario);
        detalle.setCantidad(cantidad);
        detalle.setPrecioUnitario(precioFinal);
        detalle.setDescuentoAplicado(descuentoAplicado);

        ventaRepo.guardarDetalle(detalle);

        // Actualizar total de la venta
        recalcularTotal(idVenta);
    }

    // Quitar un producto de una venta pendiente
    public void quitarDetalle(int idVenta, int idDetalle) {

        Venta venta = ventaRepo.buscarPorId(idVenta);

        // Solo ventas pendientes
        if (venta.getEstado() != EstadoVenta.pendiente) {
            throw new IllegalStateException(
                "Solo se pueden quitar productos de ventas pendientes");
        }

        // Buscar el detalle en la lista de la venta
        List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
        DetalleVenta detalle = detalles.stream()
            .filter(d -> d.getIdDetalleVenta() == idDetalle)
            .findFirst()
            .orElseThrow(() -> new IllegalArgumentException("Detalle no encontrado"));

        // Liberar el stock reservado
        inventarioService.liberarStock(
            detalle.getInventario().getIdInventario(),
            detalle.getCantidad());

        // Eliminar el detalle
        ventaRepo.eliminarDetalle(detalle);

        // Actualizar total
        recalcularTotal(idVenta);
    }

    // Completar una venta — valida pagos y descuenta stock
    public void completarVenta(int idVenta) {

        Venta venta = ventaRepo.buscarPorId(idVenta);

        // Verificar que este pendiente
        if (venta.getEstado() != EstadoVenta.pendiente) {
            throw new IllegalStateException("Solo se pueden completar ventas pendientes");
        }

        // Verificar que tenga al menos un producto
        long totalDetalles = ventaRepo.contarDetallesPorVenta(idVenta);
        if (totalDetalles == 0) {
            throw new IllegalStateException(
                "La venta debe tener al menos un producto");
        }

        // Verificar que los pagos sumen el total
        if (!pagosCubrenTotal(idVenta)) {
            throw new IllegalStateException(
                "La suma de los pagos no cubre el total de la venta");
        }

        // Descontar stock definitivamente por cada detalle
        List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
        for (DetalleVenta detalle : detalles) {
            inventarioService.descontarStock(
                detalle.getInventario().getIdInventario(),
                detalle.getCantidad());
        }

        // Cambiar estado a completada
        venta.setEstado(EstadoVenta.completada);
        ventaRepo.actualizar(venta);
    }

    // Anular una venta pendiente
    public void anularVenta(int idVenta, String motivo, Empleado empleado) {

        Venta venta = ventaRepo.buscarPorId(idVenta);

        // Solo se pueden anular ventas pendientes
        if (venta.getEstado() != EstadoVenta.pendiente) {
            throw new IllegalStateException("Solo se pueden anular ventas pendientes");
        }

        // Liberar el stock reservado por cada detalle
        List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
        for (DetalleVenta detalle : detalles) {
            inventarioService.liberarStock(
                detalle.getInventario().getIdInventario(),
                detalle.getCantidad());
        }

        // Eliminar pagos parciales si los habia
        ventaRepo.eliminarPagosPorVenta(idVenta);

        // Registrar la anulacion
        AnulacionVenta anulacion = new AnulacionVenta();
        anulacion.setVenta(venta);
        anulacion.setEmpleado(empleado);
        anulacion.setMotivo(motivo);
        anulacion.setFechaHora(LocalDateTime.now());
        ventaRepo.guardarAnulacion(anulacion);

        // Cambiar estado a anulada
        venta.setEstado(EstadoVenta.anulada);
        ventaRepo.actualizar(venta);
    }

    // Recalcular el total sumando los subtotales de cada detalle
    private void recalcularTotal(int idVenta) {

        List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);

        BigDecimal total = BigDecimal.ZERO;
        for (DetalleVenta detalle : detalles) {
            total = total.add(detalle.getSubtotal());
        }

        Venta venta = ventaRepo.buscarPorId(idVenta);
        venta.setTotal(total);
        ventaRepo.actualizar(venta);
    }

    // Verificar que la suma de pagos cubra el total de la venta
    private boolean pagosCubrenTotal(int idVenta) {

        Venta venta = ventaRepo.buscarPorId(idVenta);
        List<Pago> pagos = ventaRepo.listarPagosPorVenta(idVenta);

        BigDecimal sumaPagos = BigDecimal.ZERO;
        for (Pago pago : pagos) {
            sumaPagos = sumaPagos.add(pago.getValor());
        }

        return sumaPagos.compareTo(venta.getTotal()) >= 0;
    }

    // ── Pagos ──────────────────────────────────────────────────────────────

    // Registrar un pago para una venta pendiente
    public void registrarPago(int idVenta, Pago pago) {

        Venta venta = ventaRepo.buscarPorId(idVenta);

        // Solo ventas pendientes aceptan pagos
        if (venta.getEstado() != EstadoVenta.pendiente) {
            throw new IllegalStateException(
                "Solo se pueden registrar pagos en ventas pendientes");
        }

        // Si es tarjeta o transferencia debe tener referencia
        if (pago.getMetodo() != Pago.MetodoPago.efectivo
                && (pago.getReferencia() == null || pago.getReferencia().isBlank())) {
            throw new IllegalArgumentException(
                "Tarjeta y transferencia requieren número de referencia");
        }

        // Si es efectivo calcular cambio si aplica
        if (pago.getMetodo() == Pago.MetodoPago.efectivo) {
            BigDecimal diferencia = pago.getValor().subtract(venta.getTotal());
            if (diferencia.compareTo(BigDecimal.ZERO) > 0) {
                pago.setCambio(diferencia);
            }
        }

        pago.setVenta(venta);
        ventaRepo.guardarPago(pago);
    }

    // ── Consultas ──────────────────────────────────────────────────────────

    public Venta buscarPorId(int id) {
        return ventaRepo.buscarPorId(id);
    }

    public List<Venta> listarTodas() {
        return ventaRepo.listarTodas();
    }

    public List<Venta> listarPorEmpleado(int idEmpleado) {
        return ventaRepo.listarPorEmpleado(idEmpleado);
    }

    public List<DetalleVenta> listarDetalles(int idVenta) {
        return ventaRepo.listarDetallesPorVenta(idVenta);
    }

    public List<Pago> listarPagos(int idVenta) {
        return ventaRepo.listarPagosPorVenta(idVenta);
    }

    public List<Venta> listarPendientesVencidas() {
        return ventaRepo.listarPendientesVencidas();
    }

    public List<AnulacionVenta> listarAnulaciones() {
        return ventaRepo.listarTodasAnulaciones();
    }
}
