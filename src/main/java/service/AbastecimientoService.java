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
		entrada.setFecha(LocalDateTime.now());

		for (DetalleEntrada detalle : detalles) {
			Producto producto = productoService.buscarPorId(detalle.getInventario().getProducto().getIdProducto());

			// No se puede ingresar mercancia de un producto descontinuado
			if (producto.getEstado() == Producto.EstadoProducto.descontinuado) {
				throw new IllegalStateException(
						"No se puede ingresar mercancía de un producto descontinuado: " + producto.getNombre());
			}

			// El proveedor de la entrada debe coincidir con el del producto
			if (producto.getProveedor().getIdProveedor() != entrada.getProveedor().getIdProveedor()) {
				throw new IllegalArgumentException(
						"El proveedor seleccionado no coincide con el proveedor del producto: " + producto.getNombre());
			}

			if (detalle.getCantidad() <= 0) {
				throw new IllegalArgumentException("La cantidad debe ser mayor a cero");
			}

			// Buscar si ya existe la combinacion producto+talla+color
			Inventario inventario = inventarioRepo.buscarPorProductoTallaColor(
					detalle.getInventario().getProducto().getIdProducto(),
					detalle.getInventario().getTalla().getIdTalla(), detalle.getInventario().getColor().getIdColor());

			if (inventario == null) {
				inventario = new Inventario();
				inventario.setProducto(
						productoService.buscarPorId(detalle.getInventario().getProducto().getIdProducto()));
				inventario.setTalla(detalle.getInventario().getTalla());
				inventario.setColor(detalle.getInventario().getColor());
				inventario.setStock(0);
				inventario.setStockReservado(0);
				inventarioRepo.guardarConMerge(inventario);

				// Reobtener de BD para tener el ID generado
				inventario = inventarioRepo.buscarPorProductoTallaColor(
						detalle.getInventario().getProducto().getIdProducto(),
						detalle.getInventario().getTalla().getIdTalla(),
						detalle.getInventario().getColor().getIdColor());
			}

			detalle.setInventario(inventario);
		}

		abastecimientoRepo.guardar(entrada);

		for (DetalleEntrada detalle : detalles) {
			detalle.setEntrada(entrada);
			abastecimientoRepo.guardarDetalle(detalle);
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
