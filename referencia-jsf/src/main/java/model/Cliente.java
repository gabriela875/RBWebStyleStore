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

	@ManyToOne
	@JoinColumn(name = "id_Tipo_Doc", nullable = false)
	@NotNull(message = "El tipo de documento es obligatorio")
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
	private String correo;

	public Cliente() {
	}

	public int getIdCliente() {
		return idCliente;
	}

	public void setIdCliente(int id) {
		this.idCliente = id;
	}

	public TipoDocumento getTipoDocumento() {
		return tipoDocumento;
	}

	public void setTipoDocumento(TipoDocumento tipo) {
		this.tipoDocumento = tipo;
	}

	public String getNumDocumento() {
		return numDocumento;
	}

	public void setNumDocumento(String num) {
		this.numDocumento = num;
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