package model;

import jakarta.persistence.*;

@Entity
@Table(name = "genero")
public class Genero {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Genero")
	private int idGenero;

	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public Genero() {
	}

	public int getIdGenero() {
		return idGenero;
	}

	public void setIdGenero(int id) {
		this.idGenero = id;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}