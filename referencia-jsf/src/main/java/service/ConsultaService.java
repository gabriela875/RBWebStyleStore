package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.*;
import repository.*;
import java.util.List;

@ApplicationScoped
public class ConsultaService {

	@Inject
	private VentaRepository ventaRepo;

	@Inject
	private EmpleadoRepository empleadoRepo;

	@Inject
	private ClienteRepository clienteRepo;

	@Inject
	private ProductoRepository productoRepo;

	@Inject
	private InventarioRepository inventarioRepo;

	@Inject
	private AbastecimientoRepository abastecimientoRepo;

	// ── Consultas de ventas ────────────────────────────────────────────────

	// Todas las ventas registradas
	public List<Venta> consultarTodasLasVentas() {
		return ventaRepo.listarTodas();
	}

	// Ventas de un empleado especifico
	public List<Venta> consultarVentasPorEmpleado(int idEmpleado) {
		return ventaRepo.listarPorEmpleado(idEmpleado);
	}

	// Ventas de un cliente especifico
	public List<Venta> consultarVentasPorCliente(int idCliente) {
		return ventaRepo.listarPorCliente(idCliente);
	}

	// Ventas pendientes que llevan mas de 24 horas
	public List<Venta> consultarVentasVencidas() {
		return ventaRepo.listarPendientesVencidas();
	}

	// Detalle de una venta especifica
	public List<DetalleVenta> consultarDetalleVenta(int idVenta) {
		return ventaRepo.listarDetallesPorVenta(idVenta);
	}

	// Pagos de una venta especifica
	public List<Pago> consultarPagosPorVenta(int idVenta) {
		return ventaRepo.listarPagosPorVenta(idVenta);
	}

	// Todas las anulaciones — solo Admin
	public List<AnulacionVenta> consultarAnulaciones() {
		return ventaRepo.listarTodasAnulaciones();
	}

	// ── Consultas de empleados ─────────────────────────────────────────────

	// Todos los empleados
	public List<Empleado> consultarTodosLosEmpleados() {
		return empleadoRepo.listarTodos();
	}

	// ── Consultas de clientes ──────────────────────────────────────────────

	// Todos los clientes
	public List<Cliente> consultarTodosLosClientes() {
		return clienteRepo.listarTodos();
	}

	// ── Consultas de productos ─────────────────────────────────────────────

	// Todos los productos
	public List<Producto> consultarTodosLosProductos() {
		return productoRepo.listarTodos();
	}

	// Solo productos disponibles
	public List<Producto> consultarProductosDisponibles() {
		return productoRepo.listarDisponibles();
	}

	// Combinaciones de inventario de un producto
	public List<Inventario> consultarInventarioPorProducto(int idProducto) {
		return inventarioRepo.listarPorProducto(idProducto);
	}

	// ── Consultas de abastecimiento ────────────────────────────────────────

	// Todas las entradas de mercancia
	public List<EntradaMercancia> consultarTodasLasEntradas() {
		return abastecimientoRepo.listarTodas();
	}

	// Detalle de una entrada especifica
	public List<DetalleEntrada> consultarDetalleEntrada(int idEntrada) {
		return abastecimientoRepo.listarDetallesPorEntrada(idEntrada);
	}

	// Entradas por proveedor
	public List<EntradaMercancia> consultarEntradasPorProveedor(int idProveedor) {
		return abastecimientoRepo.listarPorProveedor(idProveedor);
	}
}
