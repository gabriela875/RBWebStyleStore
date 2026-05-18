package bean;

import jakarta.faces.view.ViewScoped;
import java.io.Serializable;
import jakarta.inject.Inject;
import jakarta.inject.Named;
import model.Cargo;
import model.Empleado;
import service.EmpleadoService;
import jakarta.persistence.EntityManager;
import util.JpaUtil;
import java.util.List;

@Named
@ViewScoped
public class EmpleadoBean implements Serializable {
	private static final long serialVersionUID = 1L;
	private int idCargoSeleccionado;

	@Inject
	private EmpleadoService empleadoService;

	@Inject
	private SessionBean sessionBean;

	private Empleado empleado = new Empleado();
	private List<Empleado> listaEmpleados;
	private String mensajeExito;
	private String mensajeError;

	// Registrar un nuevo empleado
	public void registrar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "No tiene permisos para registrar empleados";
			return;
		}

		Cargo cargo = null;
		for (Cargo c : getListaCargos()) {
			if (c.getIdCargo() == idCargoSeleccionado) {
				cargo = c;
				break;
			}
		}

		if (cargo == null) {
			mensajeError = "Debe seleccionar un cargo";
			return;
		}

		empleado.setCargo(cargo);

		try {
			empleadoService.registrar(empleado);
			mensajeExito = "Empleado registrado correctamente";
			empleado = new Empleado();
			idCargoSeleccionado = 0;
			listaEmpleados = null;
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Editar un empleado existente
	public void editar() {
		mensajeExito = null;
		mensajeError = null;

		if (!sessionBean.isAdmin()) {
			mensajeError = "No tiene permisos para editar empleados";
			return;
		}

		try {
			empleadoService.editar(empleado);
			mensajeExito = "Empleado actualizado correctamente";
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
	}

	// Desactivar un empleado
	public String desactivar(int idEmpleado) {
		mensajeExito = null;
		mensajeError = null;

		try {
			empleadoService.desactivar(idEmpleado);
			mensajeExito = "Empleado desactivado correctamente";
			listaEmpleados = null; // refrescar lista
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Activar un empleado
	public String activar(int idEmpleado) {
		mensajeExito = null;
		mensajeError = null;

		try {
			empleadoService.activar(idEmpleado);
			mensajeExito = "Empleado activado correctamente";
			listaEmpleados = null; // refrescar lista
		} catch (Exception e) {
			mensajeError = e.getMessage();
		}
		return null;
	}

	// Cargar un empleado para editar
	public void cargarEmpleado(int idEmpleado) {
		empleado = empleadoService.buscarPorId(idEmpleado);
	}

	// Listar todos los empleados — se carga una sola vez por request
	public List<Empleado> getListaEmpleados() {
		if (listaEmpleados == null) {
			listaEmpleados = empleadoService.listarTodos();
		}
		return listaEmpleados;
	}

	// Obtener lista de cargos para el formulario
	public List<Cargo> getListaCargos() {
		EntityManager em = JpaUtil.getEntityManagerFactory().createEntityManager();
		try {
			return em.createQuery("SELECT c FROM Cargo c", Cargo.class).getResultList();
		} finally {
			em.close();
		}
	}

	public Empleado getEmpleado() {
		return empleado;
	}

	public void setEmpleado(Empleado empleado) {
		this.empleado = empleado;
	}

	public String getMensajeExito() {
		return mensajeExito;
	}

	public String getMensajeError() {
		return mensajeError;
	}

	public int getIdCargoSeleccionado() {
		return idCargoSeleccionado;
	}

	public void setIdCargoSeleccionado(int idCargoSeleccionado) {
		this.idCargoSeleccionado = idCargoSeleccionado;
	}
}
