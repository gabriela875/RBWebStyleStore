package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estado_empleado")
public class EstadoEmpleado {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Estado_Emp")
	private int idEstadoEmp;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public EstadoEmpleado() {
	}

	public int getIdEstadoEmp() {
		return idEstadoEmp;
	}

	public void setIdEstadoEmp(int id) {
		this.idEstadoEmp = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
