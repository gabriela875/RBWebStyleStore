package bean;

import jakarta.enterprise.context.SessionScoped;
import jakarta.inject.Named;
import model.Empleado;
import java.io.Serializable;

@Named
@SessionScoped
public class SessionBean implements Serializable {

	private Empleado empleadoActivo;

	// Guardar el empleado que inicio sesion
	public void iniciarSesion(Empleado empleado) {
		this.empleadoActivo = empleado;
	}

	// Limpiar la sesion al cerrar
	public void cerrarSesion() {
		this.empleadoActivo = null;
	}

	// Verificar si hay alguien logueado
	public boolean isLogueado() {
		return empleadoActivo != null;
	}

	// Verificar si el empleado activo es Administrador
	public boolean isAdmin() {
		if (empleadoActivo == null)
			return false;
		return empleadoActivo.getCargo().getTipo().equals("Administrador");
	}

	// Verificar si el empleado activo es Vendedor
	public boolean isVendedor() {
		if (empleadoActivo == null)
			return false;
		return empleadoActivo.getCargo().getTipo().equals("Vendedor");
	}

	// Verificar si el empleado activo es Bodeguero
	public boolean isBodeguero() {
		if (empleadoActivo == null)
			return false;
		return empleadoActivo.getCargo().getTipo().equals("Bodeguero");
	}

	public Empleado getEmpleadoActivo() {
		return empleadoActivo;
	}

	public void setEmpleadoActivo(Empleado empleadoActivo) {
		this.empleadoActivo = empleadoActivo;
	}
}