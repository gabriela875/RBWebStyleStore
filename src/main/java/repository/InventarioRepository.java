package repository;

import jakarta.persistence.*;
import model.AjusteInventario;
import model.Inventario;
import util.JpaUtil;
import java.util.List;

public class InventarioRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	public void guardar(Inventario inventario) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(inventario);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Inventario inventario) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(inventario);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Inventario buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Inventario.class, id);
		} finally {
			em.close();
		}
	}

	public Inventario buscarPorProductoTallaColor(int idProducto, int idTalla, int idColor) {
		EntityManager em = emf.createEntityManager();
		try {
			return em
					.createQuery("SELECT i FROM Inventario i " + "WHERE i.producto.idProducto = :idP "
							+ "AND i.talla.idTalla = :idT " + "AND i.color.idColor = :idC", Inventario.class)
					.setParameter("idP", idProducto).setParameter("idT", idTalla).setParameter("idC", idColor)
					.getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<Inventario> listarPorProducto(int idProducto) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT i FROM Inventario i WHERE i.producto.idProducto = :id", Inventario.class)
					.setParameter("id", idProducto).getResultList();
		} finally {
			em.close();
		}
	}

	public boolean todoCombinacionesAgotadas(int idProducto) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em
					.createQuery(
							"SELECT COUNT(i) FROM Inventario i "
									+ "WHERE i.producto.idProducto = :id AND (i.stock - i.stockReservado) > 0",
							Long.class)
					.setParameter("id", idProducto).getSingleResult();
			return count == 0;
		} finally {
			em.close();
		}
	}

	public void guardarAjuste(AjusteInventario ajuste) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(ajuste);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public List<AjusteInventario> listarAjustesPorInventario(int idInventario) {
		EntityManager em = emf.createEntityManager();
		try {
			return em
					.createQuery("SELECT a FROM AjusteInventario a WHERE a.inventario.idInventario = :id "
							+ "ORDER BY a.fecha DESC", AjusteInventario.class)
					.setParameter("id", idInventario).getResultList();
		} finally {
			em.close();
		}
	}
}
