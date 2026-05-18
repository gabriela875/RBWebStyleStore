package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.Descuento;
import model.Inventario;
import model.Producto;
import model.Producto.EstadoProducto;
import repository.InventarioRepository;
import repository.ProductoRepository;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.util.List;

@ApplicationScoped
public class ProductoService {

	@Inject
	private ProductoRepository productoRepo;

	@Inject
	private InventarioRepository inventarioRepo;

	//  Producto

	// Registrar un nuevo producto
	public void registrar(Producto producto) {

		// Verificar que el codigo sea unico
		if (productoRepo.buscarPorCodigo(producto.getCodigo()) != null) {
			throw new IllegalArgumentException("Ya existe un producto con ese código");
		}

		// El precio debe ser mayor a cero
		if (producto.getPrecioBase().compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalArgumentException("El precio debe ser mayor a cero");
		}

		// El producto inicia como disponible
		producto.setEstado(EstadoProducto.disponible);

		productoRepo.guardar(producto);
	}

	// Actualizar precio base — no afecta ventas ya completadas
	public void actualizarPrecio(int idProducto, BigDecimal nuevoPrecio) {

		if (nuevoPrecio.compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalArgumentException("El precio debe ser mayor a cero");
		}

		Producto producto = productoRepo.buscarPorId(idProducto);
		producto.setPrecioBase(nuevoPrecio);
		productoRepo.actualizar(producto);
	}

	// Marcar producto como descontinuado — decision permanente del Admin
	public void descontinuar(int idProducto) {

		Producto producto = productoRepo.buscarPorId(idProducto);

		// Verificar que no este ya descontinuado
		if (producto.getEstado() == EstadoProducto.descontinuado) {
			throw new IllegalStateException("El producto ya está descontinuado");
		}

		// Desactivar descuento activo si existe
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);
		if (descuento != null) {
			descuento.setActivo(false);
			productoRepo.actualizarDescuento(descuento);
		}

		producto.setEstado(EstadoProducto.descontinuado);
		productoRepo.actualizar(producto);
	}

	// Verificar y actualizar estado agotado automaticamente
	public void verificarEstadoAgotado(int idProducto) {

		Producto producto = productoRepo.buscarPorId(idProducto);

		// No tocar productos descontinuados
		if (producto.getEstado() == EstadoProducto.descontinuado) {
			return;
		}

		// Si todas las combinaciones estan agotadas — marcar como agotado
		if (inventarioRepo.todoCombinacionesAgotadas(idProducto)) {
			producto.setEstado(EstadoProducto.agotado);
		} else {
			// Si recibio nuevas unidades — volver a disponible
			producto.setEstado(EstadoProducto.disponible);
		}

		productoRepo.actualizar(producto);
	}

	public Producto buscarPorId(int id) {
		return productoRepo.buscarPorId(id);
	}

	public List<Producto> listarTodos() {
		return productoRepo.listarTodos();
	}

	public List<Producto> listarDisponibles() {
		return productoRepo.listarDisponibles();
	}

	// Descuento 

	// Registrar un descuento para un producto
	public void registrarDescuento(Descuento descuento) {

		Producto producto = descuento.getProducto();

		// No se puede agregar descuento a producto descontinuado
		if (producto.getEstado() == EstadoProducto.descontinuado) {
			throw new IllegalStateException("No se puede agregar un descuento a un producto descontinuado");
		}

		// Verificar que no haya otro descuento activo para el mismo producto
		Descuento existente = productoRepo.buscarDescuentoActivo(producto.getIdProducto());
		if (existente != null) {
			throw new IllegalStateException("El producto ya tiene un descuento activo");
		}

		// Verificar que las fechas sean coherentes
		if (descuento.getFechaFin().isBefore(descuento.getFechaInicio())) {
			throw new IllegalArgumentException("La fecha de fin no puede ser anterior a la fecha de inicio");
		}

		// Verificar que el descuento no iguale ni supere el precio base
		validarValorDescuento(descuento, producto.getPrecioBase());

		descuento.setActivo(true);
		productoRepo.guardarDescuento(descuento);
	}

	// Desactivar un descuento manualmente
	public void desactivarDescuento(int idProducto) {
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);

		if (descuento == null) {
			throw new IllegalStateException("El producto no tiene un descuento activo");
		}

		descuento.setActivo(false);
		productoRepo.actualizarDescuento(descuento);
	}

	// Calcular el precio final aplicando el descuento si esta vigente
	public BigDecimal calcularPrecioFinal(int idProducto) {

		Producto producto = productoRepo.buscarPorId(idProducto);
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);

		// Sin descuento vigente — retorna precio base
		if (descuento == null) {
			return producto.getPrecioBase();
		}

		BigDecimal precioFinal;

		if (descuento.getTipo() == Descuento.TipoDescuento.porcentaje) {
			// Precio final = precio base - (precio base * porcentaje / 100)
			BigDecimal factor = descuento.getValor().divide(BigDecimal.valueOf(100));
			BigDecimal monto = producto.getPrecioBase().multiply(factor);
			precioFinal = producto.getPrecioBase().subtract(monto);
		} else {
			// Precio final = precio base - valor fijo
			precioFinal = producto.getPrecioBase().subtract(descuento.getValor());
		}

		// El precio final siempre debe ser mayor a cero
		if (precioFinal.compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalStateException("El descuento no puede dejar el precio en cero o negativo");
		}

		return precioFinal;
	}

	// Verificar que el valor del descuento no iguale ni supere el precio base
	private void validarValorDescuento(Descuento descuento, BigDecimal precioBase) {

		if (descuento.getTipo() == Descuento.TipoDescuento.porcentaje) {
			// El porcentaje no puede ser 100 o mas
			if (descuento.getValor().compareTo(BigDecimal.valueOf(100)) >= 0) {
				throw new IllegalArgumentException("El porcentaje de descuento debe ser menor a 100");
			}
		} else {
			// El valor fijo no puede igualar ni superar el precio base
			if (descuento.getValor().compareTo(precioBase) >= 0) {
				throw new IllegalArgumentException("El valor del descuento no puede igualar ni superar el precio base");
			}
		}
	}
}