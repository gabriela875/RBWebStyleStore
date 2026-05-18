package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.Proveedor;
import service.ProveedorService;
import java.io.Serializable;
import java.util.List;

@Named
@ViewScoped
public class ProveedorBean implements Serializable {

	private static final long serialVersionUID = 1L;

	@Inject
	private ProveedorService proveedorService;

	@Inject
	private SessionBean sessionBean;

	private Proveedor proveedor = new Proveedor();
	private List<Proveedor> listaProveedores;
	private String mensajeExito;
	private String mensajeError;

	// Registrar nuevo proveedor
	public String registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "No tiene permisos para registrar proveedores";
			return null;
		}
		if (proveedor.getIdProveedor() == 0) {
			// es nuevo
			proveedorService.registrar(proveedor);
			mensajeExito = "Proveedor registrado correctamente";
		} else {
			// es edición
			proveedorService.editar(proveedor);
			mensajeExito = "Proveedor actualizado correctamente";
		}
		proveedor = new Proveedor();
		listaProveedores = null;
		return null;

	}

	// Cargar proveedor en el formulario para editar
	public String cargarProveedor(int idProveedor) {
		mensajeExito = null;
		mensajeError = null;
		proveedor = proveedorService.buscarPorId(idProveedor);
		return null;
	}

	// Limpiar formulario
	public String nuevo() {
		mensajeExito = null;
		mensajeError = null;
		proveedor = new Proveedor();
		return null;
	}

	// Lista cargada una sola vez por vista
	public List<Proveedor> getListaProveedores() {
		if (listaProveedores == null) {
			listaProveedores = proveedorService.listarTodos();
		}
		return listaProveedores;
	}

	public Proveedor getProveedor() {
		return proveedor;
	}

	public void setProveedor(Proveedor proveedor) {
		this.proveedor = proveedor;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}
}
