package repository;

import jakarta.persistence.*;
import model.Descuento;
import model.Producto;
import util.JpaUtil;
import java.time.LocalDate;
import java.util.List;

public class ProductoRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	public void guardar(Producto producto) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(producto);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Producto producto) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(producto);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void eliminar(int idProducto) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			Producto p = em.find(Producto.class, idProducto);
			if (p != null) {
				em.remove(p);
			}
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Producto buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Producto.class, id);
		} finally {
			em.close();
		}
	}

	public Producto buscarPorCodigo(String codigo) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Producto p WHERE p.codigo = :codigo", Producto.class)
					.setParameter("codigo", codigo).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<Producto> listarTodos() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Producto p ORDER BY p.nombre", Producto.class).getResultList();
		} finally {
			em.close();
		}
	}

	public List<Producto> listarDisponibles() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Producto p WHERE p.estado = model.Producto$EstadoProducto.disponible "
					+ "ORDER BY p.nombre", Producto.class).getResultList();
		} finally {
			em.close();
		}
	}

	public boolean tieneVentas(int idProducto) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em.createQuery(
					"SELECT COUNT(d) FROM DetalleVenta d " + "WHERE d.inventario.producto.idProducto = :id", Long.class)
					.setParameter("id", idProducto).getSingleResult();
			return count > 0;
		} finally {
			em.close();
		}
	}

	// Descuentos
	public void guardarDescuento(Descuento descuento) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(descuento);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizarDescuento(Descuento descuento) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(descuento);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Descuento buscarDescuentoActivo(int idProducto) {
		EntityManager em = emf.createEntityManager();
		try {
			return em
					.createQuery(
							"SELECT d FROM Descuento d WHERE d.producto.idProducto = :id "
									+ "AND d.activo = true AND d.fechaInicio <= :hoy AND d.fechaFin >= :hoy",
							Descuento.class)
					.setParameter("id", idProducto).setParameter("hoy", LocalDate.now()).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

}