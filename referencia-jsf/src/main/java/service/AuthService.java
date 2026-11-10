package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import jakarta.persistence.EntityManager;
import model.Empleado;
import model.EstadoBloqueo;
import repository.EmpleadoRepository;
import util.JpaUtil;

@ApplicationScoped
public class AuthService {

	@Inject
	private EmpleadoRepository empleadoRepo;

	private static final int MAX_INTENTOS = 5;

	// Buscar estado de bloqueo por nombre
	private EstadoBloqueo buscarEstadoBloqueo(String nombre) {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EstadoBloqueo e WHERE e.nombre = :nombre", EstadoBloqueo.class)
					.setParameter("nombre", nombre).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Intenta hacer login
	public Empleado login(String usuario, String contrasena) {
		Empleado empleado = empleadoRepo.buscarPorUsuario(usuario);

		if (empleado == null) {
			throw new IllegalArgumentException("El usuario no existe en el sistema");
		}

		// Verificar bloqueo
		if (empleado.getEstadoBloqueo().getNombre().equals("Bloqueado")) {
			throw new IllegalStateException("La cuenta está bloqueada. Contacte al Administrador para desbloquearla");
		}

		// Verificar estado activo
		if (!empleado.getEstadoEmpleado().getNombre().equals("Activo")) {
			throw new IllegalStateException("El empleado está inactivo y no puede iniciar sesión");
		}

		// Verificar contraseña
		if (!empleado.getContrasena().equals(contrasena)) {
			registrarIntentoFallido(empleado);
			if (empleado.getEstadoBloqueo().getNombre().equals("Bloqueado")) {
				throw new IllegalStateException("La cuenta ha sido bloqueada por demasiados intentos fallidos");
			}
			throw new IllegalArgumentException("Contraseña incorrecta");
		}

		// Login exitoso — reiniciar intentos
		empleado.setIntentosFallidos(0);
		empleadoRepo.actualizar(empleado);
		return empleado;
	}

	// Suma un intento fallido y bloquea si llega al límite
	private void registrarIntentoFallido(Empleado empleado) {
		int intentos = empleado.getIntentosFallidos() + 1;
		empleado.setIntentosFallidos(intentos);
		if (intentos >= MAX_INTENTOS) {
			empleado.setEstadoBloqueo(buscarEstadoBloqueo("Bloqueado"));
		}
		empleadoRepo.actualizar(empleado);
	}

	// Desbloquear cuenta
	public void desbloquearCuenta(int idEmpleado) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setEstadoBloqueo(buscarEstadoBloqueo("Desbloqueado"));
		empleado.setIntentosFallidos(0);
		empleadoRepo.actualizar(empleado);
	}

	// Restablecer contraseña
	public void restablecerContrasena(int idEmpleado, String nuevaContrasena) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setContrasena(nuevaContrasena);
		empleadoRepo.actualizar(empleado);
	}
}