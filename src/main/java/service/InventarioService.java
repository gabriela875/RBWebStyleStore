package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.AjusteInventario;
import model.Inventario;
import repository.InventarioRepository;
import java.time.LocalDateTime;
import java.util.List;

@ApplicationScoped
public class InventarioService {

	@Inject
	private InventarioRepository inventarioRepo;

	@Inject
	private ProductoService productoService;

	// Buscar inventario por producto, talla y color
	public Inventario buscarCombinacion(int idProducto, int idTalla, int idColor) {
		return inventarioRepo.buscarPorProductoTallaColor(idProducto, idTalla, idColor);
	}

	// Listar todas las combinaciones de un producto
	public List<Inventario> listarPorProducto(int idProducto) {
		return inventarioRepo.listarPorProducto(idProducto);
	}

	// Buscar una combinación de inventario por su ID
	public Inventario buscarPorId(int idInventario) {
		return inventarioRepo.buscarPorId(idInventario);
	}

	// Reservar stock cuando se agrega un producto a una venta pendiente
	public void reservarStock(int idInventario, int cantidad) {

		Inventario inventario = inventarioRepo.buscarPorId(idInventario);

		// Verificar que haya stock disponible suficiente
		if (inventario.getStockDisponible() < cantidad) {
			throw new IllegalStateException("No hay stock disponible suficiente para esa combinación");
		}

		inventario.setStockReservado(inventario.getStockReservado() + cantidad);
		inventarioRepo.actualizar(inventario);
	}

	// Liberar stock reservado cuando se quita un producto de una venta o se anula
	public void liberarStock(int idInventario, int cantidad) {

		Inventario inventario = inventarioRepo.buscarPorId(idInventario);
		int nuevoReservado = inventario.getStockReservado() - cantidad;

		// El reservado no puede quedar negativo
		if (nuevoReservado < 0) {
			nuevoReservado = 0;
		}

		inventario.setStockReservado(nuevoReservado);
		inventarioRepo.actualizar(inventario);
	}

	// Descontar stock definitivamente al completar una venta
	public void descontarStock(int idInventario, int cantidad) {

		Inventario inventario = inventarioRepo.buscarPorId(idInventario);

		// Descontar del stock total y liberar el reservado
		inventario.setStock(inventario.getStock() - cantidad);
		inventario.setStockReservado(inventario.getStockReservado() - cantidad);

		inventarioRepo.actualizar(inventario);

		// Verificar si el producto quedo agotado en todas sus combinaciones
		productoService.verificarEstadoAgotado(inventario.getProducto().getIdProducto());
	}

	// Reponer stock cuando se anula una venta
	public void reponerStock(int idInventario, int cantidad) {

		Inventario inventario = inventarioRepo.buscarPorId(idInventario);
		inventario.setStock(inventario.getStock() + cantidad);
		inventarioRepo.actualizar(inventario);

		// Verificar si el producto vuelve a estar disponible
		productoService.verificarEstadoAgotado(inventario.getProducto().getIdProducto());
	}

	// Registrar ajuste manual de inventario — solo el Admin
	public void registrarAjuste(AjusteInventario ajuste) {

		Inventario inventario = inventarioRepo.buscarPorId(ajuste.getInventario().getIdInventario());

		int stockResultante = inventario.getStock() + ajuste.getCantidadAjustada();

		// El stock no puede quedar negativo
		if (stockResultante < 0) {
			throw new IllegalStateException("El ajuste dejaría el stock en negativo");
		}

		// El stock no puede quedar por debajo del reservado
		if (stockResultante < inventario.getStockReservado()) {
			throw new IllegalStateException("El ajuste no puede dejar el stock por debajo de las unidades reservadas");
		}

		// Aplicar el ajuste
		inventario.setStock(stockResultante);
		inventarioRepo.actualizar(inventario);

		// Registrar el ajuste con fecha y hora actuales
		ajuste.setFecha(LocalDateTime.now());
		inventarioRepo.guardarAjuste(ajuste);

		// Verificar estado agotado del producto
		productoService.verificarEstadoAgotado(inventario.getProducto().getIdProducto());
	}
}
