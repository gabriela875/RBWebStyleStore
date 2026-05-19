package service;

import jakarta.enterprise.context.ApplicationScoped;
import jakarta.inject.Inject;
import model.Empleado;
import repository.EmpleadoRepository;

@ApplicationScoped
public class AuthService {

	@Inject
	private EmpleadoRepository empleadoRepo;

	private static final int MAX_INTENTOS = 5;

	// Intenta hacer login, retorna el empleado si es exitoso o null si falla
	public Empleado login(String usuario, String contrasena) {

		Empleado empleado = empleadoRepo.buscarPorUsuario(usuario);

		if (empleado == null) {
			throw new IllegalArgumentException("El usuario no existe en el sistema");
		}

		if (empleado.isBloqueado()) {
			throw new IllegalStateException("La cuenta está bloqueada. Contacte al Administrador para desbloquearla");
		}

		if (!empleado.isEstado()) {
			throw new IllegalStateException("El empleado está inactivo y no puede iniciar sesión");
		}

		if (!empleado.getContrasena().equals(contrasena)) {
			registrarIntentoFallido(empleado);
			int intentosRestantes = MAX_INTENTOS - empleado.getIntentosFallidos();
			if (empleado.isBloqueado()) {
				throw new IllegalStateException("La cuenta ha sido bloqueada por demasiados intentos fallidos");
			}
			throw new IllegalArgumentException("Contraseña incorrecta");
		}

		empleado.setIntentosFallidos(0);
		empleadoRepo.actualizar(empleado);
		return empleado;
	}

	// Suma un intento fallido y bloquea la cuenta si llega al limite
	private void registrarIntentoFallido(Empleado empleado) {
		int intentos = empleado.getIntentosFallidos() + 1;
		empleado.setIntentosFallidos(intentos);

		if (intentos >= MAX_INTENTOS) {
			empleado.setBloqueado(true);
		}

		empleadoRepo.actualizar(empleado);
	}

	// Solo el Admin puede desbloquear una cuenta
	public void desbloquearCuenta(int idEmpleado) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setBloqueado(false);
		empleado.setIntentosFallidos(0);
		empleadoRepo.actualizar(empleado);
	}

	// Solo el Admin puede restablecer la contrasena
	public void restablecerContrasena(int idEmpleado, String nuevaContrasena) {
		Empleado empleado = empleadoRepo.buscarPorId(idEmpleado);
		empleado.setContrasena(nuevaContrasena);
		empleadoRepo.actualizar(empleado);
	}
}
