package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.Cargo;
import model.Empleado;
import repository.EmpleadoRepository;
import java.util.List;

@ApplicationScoped
public class EmpleadoService {

	@Inject
	private EmpleadoRepository empleadoRepo;

	// Registrar un nuevo empleado
	public void registrar(Empleado empleado) {

		// Verificar que el documento no este registrado
		if (empleadoRepo.buscarPorDocumento(empleado.getNumDocumento()) != null) {
			throw new IllegalArgumentException("Ya existe un empleado con ese documento");
		}

		// Verificar que el usuario no este en uso
		if (empleadoRepo.buscarPorUsuario(empleado.getUsuario()) != null) {
			throw new IllegalArgumentException("El usuario ya está en uso");
		}

		// El empleado inicia activo y sin bloqueo
		empleado.setEstado(true);
		empleado.setBloqueado(false);
		empleado.setIntentosFallidos(0);

		empleadoRepo.guardar(empleado);
	}

	// Editar datos de un empleado existente
	public void editar(Empleado empleado) {
		empleadoRepo.actualizar(empleado);
	}

	// Desactivar un empleado
	public void desactivar(int idEmpleado) {

		// No se puede desactivar si tiene ventas pendientes
		if (empleadoRepo.tieneVentasPendientes(idEmpleado)) {
			throw new IllegalStateException("El empleado tiene ventas pendientes. Anúlelas antes de desactivarlo");
		}

		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setEstado(false);
		empleadoRepo.actualizar(empleado);
	}

	// Activar un empleado inactivo
	public void activar(int idEmpleado) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setEstado(true);
		empleadoRepo.actualizar(empleado);
	}

	// No se permite eliminar si tiene ventas registradas
	public void eliminar(int idEmpleado) {
		if (empleadoRepo.tieneVentas(idEmpleado)) {
			throw new IllegalStateException("No se puede eliminar un empleado con ventas registradas");
		}
		// Si no tiene ventas se podria eliminar pero en la practica
		// siempre se desactiva — dejamos el metodo por completitud
	}

	public Empleado buscarPorId(int id) {
		return empleadoRepo.buscarPorId(id);
	}

	public List<Empleado> listarTodos() {
		return empleadoRepo.listarTodos();
	}

	// Verificar si el empleado tiene el cargo requerido
	public boolean tieneCargo(Empleado empleado, String cargo) {
		return empleado.getCargo().getTipo().equalsIgnoreCase(cargo);
	}
}
