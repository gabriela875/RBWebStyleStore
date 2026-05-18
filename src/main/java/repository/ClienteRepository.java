package repository;

import jakarta.persistence.*;
import model.Cliente;
import util.JpaUtil;
import java.util.List;

public class ClienteRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	public void guardar(Cliente cliente) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(cliente);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Cliente cliente) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(cliente);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Cliente buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Cliente.class, id);
		} finally {
			em.close();
		}
	}

	public Cliente buscarPorDocumento(String numDocumento) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT c FROM Cliente c WHERE c.numDocumento = :doc", Cliente.class)
					.setParameter("doc", numDocumento).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<Cliente> listarTodos() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT c FROM Cliente c ORDER BY c.apellido", Cliente.class).getResultList();
		} finally {
			em.close();
		}
	}

	public boolean tieneVentas(int idCliente) {
		EntityManager em = emf.createEntityManager();
		try {
			Long count = em.createQuery("SELECT COUNT(v) FROM Venta v WHERE v.cliente.idCliente = :id", Long.class)
					.setParameter("id", idCliente).getSingleResult();
			return count > 0;
		} finally {
			em.close();
		}
	}
}
