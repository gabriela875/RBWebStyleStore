package bean;

import jakarta.enterprise.context.RequestScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.Empleado;
import service.AuthService;

@Named
@RequestScoped
public class LoginBean {

	@Inject
	private AuthService authService;

	@Inject
	private SessionBean sessionBean;

	private String usuario;
	private String contrasena;
	private String mensajeError;

	// Intentar login y redirigir segun el cargo
	public String login() {

		mensajeError = null;

		Empleado empleado = authService.login(usuario, contrasena);

		// Si el login fallo mostrar mensaje
		if (empleado == null) {
			mensajeError = "Usuario o contraseña incorrectos, " + "o la cuenta está bloqueada/inactiva";
			return null;
		}

		// Guardar empleado en la sesion
		sessionBean.iniciarSesion(empleado);

		// Redirigir segun el cargo
		String cargo = empleado.getCargo().getTipo();

		if (cargo.equals("Administrador")) {
			return "admin";
		} else if (cargo.equals("Vendedor")) {
			return "vendedor";
		} else if (cargo.equals("Bodeguero")) {
			return "bodeguero";
		}

		return null;
	}

	// Cerrar sesion y volver al login
	public String logout() {
		sessionBean.cerrarSesion();
		return "/index?faces-redirect=true";
	}

	public String getUsuario() {
		return usuario;
	}

	public void setUsuario(String usuario) {
		this.usuario = usuario;
	}

	public String getContrasena() {
		return contrasena;
	}

	public void setContrasena(String contrasena) {
		this.contrasena = contrasena;
	}

	public String getMensajeError() {
		return mensajeError;
	}
}