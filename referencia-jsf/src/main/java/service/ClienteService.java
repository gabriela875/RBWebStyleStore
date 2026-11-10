package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.Cliente;
import repository.ClienteRepository;
import java.util.List;

@ApplicationScoped
public class ClienteService {

	@Inject
	private ClienteRepository clienteRepo;

	// Registrar cliente dentro del flujo de una venta
	public void registrar(Cliente cliente) {

		// Verificar que el documento no este registrado
		if (clienteRepo.buscarPorDocumento(cliente.getNumDocumento()) != null) {
			throw new IllegalArgumentException("Ya existe un cliente con ese documento");
		}

		clienteRepo.guardar(cliente);
	}

	// Solo el Admin puede editar datos de un cliente existente
	public void editar(Cliente cliente) {
		clienteRepo.actualizar(cliente);
	}

	// Buscar cliente por documento para vincularlo a una venta
	public Cliente buscarPorDocumento(String numDocumento) {
		return clienteRepo.buscarPorDocumento(numDocumento);
	}

	public Cliente buscarPorId(int id) {
		return clienteRepo.buscarPorId(id);
	}

	public List<Cliente> listarTodos() {
		return clienteRepo.listarTodos();
	}
}
