package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "ajuste_inventario")
public class AjusteInventario {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Ajuste")
	private int idAjuste;

	@NotBlank(message = "El motivo es obligatorio")
	@Column(name = "Motivo", nullable = false)
	private String motivo;

	@Column(name = "Cantidad_ajustada", nullable = false)
	private int cantidadAjustada; // positivo = entrada, negativo = salida

	@NotNull(message = "La fecha es obligatoria")
	@Column(name = "Fecha", nullable = false)
	private LocalDateTime fecha;

	@ManyToOne
	@JoinColumn(name = "id_Inventario", nullable = false)
	@NotNull(message = "El inventario es obligatorio")
	private Inventario inventario;

	@ManyToOne
	@JoinColumn(name = "id_Empleado", nullable = false)
	@NotNull(message = "El empleado es obligatorio")
	private Empleado empleado;

	public AjusteInventario() {
	}

	public int getIdAjuste() {
		return idAjuste;
	}

	public void setIdAjuste(int idAjuste) {
		this.idAjuste = idAjuste;
	}

	public String getMotivo() {
		return motivo;
	}

	public void setMotivo(String motivo) {
		this.motivo = motivo;
	}

	public int getCantidadAjustada() {
		return cantidadAjustada;
	}

	public void setCantidadAjustada(int cantidadAjustada) {
		this.cantidadAjustada = cantidadAjustada;
	}

	public LocalDateTime getFecha() {
		return fecha;
	}

	public void setFecha(LocalDateTime fecha) {
		this.fecha = fecha;
	}

	public Inventario getInventario() {
		return inventario;
	}

	public void setInventario(Inventario inventario) {
		this.inventario = inventario;
	}

	public Empleado getEmpleado() {
		return empleado;
	}

	public void setEmpleado(Empleado empleado) {
		this.empleado = empleado;
	}
}
