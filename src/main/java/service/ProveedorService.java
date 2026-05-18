package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.Proveedor;
import repository.ProveedorRepository;
import java.util.List;

@ApplicationScoped
public class ProveedorService {

	@Inject
	private ProveedorRepository proveedorRepo;

	// Solo el Admin puede registrar proveedores
	public void registrar(Proveedor proveedor) {
		proveedorRepo.guardar(proveedor);
	}

	// Solo el Admin puede editar proveedores
	public void editar(Proveedor proveedor) {
		proveedorRepo.actualizar(proveedor);
	}

	// No se puede eliminar si tiene productos asociados
	public void eliminar(int idProveedor) {
		if (proveedorRepo.tieneProductos(idProveedor)) {
			throw new IllegalStateException("No se puede eliminar un proveedor con productos asociados");
		}
		// En la practica se da de baja logicamente desde el Admin
	}

	public Proveedor buscarPorId(int id) {
		return proveedorRepo.buscarPorId(id);
	}

	public List<Proveedor> listarTodos() {
		return proveedorRepo.listarTodos();
	}
}
