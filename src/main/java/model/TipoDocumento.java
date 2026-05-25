package model;

import jakarta.persistence.*;

@Entity
@Table(name = "tipo_documento")
public class TipoDocumento {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Tipo_Doc")
	private int idTipoDoc;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public TipoDocumento() {
	}

	public int getIdTipoDoc() {
		return idTipoDoc;
	}

	public void setIdTipoDoc(int id) {
		this.idTipoDoc = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
