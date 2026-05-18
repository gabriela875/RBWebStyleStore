package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;
import java.time.LocalDate;

@Entity
@Table(name = "descuento")
public class Descuento {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Descuento")
	private int idDescuento;

	@NotNull(message = "El tipo de descuento es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Tipo", nullable = false)
	private TipoDescuento tipo;

	@NotNull(message = "El valor del descuento es obligatorio")
	@DecimalMin(value = "0.01", message = "El valor debe ser mayor a cero")
	@Column(name = "Valor", nullable = false, precision = 12, scale = 2)
	private BigDecimal valor;

	@NotNull(message = "La fecha de inicio es obligatoria")
	@Column(name = "Fecha_inicio", nullable = false)
	private LocalDate fechaInicio;

	@NotNull(message = "La fecha de fin es obligatoria")
	@Column(name = "Fecha_fin", nullable = false)
	private LocalDate fechaFin;

	@Column(name = "Activo", nullable = false)
	private boolean activo;

	@ManyToOne
	@JoinColumn(name = "id_Producto", nullable = false)
	@NotNull(message = "El producto es obligatorio")
	private Producto producto;

	public enum TipoDescuento {
		porcentaje, valor_fijo
	}

	public Descuento() {
	}

	public int getIdDescuento() {
		return idDescuento;
	}

	public void setIdDescuento(int idDescuento) {
		this.idDescuento = idDescuento;
	}

	public TipoDescuento getTipo() {
		return tipo;
	}

	public void setTipo(TipoDescuento tipo) {
		this.tipo = tipo;
	}

	public BigDecimal getValor() {
		return valor;
	}

	public void setValor(BigDecimal valor) {
		this.valor = valor;
	}

	public LocalDate getFechaInicio() {
		return fechaInicio;
	}

	public void setFechaInicio(LocalDate fechaInicio) {
		this.fechaInicio = fechaInicio;
	}

	public LocalDate getFechaFin() {
		return fechaFin;
	}

	public void setFechaFin(LocalDate fechaFin) {
		this.fechaFin = fechaFin;
	}

	public boolean isActivo() {
		return activo;
	}

	public void setActivo(boolean activo) {
		this.activo = activo;
	}

	public Producto getProducto() {
		return producto;
	}

	public void setProducto(Producto producto) {
		this.producto = producto;
	}
}
