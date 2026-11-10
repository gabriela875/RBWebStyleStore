package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;

@Entity
@Table(name = "empleados")
public class Empleado {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Empleado")
	private int idEmpleado;

	@NotBlank(message = "El número de documento es obligatorio")
	@Column(name = "Num_Documento", nullable = false, unique = true)
	private String numDocumento;

	@NotBlank(message = "El nombre es obligatorio")
	@Column(name = "Nombre", nullable = false)
	private String nombre;

	@NotBlank(message = "El apellido es obligatorio")
	@Column(name = "Apellido", nullable = false)
	private String apellido;

	@NotBlank(message = "El usuario es obligatorio")
	@Column(name = "Usuario", nullable = false, unique = true)
	private String usuario;

	@NotBlank(message = "La contraseña es obligatoria")
	@Column(name = "Contrasena", nullable = false)
	private String contrasena;

	@Column(name = "Intentos_fallidos", nullable = false)
	private int intentosFallidos;

	@ManyToOne
	@JoinColumn(name = "id_Cargo", nullable = false)
	@NotNull(message = "El cargo es obligatorio")
	private Cargo cargo;

	@ManyToOne
	@JoinColumn(name = "id_Estado_Emp", nullable = false)
	@NotNull(message = "El estado es obligatorio")
	private EstadoEmpleado estadoEmpleado;

	@ManyToOne
	@JoinColumn(name = "id_Estado_Bloq", nullable = false)
	@NotNull(message = "El estado de bloqueo es obligatorio")
	private EstadoBloqueo estadoBloqueo;

	public Empleado() {
	}

	// Getters y setters
	public int getIdEmpleado() {
		return idEmpleado;
	}

	public void setIdEmpleado(int id) {
		this.idEmpleado = id;
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

	public String getUsuario() {
		return usuario;
	}

	public void setUsuario(String usuario) {
		this.usuario = usuario;
	}

	public String getContrasena() {
		return contrasena;
	}

	public void setContrasena(String contrasena) {
		this.contrasena = contrasena;
	}

	public int getIntentosFallidos() {
		return intentosFallidos;
	}

	public void setIntentosFallidos(int intentos) {
		this.intentosFallidos = intentos;
	}

	public Cargo getCargo() {
		return cargo;
	}

	public void setCargo(Cargo cargo) {
		this.cargo = cargo;
	}

	public EstadoEmpleado getEstadoEmpleado() {
		return estadoEmpleado;
	}

	public void setEstadoEmpleado(EstadoEmpleado estado) {
		this.estadoEmpleado = estado;
	}

	public EstadoBloqueo getEstadoBloqueo() {
		return estadoBloqueo;
	}

	public void setEstadoBloqueo(EstadoBloqueo bloqueo) {
		this.estadoBloqueo = bloqueo;
	}
}