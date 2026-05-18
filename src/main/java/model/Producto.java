package model;

import jakarta.persistence.*;
import jakarta.validation.constraints.*;
import java.math.BigDecimal;

@Entity
@Table(name = "producto")
public class Producto {

	@Id
	@GeneratedValue(strategy = GenerationType.IDENTITY)
	@Column(name = "id_Producto")
	private int idProducto;

	@NotBlank(message = "El código es obligatorio")
	@Column(name = "Codigo", nullable = false, unique = true)
	private String codigo;

	@NotBlank(message = "El nombre es obligatorio")
	@Column(name = "Nombre", nullable = false)
	private String nombre;

	@NotBlank(message = "La marca es obligatoria")
	@Column(name = "Marca", nullable = false)
	private String marca;

	@NotNull(message = "El precio base es obligatorio")
	@DecimalMin(value = "0.01", message = "El precio debe ser mayor a cero")
	@Column(name = "Precio_base", nullable = false, precision = 12, scale = 2)
	private BigDecimal precioBase;

	@NotNull(message = "El estado es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Estado", nullable = false)
	private EstadoProducto estado;

	@NotNull(message = "El género es obligatorio")
	@Enumerated(EnumType.STRING)
	@Column(name = "Genero", nullable = false)
	private Genero genero;

	@ManyToOne
	@JoinColumn(name = "id_Categoria", nullable = false)
	@NotNull(message = "La categoría es obligatoria")
	private Categoria categoria;

	@ManyToOne
	@JoinColumn(name = "id_Proveedor", nullable = false)
	@NotNull(message = "El proveedor es obligatorio")
	private Proveedor proveedor;

	public enum EstadoProducto {
		disponible, agotado, descontinuado
	}

	public enum Genero {
		hombre, mujer, unisex, nino
	}

	public Producto() {
	}

	public int getIdProducto() {
		return idProducto;
	}

	public void setIdProducto(int idProducto) {
		this.idProducto = idProducto;
	}

	public String getCodigo() {
		return codigo;
	}

	public void setCodigo(String codigo) {
		this.codigo = codigo;
	}

	public String getNombre() {
		return nombre;
	}

	public void setNombre(String nombre) {
		this.nombre = nombre;
	}

	public String getMarca() {
		return marca;
	}

	public void setMarca(String marca) {
		this.marca = marca;
	}

	public BigDecimal getPrecioBase() {
		return precioBase;
	}

	public void setPrecioBase(BigDecimal precioBase) {
		this.precioBase = precioBase;
	}

	public EstadoProducto getEstado() {
		return estado;
	}

	public void setEstado(EstadoProducto estado) {
		this.estado = estado;
	}

	public Genero getGenero() {
		return genero;
	}

	public void setGenero(Genero genero) {
		this.genero = genero;
	}

	public Categoria getCategoria() {
		return categoria;
	}

	public void setCategoria(Categoria categoria) {
		this.categoria = categoria;
	}

	public Proveedor getProveedor() {
		return proveedor;
	}

	public void setProveedor(Proveedor proveedor) {
		this.proveedor = proveedor;
	}
}
