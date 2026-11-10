package repository;

import jakarta.persistence.*;
import model.*;
import util.JpaUtil;
import java.util.List;

public class AbastecimientoRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	// EntradaMercancia 

	public void guardar(EntradaMercancia entrada) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(entrada);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public EntradaMercancia buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(EntradaMercancia.class, id);
		} finally {
			em.close();
		}
	}

	public List<EntradaMercancia> listarTodas() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT e FROM EntradaMercancia e ORDER BY e.fecha DESC", EntradaMercancia.class)
					.getResultList();
		} finally {
			em.close();
		}
	}

	public List<EntradaMercancia> listarPorProveedor(int idProveedor) {
		EntityManager em = emf.createEntityManager();
		try {
			return em
					.createQuery("SELECT e FROM EntradaMercancia e " + "WHERE e.proveedor.idProveedor = :id "
							+ "ORDER BY e.fecha DESC", EntradaMercancia.class)
					.setParameter("id", idProveedor).getResultList();
		} finally {
			em.close();
		}
	}

	// DetalleEntrada 

	public void guardarDetalle(DetalleEntrada detalle) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(detalle);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public List<DetalleEntrada> listarDetallesPorEntrada(int idEntrada) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT d FROM DetalleEntrada d " + "WHERE d.entrada.idEntrada = :id",
					DetalleEntrada.class).setParameter("id", idEntrada).getResultList();
		} finally {
			em.close();
		}
	}
}
