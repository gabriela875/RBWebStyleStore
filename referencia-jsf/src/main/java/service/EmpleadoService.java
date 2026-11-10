package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import jakarta.persistence.EntityManager;
import model.*;
import repository.EmpleadoRepository;
import util.JpaUtil;
import java.util.List;

@ApplicationScoped
public class EmpleadoService {

	@Inject
	private EmpleadoRepository empleadoRepo;

	// Buscar estado por nombre desde la BD
	private EstadoEmpleado buscarEstadoEmpleado(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoEmpleado e WHERE e.nombre = :nombre", EstadoEmpleado.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	private EstadoBloqueo buscarEstadoBloqueo(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoBloqueo e WHERE e.nombre = :nombre", EstadoBloqueo.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Registrar un nuevo empleado
	public void registrar(Empleado empleado) {
		if (empleadoRepo.buscarPorDocumento(empleado.getNumDocumento()) != null) {
			throw new IllegalArgumentException("Ya existe un empleado con ese documento");
		}
		if (empleadoRepo.buscarPorUsuario(empleado.getUsuario()) != null) {
			throw new IllegalArgumentException("El usuario ya está en uso");
		}
		empleado.setEstadoEmpleado(buscarEstadoEmpleado("Activo"));
		empleado.setEstadoBloqueo(buscarEstadoBloqueo("Desbloqueado"));
		empleado.setIntentosFallidos(0);
		empleadoRepo.guardar(empleado);
	}

	// Editar datos de un empleado
	public void editar(Empleado empleado) {
		empleadoRepo.actualizar(empleado);
	}

	// Desactivar un empleado
	public void desactivar(int idEmpleado) {
		if (empleadoRepo.tieneVentasPendientes(idEmpleado)) {
			throw new IllegalStateException("El empleado tiene ventas pendientes. Anúlelas antes de desactivarlo");
		}
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setEstadoEmpleado(buscarEstadoEmpleado("Inactivo"));
		empleadoRepo.actualizar(empleado);
	}

	// Activar un empleado
	public void activar(int idEmpleado) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setEstadoEmpleado(buscarEstadoEmpleado("Activo"));
		empleadoRepo.actualizar(empleado);
	}

	public Empleado buscarPorId(int id) {
		return empleadoRepo.buscarPorId(id);
	}

	public List<Empleado> listarTodos() {
		return empleadoRepo.listarTodos();
	}

	public boolean tieneCargo(Empleado empleado, String cargo) {
		return empleado.getCargo().getTipo().equalsIgnoreCase(cargo);
	}
}