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
	@DecimalMin(value = "0.01", message = "El valor debe ser mayor a cero")
	@Column(name = "Valor", nullable = false, precision = 12, scale = 2)
	private BigDecimal valor;

	@NotNull(message = "El método de pago es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Metodo", nullable = false)
	private MetodoPago metodo;

	@Column(name = "Referencia")
	private String referencia; // solo tarjeta o transferencia, null para efectivo

	@Column(name = "Cambio", precision = 12, scale = 2)
	private BigDecimal cambio; // solo efectivo cuando paga de mas, null en otros casos

	@ManyToOne
	@JoinColumn(name = "id_Venta", nullable = false)
	@NotNull(message = "La venta es obligatoria")
	private Venta venta;

	public enum MetodoPago {
		efectivo, tarjeta, transferencia
	}

	public Pago() {
	}

	public int getIdPago() {
		return idPago;
	}

	public void setIdPago(int idPago) {
		this.idPago = idPago;
	}

	public BigDecimal getValor() {
		return valor;
	}

	public void setValor(BigDecimal valor) {
		this.valor = valor;
	}

	public MetodoPago getMetodo() {
		return metodo;
	}

	public void setMetodo(MetodoPago metodo) {
		this.metodo = metodo;
	}

	public String getReferencia() {
		return referencia;
	}

	public void setReferencia(String referencia) {
		this.referencia = referencia;
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
