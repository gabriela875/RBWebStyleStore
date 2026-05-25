package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import jakarta.persistence.EntityManager;
import model.*;
import repository.VentaRepository;
import util.JpaUtil;
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

	// Buscar estado de venta por nombre
	private EstadoVenta buscarEstadoVenta(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoVenta e WHERE e.nombre = :nombre", EstadoVenta.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Crear una nueva venta en estado pendiente
	public Venta crearVenta(Empleado empleado, Cliente cliente) {
		Venta venta = new Venta();
		venta.setEmpleado(empleado);
		venta.setCliente(cliente);
		venta.setFechaHora(LocalDateTime.now());
		venta.setEstadoVenta(buscarEstadoVenta("Pendiente"));
		venta.setTotal(BigDecimal.ZERO);
		ventaRepo.guardar(venta);
		return venta;
	}

	// Agregar detalle de venta
	public void agregarDetalle(int idVenta, Inventario inventario, int cantidad) {
		Venta venta = ventaRepo.buscarPorId(idVenta);

		if (!venta.getEstadoVenta().getNombre().equals("Pendiente")) {
			throw new IllegalStateException("Solo se pueden agregar productos a ventas pendientes");
		}

		String estadoProducto = inventario.getProducto().getEstadoProducto().getNombre();
		if (estadoProducto.equals("Descontinuado")) {
			throw new IllegalStateException("No se puede vender un producto descontinuado");
		}
		if (estadoProducto.equals("Agotado")) {
			throw new IllegalStateException("No se puede vender un producto agotado");
		}

		inventarioService.reservarStock(inventario.getIdInventario(), cantidad);

		BigDecimal precioFinal = productoService.calcularPrecioFinal(inventario.getProducto().getIdProducto());
		BigDecimal precioBase = inventario.getProducto().getPrecioBase();
		BigDecimal descuentoAplicado = null;
		if (precioFinal.compareTo(precioBase) < 0) {
			descuentoAplicado = precioBase.subtract(precioFinal);
		}

		DetalleVenta detalle = new DetalleVenta();
		detalle.setVenta(venta);
		detalle.setInventario(inventario);
		detalle.setCantidad(cantidad);
		detalle.setPrecioUnitario(precioFinal);
		detalle.setDescuentoAplicado(descuentoAplicado);
		ventaRepo.guardarDetalle(detalle);

		recalcularTotal(idVenta);
	}

	// Quitar un producto de una venta pendiente
	public void quitarDetalle(int idVenta, int idDetalle) {
		Venta venta = ventaRepo.buscarPorId(idVenta);

		if (!venta.getEstadoVenta().getNombre().equals("Pendiente")) {
			throw new IllegalStateException("Solo se pueden quitar productos de ventas pendientes");
		}

		List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
		DetalleVenta detalle = detalles.stream().filter(d -> d.getIdDetalleVenta() == idDetalle).findFirst()
				.orElseThrow(() -> new IllegalArgumentException("Detalle no encontrado"));

		inventarioService.liberarStock(detalle.getInventario().getIdInventario(), detalle.getCantidad());
		ventaRepo.eliminarDetalle(detalle);
		recalcularTotal(idVenta);
	}

	// Completar una venta
	public void completarVenta(int idVenta) {
		Venta venta = ventaRepo.buscarPorId(idVenta);

		if (!venta.getEstadoVenta().getNombre().equals("Pendiente")) {
			throw new IllegalStateException("Solo se pueden completar ventas pendientes");
		}

		long totalDetalles = ventaRepo.contarDetallesPorVenta(idVenta);
		if (totalDetalles == 0) {
			throw new IllegalStateException("La venta debe tener al menos un producto");
		}

		if (!pagosCubrenTotal(idVenta)) {
			throw new IllegalStateException("La suma de los pagos no cubre el total de la venta");
		}

		List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
		for (DetalleVenta detalle : detalles) {
			inventarioService.descontarStock(detalle.getInventario().getIdInventario(), detalle.getCantidad());
		}

		venta.setEstadoVenta(buscarEstadoVenta("Completada"));
		ventaRepo.actualizar(venta);
	}

	// Anular una venta pendiente
	public void anularVenta(int idVenta, String motivo, Empleado empleado) {
		Venta venta = ventaRepo.buscarPorId(idVenta);

		if (!venta.getEstadoVenta().getNombre().equals("Pendiente")) {
			throw new IllegalStateException("Solo se pueden anular ventas pendientes");
		}

		List<DetalleVenta> detalles = ventaRepo.listarDetallesPorVenta(idVenta);
		for (DetalleVenta detalle : detalles) {
			inventarioService.liberarStock(detalle.getInventario().getIdInventario(), detalle.getCantidad());
		}

		ventaRepo.eliminarPagosPorVenta(idVenta);

		AnulacionVenta anulacion = new AnulacionVenta();
		anulacion.setVenta(venta);
		anulacion.setEmpleado(empleado);
		anulacion.setMotivo(motivo);
		anulacion.setFechaHora(LocalDateTime.now());
		ventaRepo.guardarAnulacion(anulacion);

		venta.setEstadoVenta(buscarEstadoVenta("Anulada"));
		ventaRepo.actualizar(venta);
	}

	// Registrar un pago
	public void registrarPago(int idVenta, Pago pago) {
		Venta venta = ventaRepo.buscarPorId(idVenta);

		if (!venta.getEstadoVenta().getNombre().equals("Pendiente")) {
			throw new IllegalStateException("Solo se pueden registrar pagos en ventas pendientes");
		}

		// Tarjeta o transferencia requieren referencia
		String metodo = pago.getMetodoPago().getNombre();
		if (!metodo.equals("Efectivo") && (pago.getReferencia() == null || pago.getReferencia().isBlank())) {
			throw new IllegalArgumentException("Tarjeta y transferencia requieren número de referencia");
		}

		// Efectivo — calcular cambio si aplica
		if (metodo.equals("Efectivo")) {
			BigDecimal diferencia = pago.getValor().subtract(venta.getTotal());
			if (diferencia.compareTo(BigDecimal.ZERO) > 0) {
				pago.setCambio(diferencia);
			}
		}

		pago.setVenta(venta);
		ventaRepo.guardarPago(pago);
	}

	// Recalcular total
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

	// Verificar que los pagos cubran el total
	private boolean pagosCubrenTotal(int idVenta) {
		Venta venta = ventaRepo.buscarPorId(idVenta);
		List<Pago> pagos = ventaRepo.listarPagosPorVenta(idVenta);
		BigDecimal sumaPagos = BigDecimal.ZERO;
		for (Pago pago : pagos) {
			sumaPagos = sumaPagos.add(pago.getValor());
		}
		return sumaPagos.compareTo(venta.getTotal()) >= 0;
	}

	// Consultas
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
