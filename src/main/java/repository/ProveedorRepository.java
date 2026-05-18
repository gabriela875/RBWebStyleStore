package repository;

import jakarta.persistence.*;
import model.Proveedor;
import util.JpaUtil;
import java.util.List;

public class ProveedorRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	public void guardar(Proveedor proveedor) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(proveedor);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Proveedor proveedor) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(proveedor);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	// NUEVO: eliminar proveedor (dar de baja) — solo si no tiene productos
	public void eliminar(int idProveedor) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			Proveedor p = em.find(Proveedor.class, idProveedor);
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

	public Proveedor buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Proveedor.class, id);
		} finally {
			em.close();
		}
	}

	// NUEVO: buscar por correo para validar duplicados
	public Proveedor buscarPorCorreo(String correo) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Proveedor p WHERE LOWER(p.correo) = LOWER(:correo)", Proveedor.class)
					.setParameter("correo", correo).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	// NUEVO: buscar por nombre para validar duplicados
	public Proveedor buscarPorNombre(String nombre) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Proveedor p WHERE LOWER(p.nombre) = LOWER(:nombre)", Proveedor.class)
					.setParameter("nombre", nombre).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<Proveedor> listarTodos() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Proveedor p ORDER BY p.nombre", Proveedor.class).getResultList();
		} finally {
			em.close();
		}
	}

	public boolean tieneProductos(int idProveedor) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em
					.createQuery("SELECT COUNT(p) FROM Producto p WHERE p.proveedor.idProveedor = :id", Long.class)
					.setParameter("id", idProveedor).getSingleResult();
			return count > 0;
		} finally {
			em.close();
		}
	}
}