package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;

@Entity
@Table(name = "inventario")
public class Inventario {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Inventario")
	private int idInventario;

	@Min(value = 0, message = "El stock no puede ser negativo")
	@Column(name = "Stock", nullable = false)
	private int stock;

	@Min(value = 0, message = "El stock reservado no puede ser negativo")
	@Column(name = "Stock_Reservado", nullable = false)
	private int stockReservado;

	@ManyToOne
	@JoinColumn(name = "id_Producto", nullable = false)
	@NotNull(message = "El producto es obligatorio")
	private Producto producto;

	@ManyToOne
	@JoinColumn(name = "id_Talla", nullable = false)
	@NotNull(message = "La talla es obligatoria")
	private Talla talla;

	@ManyToOne
	@JoinColumn(name = "id_Color", nullable = false)
	@NotNull(message = "El color es obligatorio")
	private Color color;

	public Inventario() {
	}

	public int getIdInventario() {
		return idInventario;
	}

	public void setIdInventario(int idInventario) {
		this.idInventario = idInventario;
	}

	public int getStock() {
		return stock;
	}

	public void setStock(int stock) {
		this.stock = stock;
	}

	public int getStockReservado() {
		return stockReservado;
	}

	public void setStockReservado(int stockReservado) {
		this.stockReservado = stockReservado;
	}

	public Producto getProducto() {
		return producto;
	}

	public void setProducto(Producto producto) {
		this.producto = producto;
	}

	public Talla getTalla() {
		return talla;
	}

	public void setTalla(Talla talla) {
		this.talla = talla;
	}

	public Color getColor() {
		return color;
	}

	public void setColor(Color color) {
		this.color = color;
	}

	// Metodo util para saber el stock disponible real
	public int getStockDisponible() {
		return stock - stockReservado;
	}
}
