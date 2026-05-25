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

	@ManyToOne
	@JoinColumn(name = "id_Estado_Venta", nullable = false)
	@NotNull(message = "El estado es obligatorio")
	private EstadoVenta estadoVenta;

	@ManyToOne
	@JoinColumn(name = "id_Cliente", nullable = false)
	@NotNull(message = "El cliente es obligatorio")
	private Cliente cliente;

	@ManyToOne
	@JoinColumn(name = "id_Empleado", nullable = false)
	@NotNull(message = "El empleado es obligatorio")
	private Empleado empleado;

	public Venta() {
	}

	public int getIdVenta() {
		return idVenta;
	}

	public void setIdVenta(int id) {
		this.idVenta = id;
	}

	public LocalDateTime getFechaHora() {
		return fechaHora;
	}

	public void setFechaHora(LocalDateTime fecha) {
		this.fechaHora = fecha;
	}

	public BigDecimal getTotal() {
		return total;
	}

	public void setTotal(BigDecimal total) {
		this.total = total;
	}

	public EstadoVenta getEstadoVenta() {
		return estadoVenta;
	}

	public void setEstadoVenta(EstadoVenta estado) {
		this.estadoVenta = estado;
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