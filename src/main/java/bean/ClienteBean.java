package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.Cliente;
import service.ClienteService;
import java.io.Serializable;
import java.util.List;

@Named
@ViewScoped 
public class ClienteBean implements Serializable { 

	private static final long serialVersionUID = 1L;

	@Inject
	private ClienteService clienteService;

	@Inject
	private SessionBean sessionBean;

	private Cliente cliente = new Cliente();
	private List<Cliente> listaClientes;
	private String mensajeExito;
	private String mensajeError;

	// Registrar un cliente dentro del flujo de una venta
	public void registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin() && !sessionBean.isVendedor()) {
			mensajeError = "No tiene permisos para registrar clientes";
			return;
		}

		try {
			clienteService.registrar(cliente);
			mensajeExito = "Cliente registrado correctamente";
			cliente = new Cliente();
			listaClientes = null;
		} catch (Exception e) {
			// El ClienteService lanza excepción con mensaje claro si el documento ya existe
			mensajeError = e.getMessage();
		}
	}

	// Editar cliente — solo Admin
	public void editar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede editar clientes";
			return;
		}

		try {
			clienteService.editar(cliente);
			mensajeExito = "Cliente actualizado correctamente";
			listaClientes = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Buscar cliente por documento para vincularlo a una venta
	public void buscarPorDocumento(String numDocumento) {
		mensajeError = null;
		Cliente encontrado = clienteService.buscarPorDocumento(numDocumento);
		if (encontrado != null) {
			cliente = encontrado;
		} else {
			mensajeError = "No se encontró un cliente con ese documento";
		}
	}

	// Cargar un cliente para editar
	public void cargarCliente(int idCliente) {
		mensajeExito = null;
		mensajeError = null;
		cliente = clienteService.buscarPorId(idCliente);
	}

	// Limpiar formulario
	public void nuevo() {
		mensajeExito = null;
		mensajeError = null;
		cliente = new Cliente();
	}

	// Listar todos los clientes
	public List<Cliente> getListaClientes() {
		if (listaClientes == null) {
			listaClientes = clienteService.listarTodos();
		}
		return listaClientes;
	}

	public Cliente.TipoDocumento[] getTiposDocumento() {
		return Cliente.TipoDocumento.values();
	}

	public Cliente getCliente() {
		return cliente;
	}

	public void setCliente(Cliente cliente) {
		this.cliente = cliente;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}
}
