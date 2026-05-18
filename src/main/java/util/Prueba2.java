package util;

import model.*;
import model.Cliente.TipoDocumento;
import repository.*;
import service.*;
import java.math.BigDecimal;
import java.util.List;

public class Prueba2 {

	public static void main(String[] args) {

		// Instanciar repositories
		EmpleadoRepository empleadoRepo = new EmpleadoRepository();
		ClienteRepository clienteRepo = new ClienteRepository();
		ProductoRepository productoRepo = new ProductoRepository();
		InventarioRepository inventarioRepo = new InventarioRepository();
		ProveedorRepository proveedorRepo = new ProveedorRepository();

		System.out.println("========================================");
		System.out.println("     PRUEBA STYLESTORE - SERVICES       ");
		System.out.println("========================================");

		// ── Prueba 1: Login ───────────────────────────────────────────────
		System.out.println("\n--- Prueba 1: Login ---");
		Empleado admin = empleadoRepo.buscarPorUsuario("admin");
		if (admin != null && admin.getContrasena().equals("admin123")) {
			System.out.println("Login OK: " + admin.getNombre() + " | Cargo: " + admin.getCargo().getTipo());
		}

		// ── Prueba 2: Registrar proveedor ─────────────────────────────────
		System.out.println("\n--- Prueba 2: Registrar proveedor ---");
		Proveedor proveedor = new Proveedor();
		proveedor.setNombre("Textiles Colombia");
		proveedor.setTelefono("3001234567");
		proveedor.setCorreo("textiles@colombia.com");
		proveedorRepo.guardar(proveedor);

		Proveedor proveedorGuardado = proveedorRepo.listarTodos().get(0);
		System.out.println("Proveedor guardado: " + proveedorGuardado.getNombre());

		// ── Prueba 3: Registrar producto ──────────────────────────────────
		System.out.println("\n--- Prueba 3: Registrar producto ---");
		Categoria categoria = new Categoria();
		categoria.setIdCategoria(1); // Camisetas

		Producto producto = new Producto();
		producto.setCodigo("CAM-001");
		producto.setNombre("Camiseta Basica");
		producto.setMarca("StyleBrand");
		producto.setPrecioBase(new BigDecimal("49900"));
		producto.setEstado(Producto.EstadoProducto.disponible);
		producto.setGenero(Producto.Genero.unisex);
		producto.setCategoria(categoria);
		producto.setProveedor(proveedorGuardado);
		productoRepo.guardar(producto);

		Producto productoGuardado = productoRepo.buscarPorCodigo("CAM-001");
		System.out.println("Producto guardado: " + productoGuardado.getNombre() + " | Precio: "
				+ productoGuardado.getPrecioBase());

		// ── Prueba 4: Registrar inventario ────────────────────────────────
		System.out.println("\n--- Prueba 4: Registrar inventario ---");
		Talla talla = new Talla();
		talla.setIdTalla(3); // M

		Color color = new Color();
		color.setIdColor(1); // Negro

		Inventario inventario = new Inventario();
		inventario.setProducto(productoGuardado);
		inventario.setTalla(talla);
		inventario.setColor(color);
		inventario.setStock(10);
		inventario.setStockReservado(0);
		inventarioRepo.guardar(inventario);

		Inventario invGuardado = inventarioRepo.buscarPorProductoTallaColor(productoGuardado.getIdProducto(), 3, 1);
		System.out.println("Inventario guardado:" + " Stock=" + invGuardado.getStock() + " | Disponible="
				+ invGuardado.getStockDisponible());

		// ── Prueba 5: Registrar cliente ───────────────────────────────────
		System.out.println("\n--- Prueba 5: Registrar cliente ---");
		Cliente cliente = new Cliente();
		cliente.setTipoDocumento(TipoDocumento.cedula);
		cliente.setNumDocumento("987654321");
		cliente.setNombre("Maria");
		cliente.setApellido("Lopez");
		cliente.setCorreo("maria@correo.com");
		clienteRepo.guardar(cliente);

		Cliente clienteGuardado = clienteRepo.buscarPorDocumento("987654321");
		System.out.println("Cliente guardado: " + clienteGuardado.getNombre() + " " + clienteGuardado.getApellido());

		// ── Prueba 6: Consultas generales ─────────────────────────────────
		System.out.println("\n--- Prueba 6: Consultas generales ---");

		List<Empleado> empleados = empleadoRepo.listarTodos();
		System.out.println("Total empleados: " + empleados.size());

		List<Producto> productos = productoRepo.listarTodos();
		System.out.println("Total productos: " + productos.size());

		List<Cliente> clientes = clienteRepo.listarTodos();
		System.out.println("Total clientes: " + clientes.size());

		List<Proveedor> proveedores = proveedorRepo.listarTodos();
		System.out.println("Total proveedores: " + proveedores.size());

		List<Inventario> inventarios = inventarioRepo.listarPorProducto(productoGuardado.getIdProducto());
		System.out.println("Combinaciones de inventario: " + inventarios.size());

		System.out.println("\n========================================");
		System.out.println("     PRUEBA FINALIZADA                  ");
		System.out.println("========================================");

		JpaUtil.closeEntityManagerFactory();
	}
}