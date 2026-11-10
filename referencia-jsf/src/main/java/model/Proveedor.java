package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;

@Entity
@Table(name = "proveedor")
public class Proveedor {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Proveedor")
	private int idProveedor;

	@NotBlank(message = "El nombre del proveedor es obligatorio")
	@Column(name = "Nombre", nullable = false, unique = true)
	private String nombre;

	@NotBlank(message = "El teléfono es obligatorio")
	@Column(name = "Telefono", nullable = false)
	private String telefono;

	@NotBlank(message = "El correo es obligatorio")
	@Email(message = "El correo debe tener formato válido")
	@Column(name = "Correo", nullable = false, unique = true)
	private String correo;

	public Proveedor() {
	}

	public int getIdProveedor() {
		return idProveedor;
	}

	public void setIdProveedor(int idProveedor) {
		this.idProveedor = idProveedor;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}

	public String getTelefono() {
		return telefono;
	}

	public void setTelefono(String telefono) {
		this.telefono = telefono;
	}

	public String getCorreo() {
		return correo;
	}

	public void setCorreo(String correo) {
		this.correo = correo;
	}
}
