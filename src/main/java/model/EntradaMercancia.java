package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "entrada_mercancia")
public class EntradaMercancia {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Entrada")
	private int idEntrada;

	@NotNull(message = "La fecha es obligatoria")
	@Column(name = "Fecha", nullable = false)
	private LocalDateTime fecha;

	@ManyToOne
	@JoinColumn(name = "id_Empleado", nullable = false)
	@NotNull(message = "El empleado es obligatorio")
	private Empleado empleado;

	@ManyToOne
	@JoinColumn(name = "id_Proveedor", nullable = false)
	@NotNull(message = "El proveedor es obligatorio")
	private Proveedor proveedor;

	public EntradaMercancia() {
	}

	public int getIdEntrada() {
		return idEntrada;
	}

	public void setIdEntrada(int idEntrada) {
		this.idEntrada = idEntrada;
	}

	public LocalDateTime getFecha() {
		return fecha;
	}

	public void setFecha(LocalDateTime fecha) {
		this.fecha = fecha;
	}

	public Empleado getEmpleado() {
		return empleado;
	}

	public void setEmpleado(Empleado empleado) {
		this.empleado = empleado;
	}

	public Proveedor getProveedor() {
		return proveedor;
	}

	public void setProveedor(Proveedor proveedor) {
		this.proveedor = proveedor;
	}
}
