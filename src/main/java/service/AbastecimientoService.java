package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.DetalleEntrada;
import model.EntradaMercancia;
import model.Inventario;
import model.Producto;
import repository.AbastecimientoRepository;
import repository.InventarioRepository;
import java.time.LocalDateTime;
import java.util.List;

@ApplicationScoped
public class AbastecimientoService {

	@Inject
	private AbastecimientoRepository abastecimientoRepo;

	@Inject
	private InventarioRepository inventarioRepo;

	@Inject
	private ProductoService productoService;

	@Inject
	private InventarioService inventarioService;

	// Registrar una entrada de mercancia con sus detalles
	public void registrarEntrada(EntradaMercancia entrada, List<DetalleEntrada> detalles) {

		// Registrar fecha y hora de la entrada
		entrada.setFecha(LocalDateTime.now());

		// Validar cada detalle antes de guardar
		for (DetalleEntrada detalle : detalles) {

			// Obtener el inventario de esa combinacion
			Inventario inventario = inventarioRepo.buscarPorId(detalle.getInventario().getIdInventario());

			Producto producto = inventario.getProducto();

			// El proveedor del detalle debe coincidir con el del producto
			if (producto.getProveedor().getIdProveedor() != detalle.getProveedor().getIdProveedor()) {
				throw new IllegalArgumentException(
						"El proveedor del detalle no coincide con el del producto: " + producto.getNombre());
			}

			// El proveedor del detalle debe coincidir con el de la entrada
			if (entrada.getProveedor().getIdProveedor() != detalle.getProveedor().getIdProveedor()) {
				throw new IllegalArgumentException("El proveedor del detalle no coincide con el de la entrada");
			}

			// No se puede ingresar mercancia de un producto descontinuado
			if (producto.getEstado() == Producto.EstadoProducto.descontinuado) {
				throw new IllegalStateException(
						"No se puede ingresar mercancía de un producto descontinuado: " + producto.getNombre());
			}

			// La cantidad debe ser mayor a cero
			if (detalle.getCantidad() <= 0) {
				throw new IllegalArgumentException("La cantidad debe ser mayor a cero");
			}
		}

		// Guardar la entrada
		abastecimientoRepo.guardar(entrada);

		// Guardar cada detalle y actualizar el stock
		for (DetalleEntrada detalle : detalles) {
			detalle.setEntrada(entrada);
			abastecimientoRepo.guardarDetalle(detalle);

			// Sumar la cantidad al stock de esa combinacion
			inventarioService.reponerStock(detalle.getInventario().getIdInventario(), detalle.getCantidad());
		}
	}

	// Listar todas las entradas de mercancia
	public List<EntradaMercancia> listarEntradas() {
		return abastecimientoRepo.listarTodas();
	}

	// Listar entradas por proveedor
	public List<EntradaMercancia> listarEntradasPorProveedor(int idProveedor) {
		return abastecimientoRepo.listarPorProveedor(idProveedor);
	}

	// Listar detalles de una entrada especifica
	public List<DetalleEntrada> listarDetalles(int idEntrada) {
		return abastecimientoRepo.listarDetallesPorEntrada(idEntrada);
	}
}
