package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "anulacion_venta")
public class AnulacionVenta {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Anulacion")
	private int idAnulacion;

	@NotBlank(message = "El motivo es obligatorio")
	@Column(name = "Motivo", nullable = false)
	private String motivo;

	@NotNull(message = "La fecha y hora son obligatorias")
	@Column(name = "Fecha_hora", nullable = false)
	private LocalDateTime fechaHora;

	@ManyToOne
	@JoinColumn(name = "id_Venta", nullable = false)
	@NotNull(message = "La venta es obligatoria")
	private Venta venta;

	@ManyToOne
	@JoinColumn(name = "id_Empleado", nullable = false)
	@NotNull(message = "El empleado es obligatorio")
	private Empleado empleado;

	public AnulacionVenta() {
	}

	public int getIdAnulacion() {
		return idAnulacion;
	}

	public void setIdAnulacion(int idAnulacion) {
		this.idAnulacion = idAnulacion;
	}

	public String getMotivo() {
		return motivo;
	}

	public void setMotivo(String motivo) {
		this.motivo = motivo;
	}

	public LocalDateTime getFechaHora() {
		return fechaHora;
	}

	public void setFechaHora(LocalDateTime fechaHora) {
		this.fechaHora = fechaHora;
	}

	public Venta getVenta() {
		return venta;
	}

	public void setVenta(Venta venta) {
		this.venta = venta;
	}

	public Empleado getEmpleado() {
		return empleado;
	}

	public void setEmpleado(Empleado empleado) {
		this.empleado = empleado;
	}
}
