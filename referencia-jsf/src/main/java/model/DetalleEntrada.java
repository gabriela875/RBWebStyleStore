package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;

@Entity
@Table(name = "detalle_entrada")
public class DetalleEntrada {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Detalle")
	private int idDetalle;

	@Min(value = 1, message = "La cantidad debe ser al menos 1")
	@Column(name = "Cantidad", nullable = false)
	private int cantidad;

	@NotNull(message = "El precio unitario es obligatorio")
	@DecimalMin(value = "0.01", message = "El precio unitario debe ser mayor a cero")
	@Column(name = "Precio_unitario", nullable = false, precision = 12, scale = 2)
	private BigDecimal precioUnitario;

	@ManyToOne
	@JoinColumn(name = "id_Entrada", nullable = false)
	@NotNull(message = "La entrada es obligatoria")
	private EntradaMercancia entrada;

	@ManyToOne
	@JoinColumn(name = "id_Proveedor", nullable = false)
	@NotNull(message = "El proveedor es obligatorio")
	private Proveedor proveedor;

	@ManyToOne
	@JoinColumn(name = "id_Inventario", nullable = false)
	@NotNull(message = "El inventario es obligatorio")
	private Inventario inventario;

	public DetalleEntrada() {
	}

	public int getIdDetalle() {
		return idDetalle;
	}

	public void setIdDetalle(int idDetalle) {
		this.idDetalle = idDetalle;
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

	public EntradaMercancia getEntrada() {
		return entrada;
	}

	public void setEntrada(EntradaMercancia entrada) {
		this.entrada = entrada;
	}

	public Proveedor getProveedor() {
		return proveedor;
	}

	public void setProveedor(Proveedor proveedor) {
		this.proveedor = proveedor;
	}

	public Inventario getInventario() {
		return inventario;
	}

	public void setInventario(Inventario inventario) {
		this.inventario = inventario;
	}
}
