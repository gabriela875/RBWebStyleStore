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
	private List<Genero> listaGeneros;
	private List<TipoDescuento> listaTiposDescuento;

	private String mensajeExito;
	private String mensajeError;

	private int idCategoriaSeleccionada;
	private int idProveedorSeleccionado;
	private int idGeneroSeleccionado;
	private int idTipoDescuentoSeleccionado;
	private int idProductoDescuento;
	private int idProductoActualizar;
	private String fechaInicioDescuento;
	private String fechaFinDescuento;
	private BigDecimal nuevoPrecio;

	// Registrar nuevo producto
	public String registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede registrar productos";
			return null;
		}

		Categoria categoria = null;
		for (Categoria c : getListaCategorias()) {
			if (c.getIdCategoria() == idCategoriaSeleccionada) {
				categoria = c;
				break;
			}
		}
		Proveedor proveedor = null;
		for (Proveedor p : getListaProveedores()) {
			if (p.getIdProveedor() == idProveedorSeleccionado) {
				proveedor = p;
				break;
			}
		}
		Genero genero = null;
		for (Genero g : getListaGeneros()) {
			if (g.getIdGenero() == idGeneroSeleccionado) {
				genero = g;
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
		if (genero == null) {
			mensajeError = "Debe seleccionar un género";
			return null;
		}

		producto.setCategoria(categoria);
		producto.setProveedor(proveedor);
		producto.setGenero(genero);

		try {
			productoService.registrar(producto);
			mensajeExito = "Producto registrado correctamente";
			producto = new Producto();
			idCategoriaSeleccionada = 0;
			idProveedorSeleccionado = 0;
			idGeneroSeleccionado = 0;
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	public String eliminar(int idProducto) {
		mensajeExito = null;
		mensajeError = null;
		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede eliminar productos";
			return null;
		}
		try {
			productoService.eliminar(idProducto);
			mensajeExito = "Producto eliminado correctamente";
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

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

	public String marcarAgotado(int idProducto) {
		mensajeExito = null;
		mensajeError = null;
		if (!sessionBean.isAdmin() && !sessionBean.isBodeguero()) {
			mensajeError = "No tiene permisos para marcar productos como agotados";
			return null;
		}
		try {
			productoService.marcarAgotado(idProducto);
			mensajeExito = "Producto marcado como agotado";
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	public String editar() {
		mensajeExito = null;
		mensajeError = null;
		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede editar productos";
			return null;
		}

		Categoria categoria = null;
		for (Categoria c : getListaCategorias()) {
			if (c.getIdCategoria() == idCategoriaSeleccionada) {
				categoria = c;
				break;
			}
		}
		Proveedor proveedor = null;
		for (Proveedor p : getListaProveedores()) {
			if (p.getIdProveedor() == idProveedorSeleccionado) {
				proveedor = p;
				break;
			}
		}
		Genero genero = null;
		for (Genero g : getListaGeneros()) {
			if (g.getIdGenero() == idGeneroSeleccionado) {
				genero = g;
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
		if (genero == null) {
			mensajeError = "Debe seleccionar un género";
			return null;
		}

		producto.setCategoria(categoria);
		producto.setProveedor(proveedor);
		producto.setGenero(genero);

		try {
			productoService.editar(producto);
			mensajeExito = "Producto actualizado correctamente";
			listaProductos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

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

		// Buscar tipo de descuento seleccionado
		TipoDescuento tipoDescuento = null;
		for (TipoDescuento td : getListaTiposDescuento()) {
			if (td.getIdTipoDesc() == idTipoDescuentoSeleccionado) {
				tipoDescuento = td;
				break;
			}
		}
		if (tipoDescuento == null) {
			mensajeError = "Debe seleccionar el tipo de descuento";
			return null;
		}

		try {
			descuento.setFechaInicio(java.time.LocalDate.parse(fechaInicioDescuento));
			descuento.setFechaFin(java.time.LocalDate.parse(fechaFinDescuento));
		} catch (Exception ex) {
			mensajeError = "El formato de fecha debe ser AAAA-MM-DD (ejemplo: 2026-05-17)";
			return null;
		}

		try {
			Producto p = productoService.buscarPorId(idProducto);
			descuento.setProducto(p);
			descuento.setTipoDescuento(tipoDescuento);
			productoService.registrarDescuento(descuento);
			mensajeExito = "Descuento registrado correctamente";
			descuento = new Descuento();
			idProductoDescuento = 0;
			idTipoDescuentoSeleccionado = 0;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

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

	public String nuevo() {
		mensajeExito = null;
		mensajeError = null;
		producto = new Producto();
		idCategoriaSeleccionada = 0;
		idProveedorSeleccionado = 0;
		idGeneroSeleccionado = 0;
		return null;
	}

	public String cargarProducto(int idProducto) {
		producto = productoService.buscarPorId(idProducto);
		idCategoriaSeleccionada = producto.getCategoria().getIdCategoria();
		idProveedorSeleccionado = producto.getProveedor().getIdProveedor();
		idGeneroSeleccionado = producto.getGenero().getIdGenero();
		listaInventario = inventarioService.listarPorProducto(idProducto);
		return null;
	}

	// Listas
	public List<Producto> getListaProductos() {
		if (listaProductos == null)
			listaProductos = productoService.listarTodos();
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

	public List<Genero> getListaGeneros() {
		if (listaGeneros == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaGeneros = em.createQuery("SELECT g FROM Genero g", Genero.class).getResultList();
			} finally {
				em.close();
			}
		}
		return listaGeneros;
	}

	public List<TipoDescuento> getListaTiposDescuento() {
		if (listaTiposDescuento == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaTiposDescuento = em.createQuery("SELECT t FROM TipoDescuento t", TipoDescuento.class)
						.getResultList();
			} finally {
				em.close();
			}
		}
		return listaTiposDescuento;
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

	// Getters y setters
	public Producto getProducto() {
		return producto;
	}

	public void setProducto(Producto p) {
		this.producto = p;
	}

	public Descuento getDescuento() {
		return descuento;
	}

	public void setDescuento(Descuento d) {
		this.descuento = d;
	}

	public Inventario getInventario() {
		return inventario;
	}

	public void setInventario(Inventario i) {
		this.inventario = i;
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

	public int getIdGeneroSeleccionado() {
		return idGeneroSeleccionado;
	}

	public void setIdGeneroSeleccionado(int id) {
		this.idGeneroSeleccionado = id;
	}

	public int getIdTipoDescuentoSeleccionado() {
		return idTipoDescuentoSeleccionado;
	}

	public void setIdTipoDescuentoSeleccionado(int id) {
		this.idTipoDescuentoSeleccionado = id;
	}

	public int getIdProductoDescuento() {
		return idProductoDescuento;
	}

	public void setIdProductoDescuento(int id) {
		this.idProductoDescuento = id;
	}

	public int getIdProductoActualizar() {
		return idProductoActualizar;
	}

	public void setIdProductoActualizar(int id) {
		this.idProductoActualizar = id;
	}

	public BigDecimal getNuevoPrecio() {
		return nuevoPrecio;
	}

	public void setNuevoPrecio(BigDecimal p) {
		this.nuevoPrecio = p;
	}

	public String getFechaInicioDescuento() {
		return fechaInicioDescuento;
	}

	public void setFechaInicioDescuento(String f) {
		this.fechaInicioDescuento = f;
	}

	public String getFechaFinDescuento() {
		return fechaFinDescuento;
	}

	public void setFechaFinDescuento(String f) {
		this.fechaFinDescuento = f;
	}
}