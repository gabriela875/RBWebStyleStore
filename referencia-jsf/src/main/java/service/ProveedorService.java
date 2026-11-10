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
		if (proveedorRepo.buscarPorNombre(proveedor.getNombre()) != null) {
			throw new IllegalArgumentException("Ya existe un proveedor con ese nombre");
		}
		if (proveedorRepo.buscarPorCorreo(proveedor.getCorreo()) != null) {
			throw new IllegalArgumentException("Ya existe un proveedor con ese correo");
		}
		proveedorRepo.guardar(proveedor);
	}

	// Solo el Admin puede editar proveedores
	public void editar(Proveedor proveedor) {
		Proveedor existeNombre = proveedorRepo.buscarPorNombre(proveedor.getNombre());
		if (existeNombre != null && existeNombre.getIdProveedor() != proveedor.getIdProveedor()) {
			throw new IllegalArgumentException("Ya existe otro proveedor con ese nombre");
		}
		Proveedor existeCorreo = proveedorRepo.buscarPorCorreo(proveedor.getCorreo());
		if (existeCorreo != null && existeCorreo.getIdProveedor() != proveedor.getIdProveedor()) {
			throw new IllegalArgumentException("Ya existe otro proveedor con ese correo");
		}
		proveedorRepo.actualizar(proveedor);
	}

	// No se puede eliminar si tiene productos asociados
	public void eliminar(int idProveedor) {
		if (proveedorRepo.tieneProductos(idProveedor)) {
			throw new IllegalStateException("No se puede eliminar un proveedor con productos asociados");
		}
		proveedorRepo.eliminar(idProveedor);
	}

	public Proveedor buscarPorId(int id) {
		return proveedorRepo.buscarPorId(id);
	}

	public List<Proveedor> listarTodos() {
		return proveedorRepo.listarTodos();
	}
}
