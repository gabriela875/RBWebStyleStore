package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estado_producto")
public class EstadoProducto {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Estado_Prod")
	private int idEstadoProd;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public EstadoProducto() {
	}

	public int getIdEstadoProd() {
		return idEstadoProd;
	}

	public void setIdEstadoProd(int id) {
		this.idEstadoProd = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
