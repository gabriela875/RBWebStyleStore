package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;

@Entity
@Table(name = "detalle_venta")
public class DetalleVenta {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Detalle_Venta")
	private int idDetalleVenta;

	@Min(value = 1, message = "La cantidad debe ser al menos 1")
	@Column(name = "Cantidad", nullable = false)
	private int cantidad;

	@NotNull(message = "El precio unitario es obligatorio")
	@DecimalMin(value = "0.01", message = "El precio unitario debe ser mayor a cero")
	@Column(name = "Precio_unitario", nullable = false, precision = 12, scale = 2)
	private BigDecimal precioUnitario;

	@Column(name = "Descuento_aplicado", precision = 12, scale = 2)
	private BigDecimal descuentoAplicado; // null si no habia descuento vigente

	@ManyToOne
	@JoinColumn(name = "id_Venta", nullable = false)
	@NotNull(message = "La venta es obligatoria")
	private Venta venta;

	@ManyToOne
	@JoinColumn(name = "id_Inventario", nullable = false)
	@NotNull(message = "El inventario es obligatorio")
	private Inventario inventario;

	public DetalleVenta() {
	}

	public int getIdDetalleVenta() {
		return idDetalleVenta;
	}

	public void setIdDetalleVenta(int idDetalleVenta) {
		this.idDetalleVenta = idDetalleVenta;
	}

	public int getCantidad() {
		return cantidad;
	}

	public void setCantidad(int cantidad) {
		this.cantidad = cantidad;
	}

	public BigDecimal getPrecioUnitario() {
		return precioUnitario;
	}

	public void setPrecioUnitario(BigDecimal precioUnitario) {
		this.precioUnitario = precioUnitario;
	}

	public BigDecimal getDescuentoAplicado() {
		return descuentoAplicado;
	}

	public void setDescuentoAplicado(BigDecimal descuentoAplicado) {
		this.descuentoAplicado = descuentoAplicado;
	}

	public Venta getVenta() {
		return venta;
	}

	public void setVenta(Venta venta) {
		this.venta = venta;
	}

	public Inventario getInventario() {
		return inventario;
	}

	public void setInventario(Inventario inventario) {
		this.inventario = inventario;
	}

	// Metodo util para calcular subtotal del detalle
	public BigDecimal getSubtotal() {
		return precioUnitario.multiply(BigDecimal.valueOf(cantidad));
	}
}