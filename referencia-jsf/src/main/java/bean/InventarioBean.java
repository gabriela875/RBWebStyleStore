package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.AjusteInventario;
import model.Inventario;
import model.Producto;
import service.InventarioService;
import service.ProductoService;
import java.io.Serializable;
import java.util.List;

@Named
@ViewScoped
public class InventarioBean implements Serializable {

	private static final long serialVersionUID = 1L;

	@Inject
	private InventarioService inventarioService;

	@Inject
	private ProductoService productoService;

	@Inject
	private SessionBean sessionBean;

	private AjusteInventario ajuste = new AjusteInventario();
	private List<Inventario> listaInventario;
	private List<Producto> listaProductos;
	private String mensajeExito;
	private String mensajeError;

	// ID del producto seleccionado en el filtro de búsqueda
	private int idProductoFiltro;

	// ID del inventario para el ajuste manual
	private int idInventarioAjuste;

	// Buscar combinaciones de inventario por producto
	public String buscarPorProducto() {
		mensajeExito = null;
		mensajeError = null;

		if (idProductoFiltro == 0) {
			mensajeError = "Debe seleccionar un producto";
			return null;
		}

		try {
			listaInventario = inventarioService.listarPorProducto(idProductoFiltro);
			if (listaInventario.isEmpty()) {
				mensajeError = "El producto no tiene combinaciones registradas en inventario";
			}
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Registrar ajuste manual de stock
	public String registrarAjuste() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede registrar ajustes de inventario";
			return null;
		}

		if (idInventarioAjuste == 0) {
			mensajeError = "Debe ingresar el ID del inventario a ajustar";
			return null;
		}

		try {
			// Armar el inventario y el empleado dentro del ajuste
			Inventario inv = new Inventario();
			inv.setIdInventario(idInventarioAjuste);
			ajuste.setInventario(inv);
			ajuste.setEmpleado(sessionBean.getEmpleadoActivo());

			inventarioService.registrarAjuste(ajuste);
			mensajeExito = "Ajuste registrado correctamente";
			ajuste = new AjusteInventario();
			idInventarioAjuste = 0;
			if (idProductoFiltro != 0) {
				listaInventario = inventarioService.listarPorProducto(idProductoFiltro);
			}
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Lista de productos para el selector de búsqueda
	public List<Producto> getListaProductos() {
		if (listaProductos == null) {
			listaProductos = productoService.listarTodos();
		}
		return listaProductos;
	}

	// Getters y setters
	public AjusteInventario getAjuste() {
		return ajuste;
	}

	public void setAjuste(AjusteInventario ajuste) {
		this.ajuste = ajuste;
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

	public int getIdProductoFiltro() {
		return idProductoFiltro;
	}

	public void setIdProductoFiltro(int idProductoFiltro) {
		this.idProductoFiltro = idProductoFiltro;
	}

	public int getIdInventarioAjuste() {
		return idInventarioAjuste;
	}

	public void setIdInventarioAjuste(int idInventarioAjuste) {
		this.idInventarioAjuste = idInventarioAjuste;
	}
}
