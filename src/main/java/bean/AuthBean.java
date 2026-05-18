package bean;

import jakarta.faces.view.ViewScoped;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import service.AuthService;
import java.io.Serializable;

@Named
@ViewScoped // CORREGIDO: era @RequestScoped — los mensajes se perdían antes de renderizarse
public class AuthBean implements Serializable { // CORREGIDO: faltaba Serializable

	private static final long serialVersionUID = 1L;

	@Inject
	private AuthService authService;

	@Inject
	private SessionBean sessionBean;

	private String nuevaContrasena;
	private String mensajeExito;
	private String mensajeError;

	// Desbloquear cuenta — solo Admin
	public void desbloquear(int idEmpleado) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede desbloquear cuentas";
			return;
		}

		try {
			authService.desbloquearCuenta(idEmpleado);
			mensajeExito = "Cuenta desbloqueada correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Restablecer contraseña — solo Admin
	public void restablecerContrasena(int idEmpleado) {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "Solo el Administrador puede restablecer contraseñas";
			return;
		}

		if (nuevaContrasena == null || nuevaContrasena.trim().length() < 6) {
			mensajeError = "La contraseña debe tener al menos 6 caracteres";
			return;
		}

		try {
			authService.restablecerContrasena(idEmpleado, nuevaContrasena.trim());
			mensajeExito = "Contraseña restablecida correctamente";
			nuevaContrasena = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	public String getNuevaContrasena() {
		return nuevaContrasena;
	}

	public void setNuevaContrasena(String nuevaContrasena) {
		this.nuevaContrasena = nuevaContrasena;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}
}