package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import jakarta.persistence.EntityManager;
import model.Cliente;
import model.TipoDocumento;
import service.ClienteService;
import util.JpaUtil;
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
	private List<TipoDocumento> listaTiposDocumento;
	private int idTipoDocSeleccionado;
	private String mensajeExito;
	private String mensajeError;

	public void registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin() && !sessionBean.isVendedor()) {
			mensajeError = "No tiene permisos para registrar clientes";
			return;
		}

		// Buscar tipo de documento seleccionado
		TipoDocumento tipoDoc = null;
		for (TipoDocumento td : getListaTiposDocumento()) {
			if (td.getIdTipoDoc() == idTipoDocSeleccionado) {
				tipoDoc = td;
				break;
			}
		}
		if (tipoDoc == null) {
			mensajeError = "Debe seleccionar el tipo de documento";
			return;
		}
		cliente.setTipoDocumento(tipoDoc);

		try {
			clienteService.registrar(cliente);
			mensajeExito = "Cliente registrado correctamente";
			cliente = new Cliente();
			idTipoDocSeleccionado = 0;
			listaClientes = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	public void editar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede editar clientes";
			return;
		}

		TipoDocumento tipoDoc = null;
		for (TipoDocumento td : getListaTiposDocumento()) {
			if (td.getIdTipoDoc() == idTipoDocSeleccionado) {
				tipoDoc = td;
				break;
			}
		}
		if (tipoDoc != null) {
			cliente.setTipoDocumento(tipoDoc);
		}

		try {
			clienteService.editar(cliente);
			mensajeExito = "Cliente actualizado correctamente";
			listaClientes = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	public void buscarPorDocumento(String numDocumento) {
		mensajeError = null;
		Cliente encontrado = clienteService.buscarPorDocumento(numDocumento);
		if (encontrado != null) {
			cliente = encontrado;
			idTipoDocSeleccionado = cliente.getTipoDocumento().getIdTipoDoc();
		} else {
			mensajeError = "No se encontró un cliente con ese documento";
		}
	}

	public void cargarCliente(int idCliente) {
		mensajeExito = null;
		mensajeError = null;
		cliente = clienteService.buscarPorId(idCliente);
		idTipoDocSeleccionado = cliente.getTipoDocumento().getIdTipoDoc();
	}

	public void nuevo() {
		mensajeExito = null;
		mensajeError = null;
		cliente = new Cliente();
		idTipoDocSeleccionado = 0;
	}

	public List<Cliente> getListaClientes() {
		if (listaClientes == null)
			listaClientes = clienteService.listarTodos();
		return listaClientes;
	}

	public List<TipoDocumento> getListaTiposDocumento() {
		if (listaTiposDocumento == null) {
			EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
			try {
				listaTiposDocumento = em.createQuery("SELECT t FROM TipoDocumento t", TipoDocumento.class)
						.getResultList();
			} finally {
				em.close();
			}
		}
		return listaTiposDocumento;
	}

	public Cliente getCliente() {
		return cliente;
	}

	public void setCliente(Cliente cliente) {
		this.cliente = cliente;
	}

	public int getIdTipoDocSeleccionado() {
		return idTipoDocSeleccionado;
	}

	public void setIdTipoDocSeleccionado(int id) {
		this.idTipoDocSeleccionado = id;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}
}
