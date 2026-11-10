package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import jakarta.persistence.EntityManager;
import model.*;
import repository.InventarioRepository;
import repository.ProductoRepository;
import util.JpaUtil;
import java.math.BigDecimal;
import java.util.List;

@ApplicationScoped
public class ProductoService {

	@Inject
	private ProductoRepository productoRepo;

	@Inject
	private InventarioRepository inventarioRepo;

	// Buscar estado de producto por nombre
	private EstadoProducto buscarEstadoProducto(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoProducto e WHERE e.nombre = :nombre", EstadoProducto.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Buscar estado de descuento por nombre
	private EstadoDescuento buscarEstadoDescuento(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoDescuento e WHERE e.nombre = :nombre", EstadoDescuento.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Registrar un nuevo producto
	public void registrar(Producto producto) {
		if (productoRepo.buscarPorCodigo(producto.getCodigo()) != null) {
			throw new IllegalArgumentException("Ya existe un producto con ese código");
		}
		if (producto.getPrecioBase().compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalArgumentException("El precio debe ser mayor a cero");
		}
		producto.setEstadoProducto(buscarEstadoProducto("Disponible"));
		productoRepo.guardar(producto);
	}

	// Eliminar producto descontinuado sin ventas
	public void eliminar(int idProducto) {
		Producto producto = productoRepo.buscarPorId(idProducto);
		if (!producto.getEstadoProducto().getNombre().equals("Descontinuado")) {
			throw new IllegalStateException("Solo se pueden eliminar productos descontinuados");
		}
		if (productoRepo.tieneVentas(idProducto)) {
			throw new IllegalStateException("No se puede eliminar un producto que tiene ventas registradas");
		}
		productoRepo.eliminar(idProducto);
	}

	// Editar producto
	public void editar(Producto producto) {
		Producto existente = productoRepo.buscarPorId(producto.getIdProducto());
		Producto porCodigo = productoRepo.buscarPorCodigo(producto.getCodigo());
		if (porCodigo != null && porCodigo.getIdProducto() != producto.getIdProducto()) {
			throw new IllegalArgumentException("Ya existe otro producto con ese código");
		}
		producto.setEstadoProducto(existente.getEstadoProducto());
		producto.setPrecioBase(existente.getPrecioBase());
		productoRepo.actualizar(producto);
	}

	// Actualizar precio base
	public void actualizarPrecio(int idProducto, BigDecimal nuevoPrecio) {
		if (nuevoPrecio.compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalArgumentException("El precio debe ser mayor a cero");
		}
		Producto producto = productoRepo.buscarPorId(idProducto);
		producto.setPrecioBase(nuevoPrecio);
		productoRepo.actualizar(producto);
	}

	// Marcar como descontinuado
	public void descontinuar(int idProducto) {
		Producto producto = productoRepo.buscarPorId(idProducto);
		if (producto.getEstadoProducto().getNombre().equals("Descontinuado")) {
			throw new IllegalStateException("El producto ya está descontinuado");
		}
		// Desactivar descuento activo si existe
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);
		if (descuento != null) {
			descuento.setEstadoDescuento(buscarEstadoDescuento("Inactivo"));
			productoRepo.actualizarDescuento(descuento);
		}
		producto.setEstadoProducto(buscarEstadoProducto("Descontinuado"));
		productoRepo.actualizar(producto);
	}

	// Verificar y actualizar estado agotado automaticamente
	public void verificarEstadoAgotado(int idProducto) {
		Producto producto = productoRepo.buscarPorId(idProducto);
		if (producto.getEstadoProducto().getNombre().equals("Descontinuado")) {
			return;
		}
		if (inventarioRepo.todoCombinacionesAgotadas(idProducto)) {
			producto.setEstadoProducto(buscarEstadoProducto("Agotado"));
		} else {
			producto.setEstadoProducto(buscarEstadoProducto("Disponible"));
		}
		productoRepo.actualizar(producto);
	}

	// Marcar como agotado manualmente
	public void marcarAgotado(int idProducto) {
		Producto producto = productoRepo.buscarPorId(idProducto);
		if (producto.getEstadoProducto().getNombre().equals("Descontinuado")) {
			throw new IllegalStateException("El producto está descontinuado y no puede modificarse");
		}
		if (producto.getEstadoProducto().getNombre().equals("Agotado")) {
			throw new IllegalStateException("El producto ya está marcado como agotado");
		}
		producto.setEstadoProducto(buscarEstadoProducto("Agotado"));
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

	// Registrar descuento
	public void registrarDescuento(Descuento descuento) {
		Producto producto = descuento.getProducto();
		if (producto.getEstadoProducto().getNombre().equals("Descontinuado")) {
			throw new IllegalStateException("No se puede agregar un descuento a un producto descontinuado");
		}
		Descuento existente = productoRepo.buscarDescuentoActivo(producto.getIdProducto());
		if (existente != null) {
			throw new IllegalStateException("El producto ya tiene un descuento activo");
		}
		if (descuento.getFechaFin().isBefore(descuento.getFechaInicio())) {
			throw new IllegalArgumentException("La fecha de fin no puede ser anterior a la fecha de inicio");
		}
		validarValorDescuento(descuento, producto.getPrecioBase());
		descuento.setEstadoDescuento(buscarEstadoDescuento("Activo"));
		productoRepo.guardarDescuento(descuento);
	}

	// Desactivar descuento
	public void desactivarDescuento(int idProducto) {
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);
		if (descuento == null) {
			throw new IllegalStateException("El producto no tiene un descuento activo");
		}
		descuento.setEstadoDescuento(buscarEstadoDescuento("Inactivo"));
		productoRepo.actualizarDescuento(descuento);
	}

	// Calcular precio final con descuento vigente
	public BigDecimal calcularPrecioFinal(int idProducto) {
		Producto producto = productoRepo.buscarPorId(idProducto);
		Descuento descuento = productoRepo.buscarDescuentoActivo(idProducto);

		if (descuento == null) {
			return producto.getPrecioBase();
		}

		BigDecimal precioFinal;
		if (descuento.getTipoDescuento().getNombre().equals("Porcentaje")) {
			BigDecimal factor = descuento.getValor().divide(BigDecimal.valueOf(100));
			BigDecimal monto = producto.getPrecioBase().multiply(factor);
			precioFinal = producto.getPrecioBase().subtract(monto);
		} else {
			precioFinal = producto.getPrecioBase().subtract(descuento.getValor());
		}

		if (precioFinal.compareTo(BigDecimal.ZERO) <= 0) {
			throw new IllegalStateException("El descuento no puede dejar el precio en cero o negativo");
		}
		return precioFinal;
	}

	private void validarValorDescuento(Descuento descuento, BigDecimal precioBase) {
		if (descuento.getTipoDescuento().getNombre().equals("Porcentaje")) {
			if (descuento.getValor().compareTo(BigDecimal.valueOf(100)) >= 0) {
				throw new IllegalArgumentException("El porcentaje de descuento debe ser menor a 100");
			}
		} else {
			if (descuento.getValor().compareTo(precioBase) >= 0) {
				throw new IllegalArgumentException("El valor del descuento no puede igualar ni superar el precio base");
			}
		}
	}
}