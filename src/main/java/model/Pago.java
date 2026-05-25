package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;

@Entity
@Table(name = "pago")
public class Pago {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Pago")
	private int idPago;

	@NotNull(message = "El valor es obligatorio")
	@Column(name = "Valor", nullable = false, precision = 12, scale = 2)
	private BigDecimal valor;

	@ManyToOne
	@JoinColumn(name = "id_Metodo_Pago", nullable = false)
	@NotNull(message = "El método de pago es obligatorio")
	private MetodoPago metodoPago;

	@Column(name = "Referencia")
	private String referencia;

	@Column(name = "Cambio", precision = 12, scale = 2)
	private BigDecimal cambio;

	@ManyToOne
	@JoinColumn(name = "id_Venta", nullable = false)
	private Venta venta;

	public Pago() {
	}

	public int getIdPago() {
		return idPago;
	}

	public void setIdPago(int id) {
		this.idPago = id;
	}

	public BigDecimal getValor() {
		return valor;
	}

	public void setValor(BigDecimal valor) {
		this.valor = valor;
	}

	public MetodoPago getMetodoPago() {
		return metodoPago;
	}

	public void setMetodoPago(MetodoPago metodo) {
		this.metodoPago = metodo;
	}

	public String getReferencia() {
		return referencia;
	}

	public void setReferencia(String ref) {
		this.referencia = ref;
	}

	public BigDecimal getCambio() {
		return cambio;
	}

	public void setCambio(BigDecimal cambio) {
		this.cambio = cambio;
	}

	public Venta getVenta() {
		return venta;
	}

	public void setVenta(Venta venta) {
		this.venta = venta;
	}
}