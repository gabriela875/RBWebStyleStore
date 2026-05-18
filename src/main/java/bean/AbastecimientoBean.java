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

	// Agregar un detalle a la lista temporal
	public String agregarDetalle() {
		mensajeExito = null;
		mensajeError = null;

		if (idInventarioDetalle == 0) {
			mensajeError = "Debe ingresar el ID del inventario";
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
			// Buscar el inventario y asignarlo al detalle
			Inventario inv = inventarioService.buscarPorId(idInventarioDetalle);
			detalleActual.setInventario(inv);

			// Asignar el proveedor seleccionado al detalle
			Proveedor prov = null;
			for (Proveedor p : getListaProveedores()) {
				if (p.getIdProveedor() == idProveedorSeleccionado) {
					prov = p;
					break;
				}
			}
			detalleActual.setProveedor(prov);

			detalles.add(detalleActual);
			detalleActual = new DetalleEntrada();
			idInventarioDetalle = 0;
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
			mensajeError = e.getMessage();
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
}
