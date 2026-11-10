package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.NotBlank;

@Entity
@Table(name = "talla")
public class Talla {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Talla")
	private int idTalla;

	@NotBlank(message = "El nombre de la talla es obligatorio")
	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	public Talla() {
	}

	public int getIdTalla() {
		return idTalla;
	}

	public void setIdTalla(int idTalla) {
		this.idTalla = idTalla;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}
}
