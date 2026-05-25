package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estado_venta")
public class EstadoVenta {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Estado_Venta")
	private int idEstadoVenta;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public EstadoVenta() {
	}

	public int getIdEstadoVenta() {
		return idEstadoVenta;
	}

	public void setIdEstadoVenta(int id) {
		this.idEstadoVenta = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}