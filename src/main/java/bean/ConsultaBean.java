package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.*;
import service.ConsultaService;
import java.io.Serializable;
import java.util.ArrayList;
import java.util.List;

@Named
@ViewScoped
public class ConsultaBean implements Serializable {

	private static final long serialVersionUID = 1L;

	@Inject
	private ConsultaService consultaService;

	@Inject
	private SessionBean sessionBean;

	// Selector de consulta y filtro unificado
	private String consultaSeleccionada;
	private int idFiltro;

	// Resultados
	private List<Venta> listaVentas;
	private List<DetalleVenta> listaDetalles;
	private List<Pago> listaPagos;
	private List<AnulacionVenta> listaAnulaciones;
	private List<Empleado> listaEmpleados;
	private List<Cliente> listaClientes;
	private List<Producto> listaProductos;
	private List<Inventario> listaInventario;
	private List<EntradaMercancia> listaEntradas;
	private List<DetalleEntrada> listaDetallesEntrada;

	private String mensajeError;

	// Método único que despacha según la consulta seleccionada
	public String ejecutarConsulta() {
		mensajeError = null;

		// Limpiar todos los resultados anteriores
		listaVentas = null;
		listaDetalles = null;
		listaPagos = null;
		listaAnulaciones = null;
		listaEmpleados = null;
		listaClientes = null;
		listaProductos = null;
		listaInventario = null;
		listaEntradas = null;
		listaDetallesEntrada = null;

		if (consultaSeleccionada == null || consultaSeleccionada.isEmpty()) {
			mensajeError = "Debe seleccionar una consulta";
			return null;
		}

		try {
			switch (consultaSeleccionada) {

			case "todas-ventas":
				if (!sessionBean.isAdmin()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				listaVentas = consultaService.consultarTodasLasVentas();
				break;

			case "ventas-vencidas":
				if (!sessionBean.isAdmin()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				listaVentas = consultaService.consultarVentasVencidas();
				break;

			case "anulaciones":
				if (!sessionBean.isAdmin()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				listaAnulaciones = consultaService.consultarAnulaciones();
				break;

			case "empleados":
				if (!sessionBean.isAdmin()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				listaEmpleados = consultaService.consultarTodosLosEmpleados();
				break;

			case "mis-ventas":
				listaVentas = consultaService
						.consultarVentasPorEmpleado(sessionBean.getEmpleadoActivo().getIdEmpleado());
				break;

			case "ventas-cliente":
				if (idFiltro == 0) {
					mensajeError = "Debe ingresar el ID del cliente";
					break;
				}
				listaVentas = consultaService.consultarVentasPorCliente(idFiltro);
				break;

			case "detalle-venta":
				if (idFiltro == 0) {
					mensajeError = "Debe ingresar el ID de la venta";
					break;
				}
				listaDetalles = consultaService.consultarDetalleVenta(idFiltro);
				break;

			case "pagos-venta":
				if (idFiltro == 0) {
					mensajeError = "Debe ingresar el ID de la venta";
					break;
				}
				listaPagos = consultaService.consultarPagosPorVenta(idFiltro);
				break;

			case "clientes":
				listaClientes = consultaService.consultarTodosLosClientes();
				break;

			case "productos":
				listaProductos = consultaService.consultarTodosLosProductos();
				break;

			case "inventario":
				if (idFiltro == 0) {
					mensajeError = "Debe ingresar el ID del producto";
					break;
				}
				listaInventario = consultaService.consultarInventarioPorProducto(idFiltro);
				break;

			case "entradas":
				if (!sessionBean.isAdmin() && !sessionBean.isBodeguero()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				listaEntradas = consultaService.consultarTodasLasEntradas();
				break;

			case "detalle-entrada":
				if (!sessionBean.isAdmin() && !sessionBean.isBodeguero()) {
					mensajeError = "No tiene permisos para esta consulta";
					break;
				}
				if (idFiltro == 0) {
					mensajeError = "Debe ingresar el ID de la entrada";
					break;
				}
				listaDetallesEntrada = consultaService.consultarDetalleEntrada(idFiltro);
				break;

			default:
				mensajeError = "Consulta no reconocida";
				break;
			}
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}

		return null;
	}

	public static class OpcionConsulta {
		private String label;
		private String value;

		public OpcionConsulta(String label, String value) {
			this.label = label;
			this.value = value;
		}

		public String getLabel() {
			return label;
		}

		public String getValue() {
			return value;
		}
	}

	// Lista de opciones según el cargo del empleado activo
	public List<OpcionConsulta> getOpcionesConsulta() {
		List<OpcionConsulta> opciones = new ArrayList<>();
		opciones.add(new OpcionConsulta("Seleccionar consulta...", ""));

		if (sessionBean.isAdmin()) {
			opciones.add(new OpcionConsulta("Todas las ventas", "todas-ventas"));
			opciones.add(new OpcionConsulta("Ventas vencidas (+24h)", "ventas-vencidas"));
			opciones.add(new OpcionConsulta("Anulaciones", "anulaciones"));
			opciones.add(new OpcionConsulta("Todos los empleados", "empleados"));
		}

		if (sessionBean.isAdmin() || sessionBean.isVendedor()) {
			opciones.add(new OpcionConsulta("Mis ventas", "mis-ventas"));
			opciones.add(new OpcionConsulta("Ventas por cliente (ID)", "ventas-cliente"));
			opciones.add(new OpcionConsulta("Detalle de venta (ID)", "detalle-venta"));
			opciones.add(new OpcionConsulta("Pagos de una venta (ID)", "pagos-venta"));
			opciones.add(new OpcionConsulta("Todos los clientes", "clientes"));
		}

		opciones.add(new OpcionConsulta("Todos los productos", "productos"));
		opciones.add(new OpcionConsulta("Inventario por producto (ID)", "inventario"));

		if (sessionBean.isAdmin() || sessionBean.isBodeguero()) {
			opciones.add(new OpcionConsulta("Entradas de mercancía", "entradas"));
			opciones.add(new OpcionConsulta("Detalle de entrada (ID)", "detalle-entrada"));
		}

		return opciones;
	}

	// Getters de resultados
	public List<Venta> getListaVentas() {
		return listaVentas;
	}

	public List<DetalleVenta> getListaDetalles() {
		return listaDetalles;
	}

	public List<Pago> getListaPagos() {
		return listaPagos;
	}

	public List<AnulacionVenta> getListaAnulaciones() {
		return listaAnulaciones;
	}

	public List<Empleado> getListaEmpleados() {
		return listaEmpleados;
	}

	public List<Cliente> getListaClientes() {
		return listaClientes;
	}

	public List<Producto> getListaProductos() {
		return listaProductos;
	}

	public List<Inventario> getListaInventario() {
		return listaInventario;
	}

	public List<EntradaMercancia> getListaEntradas() {
		return listaEntradas;
	}

	public List<DetalleEntrada> getListaDetallesEntrada() {
		return listaDetallesEntrada;
	}

	public String getMensajeError() {
		return mensajeError;
	}

	public String getConsultaSeleccionada() {
		return consultaSeleccionada;
	}

	public void setConsultaSeleccionada(String consultaSeleccionada) {
		this.consultaSeleccionada = consultaSeleccionada;
	}

	public int getIdFiltro() {
		return idFiltro;
	}

	public void setIdFiltro(int idFiltro) {
		this.idFiltro = idFiltro;
	}
}