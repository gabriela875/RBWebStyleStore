package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "venta")
public class Venta {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Venta")
	private int idVenta;

	@NotNull(message = "La fecha y hora son obligatorias")
	@Column(name = "Fecha_hora", nullable = false)
	private LocalDateTime fechaHora;

	@NotNull(message = "El total es obligatorio")
	@Column(name = "Total", nullable = false, precision = 12, scale = 2)
	private BigDecimal total;

	@NotNull(message = "El estado es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Estado", nullable = false)
	private EstadoVenta estado;

	@ManyToOne
	@JoinColumn(name = "id_Cliente", nullable = false)
	@NotNull(message = "El cliente es obligatorio")
	private Cliente cliente;

	@ManyToOne
	@JoinColumn(name = "id_Empleado", nullable = false)
	@NotNull(message = "El empleado es obligatorio")
	private Empleado empleado;

	public enum EstadoVenta {
		pendiente, completada, anulada
	}

	public Venta() {
	}

	public int getIdVenta() {
		return idVenta;
	}

	public void setIdVenta(int idVenta) {
		this.idVenta = idVenta;
	}

	public LocalDateTime getFechaHora() {
		return fechaHora;
	}

	public void setFechaHora(LocalDateTime fechaHora) {
		this.fechaHora = fechaHora;
	}

	public BigDecimal getTotal() {
		return total;
	}

	public void setTotal(BigDecimal total) {
		this.total = total;
	}

	public EstadoVenta getEstado() {
		return estado;
	}

	public void setEstado(EstadoVenta estado) {
		this.estado = estado;
	}

	public Cliente getCliente() {
		return cliente;
	}

	public void setCliente(Cliente cliente) {
		this.cliente = cliente;
	}

	public Empleado getEmpleado() {
		return empleado;
	}

	public void setEmpleado(Empleado empleado) {
		this.empleado = empleado;
	}
}
