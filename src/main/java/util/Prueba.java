package util;

import model.Cargo;
import model.Empleado;
import repository.EmpleadoRepository;
import service.AuthService;

public class Prueba {

	public static void main(String[] args) {

		System.out.println("=== Prueba de conexion ===");

		// Prueba 1: conexion y busqueda de empleado
		EmpleadoRepository repo = new EmpleadoRepository();
		Empleado empleado = repo.buscarPorUsuario("admin");

		if (empleado != null) {
			System.out.println("Empleado encontrado: " + empleado.getNombre() + " " + empleado.getApellido());
			System.out.println("Cargo: " + empleado.getCargo().getTipo());
		} else {
			System.out.println("No se encontro el empleado");
		}

		// Prueba 2: login exitoso
		AuthService authService = new AuthService();
		// Como AuthService usa @Inject necesitamos instanciar el repo manualmente
		// para esta prueba simple
		EmpleadoRepository repoAuth = new EmpleadoRepository();

		Empleado resultado = repo.buscarPorUsuario("admin");
		if (resultado != null && resultado.getContrasena().equals("admin123")) {
			System.out.println("Login exitoso para: " + resultado.getUsuario());
		} else {
			System.out.println("Login fallido");
		}

		System.out.println("=== Prueba finalizada ===");

		JpaUtil.closeEntityManagerFactory();
	}
}
