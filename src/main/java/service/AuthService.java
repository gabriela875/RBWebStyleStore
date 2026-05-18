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

		// Verificar que el usuario exista
		if (empleado == null) {
			return null;
		}

		// Verificar que la cuenta no este bloqueada
		if (empleado.isBloqueado()) {
			return null;
		}

		// Verificar que el empleado este activo
		if (!empleado.isEstado()) {
			return null;
		}

		// Verificar contrasena
		if (!empleado.getContrasena().equals(contrasena)) {
			registrarIntentoFallido(empleado);
			return null;
		}

		// Login exitoso: reiniciar intentos fallidos
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
