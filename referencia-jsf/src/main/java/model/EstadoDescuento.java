package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estado_descuento")
public class EstadoDescuento {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Estado_Desc")
	private int idEstadoDesc;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public EstadoDescuento() {
	}

	public int getIdEstadoDesc() {
		return idEstadoDesc;
	}

	public void setIdEstadoDesc(int id) {
		this.idEstadoDesc = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
