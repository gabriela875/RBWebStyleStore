package bean;

import jakarta.enterprise.context.SessionScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.*;
import service.AbastecimientoService;
import service.InventarioService;
import repository.ProveedorRepository;
import jakarta.persistence.EntityManager;
import util.JpaUtil;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;
import model.Color;
import model.Producto;
import model.Talla;

@Named
@SessionScoped
public class AbastecimientoBean implements Serializable {

	private static final long serialVersionUID = 1L;

	@Inject
	private AbastecimientoService abastecimientoService;

	@Inject
	private InventarioService inventarioService;

	@Inject
	private SessionBean sessionBean;

	private EntradaMercancia entrada = new EntradaMercancia();
	private DetalleEntrada detalleActual = new DetalleEntrada();
	private List<DetalleEntrada> detalles = new ArrayList<>();
	private List<Proveedor> listaProveedores;

	private int idProveedorSeleccionado;
	private int idInventarioDetalle;
	private String mensajeExito;
	private String mensajeError;
	private int idProductoDetalle;
	private int idTallaDetalle;
	private int idColorDetalle;
	private List<Producto> listaProductos;
	private List<Talla> listaTallas;
	private List<Color> listaColores;

	// Agregar un detalle a la lista temporal
	public String agregarDetalle() {
		mensajeExito = null;
		mensajeError = null;

		if (idProductoDetalle == 0) {
			mensajeError = "Debe seleccionar un producto";
			return null;
		}
		if (idTallaDetalle == 0) {
			mensajeError = "Debe seleccionar una talla";
			return null;
		}
		if (idColorDetalle == 0) {
			mensajeError = "Debe seleccionar un color";
			return null;
		}
		if (detalleActual.getCantidad() <= 0) {
			mensajeError = "La cantidad debe ser mayor a cero";
			return null;
		}
		if (detalleActual.getPrecioUnitario() == null || detalleActual.getPrecioUnitario().doubleValue() <= 0) {
			mensajeError = "El precio unitario debe ser mayor a cero";
			return null;
		}

		try {
			// Construir inventario temporal con producto, talla y color
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			Inventario inv = new Inventario();
			try {
				model.Producto prod = em.find(model.Producto.class, idProductoDetalle);
				model.Talla talla = em.find(model.Talla.class, idTallaDetalle);
				model.Color color = em.find(model.Color.class, idColorDetalle);
				inv.setProducto(prod);
				inv.setTalla(talla);
				inv.setColor(color);
			} finally {
				em.close();
			}

			Proveedor prov = null;
			for (Proveedor p : getListaProveedores()) {
				if (p.getIdProveedor() == idProveedorSeleccionado) {
					prov = p;
					break;
				}
			}
			detalleActual.setInventario(inv);
			detalleActual.setProveedor(prov);
			detalles.add(detalleActual);
			detalleActual = new DetalleEntrada();
			idProductoDetalle = 0;
			idTallaDetalle = 0;
			idColorDetalle = 0;
			mensajeExito = "Producto agregado a la entrada";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Quitar un detalle de la lista temporal por índice
	public String quitarDetalle(int index) {
		mensajeExito = null;
		mensajeError = null;
		if (index >= 0 && index < detalles.size()) {
			detalles.remove(index);
		}
		return null;
	}

	// Registrar la entrada completa
	public String registrarEntrada() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin() && !sessionBean.isBodeguero()) {
			mensajeError = "No tiene permisos para registrar entradas de mercancía";
			return null;
		}

		if (idProveedorSeleccionado == 0) {
			mensajeError = "Debe seleccionar un proveedor";
			return null;
		}

		if (detalles.isEmpty()) {
			mensajeError = "Debe agregar al menos un producto a la entrada";
			return null;
		}

		try {
			// Armar la entrada
			Proveedor prov = null;
			for (Proveedor p : getListaProveedores()) {
				if (p.getIdProveedor() == idProveedorSeleccionado) {
					prov = p;
					break;
				}
			}

			entrada.setProveedor(prov);
			entrada.setEmpleado(sessionBean.getEmpleadoActivo());

			abastecimientoService.registrarEntrada(entrada, detalles);

			mensajeExito = "Entrada de mercancía registrada correctamente";
			entrada = new EntradaMercancia();
			detalles = new ArrayList<>();
			idProveedorSeleccionado = 0;
		} catch (Exception e) {
			mensajeError = "Error: " + e.getClass().getSimpleName() + " - " + e.getMessage();
			if (e.getCause() != null) {
				mensajeError += " | Causa: " + e.getCause().getMessage();
			}
		}
		return null;
	}

	// Lista de proveedores cargada una sola vez
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

	public List<Producto> getListaProductos() {
		if (listaProductos == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaProductos = em.createQuery("SELECT p FROM Producto p WHERE p.estadoProducto.nombre != :estado",
						Producto.class).setParameter("estado", "Descontinuado").getResultList();
			} finally {
				em.close();
			}
		}
		return listaProductos;
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
	public EntradaMercancia getEntrada() {
		return entrada;
	}

	public void setEntrada(EntradaMercancia entrada) {
		this.entrada = entrada;
	}

	public DetalleEntrada getDetalleActual() {
		return detalleActual;
	}

	public void setDetalleActual(DetalleEntrada detalle) {
		this.detalleActual = detalle;
	}

	public List<DetalleEntrada> getDetalles() {
		return detalles;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}

	public int getIdProveedorSeleccionado() {
		return idProveedorSeleccionado;
	}

	public void setIdProveedorSeleccionado(int id) {
		this.idProveedorSeleccionado = id;
	}

	public int getIdInventarioDetalle() {
		return idInventarioDetalle;
	}

	public void setIdInventarioDetalle(int id) {
		this.idInventarioDetalle = id;
	}

	public int getIdProductoDetalle() {
		return idProductoDetalle;
	}

	public void setIdProductoDetalle(int id) {
		this.idProductoDetalle = id;
	}

	public int getIdTallaDetalle() {
		return idTallaDetalle;
	}

	public void setIdTallaDetalle(int id) {
		this.idTallaDetalle = id;
	}

	public int getIdColorDetalle() {
		return idColorDetalle;
	}

	public void setIdColorDetalle(int id) {
		this.idColorDetalle = id;
	}
}
