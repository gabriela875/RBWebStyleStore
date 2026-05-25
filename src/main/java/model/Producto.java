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
	@Column(name = "Precio_base", nullable = false, precision = 12, scale = 2)
	private BigDecimal precioBase;

	@ManyToOne
	@JoinColumn(name = "id_Estado_Prod", nullable = false)
	@NotNull(message = "El estado es obligatorio")
	private EstadoProducto estadoProducto;

	@ManyToOne
	@JoinColumn(name = "id_Genero", nullable = false)
	@NotNull(message = "El género es obligatorio")
	private Genero genero;

	@ManyToOne
	@JoinColumn(name = "id_Categoria", nullable = false)
	@NotNull(message = "La categoría es obligatoria")
	private Categoria categoria;

	@ManyToOne
	@JoinColumn(name = "id_Proveedor", nullable = false)
	@NotNull(message = "El proveedor es obligatorio")
	private Proveedor proveedor;

	public Producto() {
	}

	public int getIdProducto() {
		return idProducto;
	}

	public void setIdProducto(int id) {
		this.idProducto = id;
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

	public void setPrecioBase(BigDecimal precio) {
		this.precioBase = precio;
	}

	public EstadoProducto getEstadoProducto() {
		return estadoProducto;
	}

	public void setEstadoProducto(EstadoProducto estado) {
		this.estadoProducto = estado;
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