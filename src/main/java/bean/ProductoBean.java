package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.*;
import service.ProductoService;
import service.InventarioService;
import jakarta.persistence.EntityManager;
import util.JpaUtil;
import java.io.Serializable;
import java.math.BigDecimal;
import java.util.List;

@Named
@ViewScoped
public class ProductoBean implements Serializable {

	private static final long serialVersionUID = 1L;

	@Inject
	private ProductoService productoService;

	@Inject
	private InventarioService inventarioService;

	@Inject
	private SessionBean sessionBean;

	private Producto producto = new Producto();
	private Descuento descuento = new Descuento();
	private Inventario inventario = new Inventario();
	private List<Producto> listaProductos;
	private List<Inventario> listaInventario;
	private List<Categoria> listaCategorias;
	private List<Proveedor> listaProveedores;
	private String mensajeExito;
	private String mensajeError;

	// Campos para selectOneMenu con objetos complejos
	private int idCategoriaSeleccionada;
	private int idProveedorSeleccionado;
	private int idProductoDescuento;

	// Campo para actualizar precio desde la tabla
	private BigDecimal nuevoPrecio;

	// Registrar nuevo producto
	public String registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede registrar productos";
			return null;
		}

		// Buscar categoria seleccionada
		Categoria categoria = null;
		for (Categoria c : getListaCategorias()) {
			if (c.getIdCategoria() == idCategoriaSeleccionada) {
				categoria = c;
				break;
			}
		}

		// Buscar proveedor seleccionado
		Proveedor proveedor = null;
		for (Proveedor p : getListaProveedores()) {
			if (p.getIdProveedor() == idProveedorSeleccionado) {
				proveedor = p;
				break;
			}
		}

		if (categoria == null) {
			mensajeError = "Debe seleccionar una categoría";
			return null;
		}
		if (proveedor == null) {
			mensajeError = "Debe seleccionar un proveedor";
			return null;
		}

		producto.setCategoria(categoria);
		producto.setProveedor(proveedor);

		try {
			productoService.registrar(producto);
			mensajeExito = "Producto registrado correctamente";
			producto = new Producto();
			idCategoriaSeleccionada = 0;
			idProveedorSeleccionado = 0;
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Actualizar precio — lee el precio del campo nuevoPrecio del bean
	public String actualizarPrecio(int idProducto) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede modificar precios";
			return null;
		}

		if (nuevoPrecio == null || nuevoPrecio.compareTo(BigDecimal.ZERO) <= 0) {
			mensajeError = "El precio debe ser mayor a cero";
			return null;
		}

		try {
			productoService.actualizarPrecio(idProducto, nuevoPrecio);
			mensajeExito = "Precio actualizado correctamente";
			nuevoPrecio = null;
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Descontinuar producto
	public String descontinuar(int idProducto) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin() && !sessionBean.isBodeguero()) {
			mensajeError = "No tiene permisos para descontinuar productos";
			return null;
		}

		try {
			productoService.descontinuar(idProducto);
			mensajeExito = "Producto marcado como descontinuado";
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Registrar descuento — el idProducto viene del selector idProductoDescuento
	public String registrarDescuento(int idProducto) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede registrar descuentos";
			return null;
		}

		if (idProducto == 0) {
			mensajeError = "Debe seleccionar un producto";
			return null;
		}

		try {
			Producto p = productoService.buscarPorId(idProducto);
			descuento.setProducto(p);
			productoService.registrarDescuento(descuento);
			mensajeExito = "Descuento registrado correctamente";
			descuento = new Descuento();
			idProductoDescuento = 0;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Desactivar descuento
	public String desactivarDescuento(int idProducto) {
		mensajeExito = null;
		mensajeError = null;

		try {
			productoService.desactivarDescuento(idProducto);
			mensajeExito = "Descuento desactivado correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Limpiar formulario
	public String nuevo() {
		mensajeExito = null;
		mensajeError = null;
		producto = new Producto();
		idCategoriaSeleccionada = 0;
		idProveedorSeleccionado = 0;
		return null;
	}

	// Cargar producto para ver detalle
	public String cargarProducto(int idProducto) {
		producto = productoService.buscarPorId(idProducto);
		listaInventario = inventarioService.listarPorProducto(idProducto);
		return null;
	}

	// Listas
	public List<Producto> getListaProductos() {
		if (listaProductos == null) {
			listaProductos = productoService.listarTodos();
		}
		return listaProductos;
	}

	public List<Producto> getListaDisponibles() {
		return productoService.listarDisponibles();
	}

	public List<Categoria> getListaCategorias() {
		if (listaCategorias == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaCategorias = em.createQuery("SELECT c FROM Categoria c", Categoria.class).getResultList();
			} finally {
				em.close();
			}
		}
		return listaCategorias;
	}

	public List<Proveedor> getListaProveedores() {
		if (listaProveedores == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaProveedores = em.createQuery("SELECT p FROM Proveedor p", Proveedor.class).getResultList();
			} finally {
				em.close();
			}
		}
		return listaProveedores;
	}

	public List<Talla> getListaTallas() {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT t FROM Talla t", Talla.class).getResultList();
		} finally {
			em.close();
		}
	}

	public List<Color> getListaColores() {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT c FROM Color c", Color.class).getResultList();
		} finally {
			em.close();
		}
	}

	public Producto.Genero[] getGeneros() {
		return Producto.Genero.values();
	}

	public Producto.EstadoProducto[] getEstados() {
		return Producto.EstadoProducto.values();
	}

	public Descuento.TipoDescuento[] getTiposDescuento() {
		return Descuento.TipoDescuento.values();
	}

	// Getters y setters
	public Producto getProducto() {
		return producto;
	}

	public void setProducto(Producto producto) {
		this.producto = producto;
	}

	public Descuento getDescuento() {
		return descuento;
	}

	public void setDescuento(Descuento descuento) {
		this.descuento = descuento;
	}

	public Inventario getInventario() {
		return inventario;
	}

	public void setInventario(Inventario inventario) {
		this.inventario = inventario;
	}

	public List<Inventario> getListaInventario() {
		return listaInventario;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}

	public int getIdCategoriaSeleccionada() {
		return idCategoriaSeleccionada;
	}

	public void setIdCategoriaSeleccionada(int id) {
		this.idCategoriaSeleccionada = id;
	}

	public int getIdProveedorSeleccionado() {
		return idProveedorSeleccionado;
	}

	public void setIdProveedorSeleccionado(int id) {
		this.idProveedorSeleccionado = id;
	}

	public int getIdProductoDescuento() {
		return idProductoDescuento;
	}

	public void setIdProductoDescuento(int id) {
		this.idProductoDescuento = id;
	}

	public BigDecimal getNuevoPrecio() {
		return nuevoPrecio;
	}

	public void setNuevoPrecio(BigDecimal nuevoPrecio) {
		this.nuevoPrecio = nuevoPrecio;
	}
}