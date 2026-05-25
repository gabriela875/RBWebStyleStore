package model;

import jakarta.persistence.*;

@Entity
@Table(name = "estado_bloqueo")
public class EstadoBloqueo {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Estado_Bloq")
	private int idEstadoBloq;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public EstadoBloqueo() {
	}

	public int getIdEstadoBloq() {
		return idEstadoBloq;
	}

	public void setIdEstadoBloq(int id) {
		this.idEstadoBloq = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
