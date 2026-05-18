package repository;

import jakarta.persistence.*;
import model.Empleado;
import util.JpaUtil;
import java.util.List;
import jakarta.enterprise.context.ApplicationScoped;

@ApplicationScoped
public class EmpleadoRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	public void guardar(Empleado empleado) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(empleado);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Empleado empleado) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(empleado);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Empleado buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Empleado.class, id);
		} finally {
			em.close();
		}
	}

	public Empleado buscarPorUsuario(String usuario) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT e FROM Empleado e WHERE e.usuario = :usuario", Empleado.class)
					.setParameter("usuario", usuario).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public Empleado buscarPorDocumento(String numDocumento) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT e FROM Empleado e WHERE e.numDocumento = :doc", Empleado.class)
					.setParameter("doc", numDocumento).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<Empleado> listarTodos() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT e FROM Empleado e ORDER BY e.apellido", Empleado.class).getResultList();
		} finally {
			em.close();
		}
	}

	public boolean tieneVentas(int idEmpleado) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em.createQuery("SELECT COUNT(v) FROM Venta v WHERE v.empleado.idEmpleado = :id", Long.class)
					.setParameter("id", idEmpleado).getSingleResult();
			return count > 0;
		} finally {
			em.close();
		}
	}

	public boolean tieneVentasPendientes(int idEmpleado) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em
					.createQuery("SELECT COUNT(v) FROM Venta v WHERE v.empleado.idEmpleado = :id "
							+ "AND v.estado = model.Venta$EstadoVenta.pendiente", Long.class)
					.setParameter("id", idEmpleado).getSingleResult();
			return count > 0;
		} finally {
			em.close();
		}
	}
}