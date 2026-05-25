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

	@ManyToOne
	@JoinColumn(name = "id_Tipo_Desc", nullable = false)
	@NotNull(message = "El tipo de descuento es obligatorio")
	private TipoDescuento tipoDescuento;

	@NotNull(message = "El valor es obligatorio")
	@Column(name = "Valor", nullable = false, precision = 12, scale = 2)
	private BigDecimal valor;

	@NotNull(message = "La fecha de inicio es obligatoria")
	@Column(name = "Fecha_inicio", nullable = false)
	private LocalDate fechaInicio;

	@NotNull(message = "La fecha de fin es obligatoria")
	@Column(name = "Fecha_fin", nullable = false)
	private LocalDate fechaFin;

	@ManyToOne
	@JoinColumn(name = "id_Producto", nullable = false)
	@NotNull(message = "El producto es obligatorio")
	private Producto producto;

	@ManyToOne
	@JoinColumn(name = "id_Estado_Desc", nullable = false)
	@NotNull(message = "El estado del descuento es obligatorio")
	private EstadoDescuento estadoDescuento;

	public Descuento() {
	}

	public int getIdDescuento() {
		return idDescuento;
	}

	public void setIdDescuento(int id) {
		this.idDescuento = id;
	}

	public TipoDescuento getTipoDescuento() {
		return tipoDescuento;
	}

	public void setTipoDescuento(TipoDescuento tipo) {
		this.tipoDescuento = tipo;
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

	public void setFechaInicio(LocalDate fecha) {
		this.fechaInicio = fecha;
	}

	public LocalDate getFechaFin() {
		return fechaFin;
	}

	public void setFechaFin(LocalDate fecha) {
		this.fechaFin = fecha;
	}

	public Producto getProducto() {
		return producto;
	}

	public void setProducto(Producto producto) {
		this.producto = producto;
	}

	public EstadoDescuento getEstadoDescuento() {
		return estadoDescuento;
	}

	public void setEstadoDescuento(EstadoDescuento estado) {
		this.estadoDescuento = estado;
	}
}