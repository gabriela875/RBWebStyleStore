package model;

import jakarta.persistence.*;

@Entity
@Table(name = "tipo_descuento")
public class TipoDescuento {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Tipo_Desc")
	private int idTipoDesc;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public TipoDescuento() {
	}

	public int getIdTipoDesc() {
		return idTipoDesc;
	}

	public void setIdTipoDesc(int id) {
		this.idTipoDesc = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
