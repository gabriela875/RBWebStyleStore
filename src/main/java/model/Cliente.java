package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;

@Entity
@Table(name = "clientes")
public class Cliente {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Cliente")
	private int idCliente;

	@NotNull(message = "El tipo de documento es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Tipo_Documento", nullable = false)
	private TipoDocumento tipoDocumento;

	@NotBlank(message = "El número de documento es obligatorio")
	@Column(name = "Num_Documento", nullable = false, unique = true)
	private String numDocumento;

	@NotBlank(message = "El nombre es obligatorio")
	@Column(name = "Nombre", nullable = false)
	private String nombre;

	@NotBlank(message = "El apellido es obligatorio")
	@Column(name = "Apellido", nullable = false)
	private String apellido;

	@Email(message = "El correo debe tener formato válido")
	@Column(name = "Correo")
	private String correo; // opcional, puede ser null

	public enum TipoDocumento {//Listado
		cedula, pasaporte
	}

	public Cliente() {
	}

	public int getIdCliente() {
		return idCliente;
	}

	public void setIdCliente(int idCliente) {
		this.idCliente = idCliente;
	}

	public TipoDocumento getTipoDocumento() {
		return tipoDocumento;
	}

	public void setTipoDocumento(TipoDocumento tipoDocumento) {
		this.tipoDocumento = tipoDocumento;
	}

	public String getNumDocumento() {
		return numDocumento;
	}

	public void setNumDocumento(String numDocumento) {
		this.numDocumento = numDocumento;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}

	public String getApellido() {
		return apellido;
	}

	public void setApellido(String apellido) {
		this.apellido = apellido;
	}

	public String getCorreo() {
		return correo;
	}

	public void setCorreo(String correo) {
		this.correo = correo;
	}
}