package bean;

import jakarta.enterprise.context.SessionScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.*;
import service.ClienteService;
import service.InventarioService;
import service.VentaService;
import java.io.Serializable;
import java.util.Collections;
import java.util.List;

@Named
@SessionScoped
public class VentaBean implements Serializable {

	@Inject
	private VentaService ventaService;

	@Inject
	private ClienteService clienteService;

	@Inject
	private InventarioService inventarioService;

	@Inject
	private SessionBean sessionBean;

	private Venta ventaActiva;
	private Cliente cliente = new Cliente();
	private Pago pago = new Pago();
	private List<DetalleVenta> detallesActivos;
	private String documentoBusqueda;
	private String mensajeExito;
	private String mensajeError;
	private String motivoAnulacion; // campo nuevo
	private int idInventarioSeleccionado;
	private int cantidadAgregar;

	// Iniciar una nueva venta
	public void iniciarVenta() {
		mensajeExito = null;
		mensajeError = null;

		if (cliente == null || cliente.getIdCliente() == 0) {
			mensajeError = "Debe seleccionar un cliente válido";
			return;
		}

		try {
			ventaActiva = ventaService.crearVenta(sessionBean.getEmpleadoActivo(), cliente);
			detallesActivos = null;
			mensajeExito = "Venta iniciada correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Agregar producto a la venta activa
	public void agregarProducto() {
		mensajeExito = null;
		mensajeError = null;
		if (ventaActiva == null) {
			mensajeError = "Debe iniciar una venta primero";
			return;
		}
		if (idInventarioSeleccionado == 0) {
			mensajeError = "Debe ingresar el ID del inventario";
			return;
		}

		if (cantidadAgregar <= 0) {
			mensajeError = "La cantidad debe ser mayor a cero";
			return;
		}

		try {
			Inventario inv = inventarioService.buscarPorId(idInventarioSeleccionado);
			ventaService.agregarDetalle(ventaActiva.getIdVenta(), inv, cantidadAgregar);
			detallesActivos = null;
			idInventarioSeleccionado = 0;
			cantidadAgregar = 0;
			mensajeExito = "Producto agregado correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Quitar producto de la venta activa
	public void quitarProducto(int idDetalle) {
		mensajeExito = null;
		mensajeError = null;
		try {
			ventaService.quitarDetalle(ventaActiva.getIdVenta(), idDetalle);
			detallesActivos = null;
			mensajeExito = "Producto quitado correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Registrar un pago para la venta activa
	public void registrarPago() {
		mensajeExito = null;
		mensajeError = null;
		try {
			ventaService.registrarPago(ventaActiva.getIdVenta(), pago);
			pago = new Pago();
			mensajeExito = "Pago registrado correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Completar la venta activa
	public void completarVenta() {
		mensajeExito = null;
		mensajeError = null;
		try {
			ventaService.completarVenta(ventaActiva.getIdVenta());
			mensajeExito = "Venta completada correctamente";
			ventaActiva = null;
			detallesActivos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Anular la venta activa (vendedor)
	public void anularVenta(String motivo) {
		mensajeExito = null;
		mensajeError = null;
		try {
			ventaService.anularVenta(ventaActiva.getIdVenta(), motivo, sessionBean.getEmpleadoActivo());
			mensajeExito = "Venta anulada correctamente";
			ventaActiva = null;
			detallesActivos = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Anular venta desde la vista admin
	public String anularVenta(int idVenta) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede anular ventas desde esta vista";
			return null;
		}

		if (motivoAnulacion == null || motivoAnulacion.trim().isEmpty()) {
			mensajeError = "El motivo de anulación es obligatorio";
			return null;
		}

		try {
			ventaService.anularVenta(idVenta, motivoAnulacion.trim(), sessionBean.getEmpleadoActivo());
			mensajeExito = "Venta anulada correctamente";
			motivoAnulacion = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Listar todas las ventas
	public List<Venta> listarTodas() {
		return ventaService.listarTodas();
	}

	// Listar anulaciones — solo admin
	public List<AnulacionVenta> listarAnulaciones() {
		if (!sessionBean.isAdmin()) {
			return Collections.emptyList();
		}
		return ventaService.listarAnulaciones();
	}

	// Buscar cliente por documento
	public void buscarCliente() {
		mensajeError = null;
		Cliente encontrado = clienteService.buscarPorDocumento(documentoBusqueda);
		if (encontrado != null) {
			cliente = encontrado;
		} else {
			mensajeError = "No se encontró cliente con ese documento";
		}
	}

	// Detalles de la venta activa
	public List<DetalleVenta> getDetallesActivos() {
		if (ventaActiva != null && detallesActivos == null) {
			detallesActivos = ventaService.listarDetalles(ventaActiva.getIdVenta());
		}
		return detallesActivos;
	}

	// Pagos de la venta activa
	public List<Pago> getPagosActivos() {
		if (ventaActiva == null)
			return null;
		return ventaService.listarPagos(ventaActiva.getIdVenta());
	}

	public Pago.MetodoPago[] getMetodosPago() {
		return Pago.MetodoPago.values();
	}

	// Getters y setters
	public Venta getVentaActiva() {
		return ventaActiva;
	}

	public Cliente getCliente() {
		return cliente;
	}

	public void setCliente(Cliente cliente) {
		this.cliente = cliente;
	}

	public Pago getPago() {
		return pago;
	}

	public void setPago(Pago pago) {
		this.pago = pago;
	}

	public String getDocumentoBusqueda() {
		return documentoBusqueda;
	}

	public void setDocumentoBusqueda(String documentoBusqueda) {
		this.documentoBusqueda = documentoBusqueda;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}

	public String getMotivoAnulacion() {
		return motivoAnulacion;
	}

	public void setMotivoAnulacion(String motivoAnulacion) {
		this.motivoAnulacion = motivoAnulacion;
	}

	public int getIdInventarioSeleccionado() {
		return idInventarioSeleccionado;
	}

	public void setIdInventarioSeleccionado(int id) {
		this.idInventarioSeleccionado = id;
	}

	public int getCantidadAgregar() {
		return cantidadAgregar;
	}

	public void setCantidadAgregar(int cantidad) {
		this.cantidadAgregar = cantidad;
	}
}
