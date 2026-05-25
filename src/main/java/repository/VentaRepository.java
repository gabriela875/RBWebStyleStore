package repository;

import jakarta.persistence.*;
import model.*;
import util.JpaUtil;
import java.time.LocalDateTime;
import java.util.List;

public class VentaRepository {

	private EntityManagerFactory emf = JpaUtil.getEntityManagerFactory();

	// Venta

	public void guardar(Venta venta) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			// Reattach entidades relacionadas
			if (venta.getCliente() != null) {
				venta.setCliente(em.merge(venta.getCliente()));
			}
			if (venta.getEmpleado() != null) {
				venta.setEmpleado(em.merge(venta.getEmpleado()));
			}
			em.persist(venta);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void actualizar(Venta venta) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.merge(venta);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public Venta buscarPorId(int id) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.find(Venta.class, id);
		} finally {
			em.close();
		}
	}

	public List<Venta> listarTodas() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT v FROM Venta v ORDER BY v.fechaHora DESC", Venta.class).getResultList();
		} finally {
			em.close();
		}
	}

	public List<Venta> listarPorEmpleado(int idEmpleado) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery(
					"SELECT v FROM Venta v WHERE v.empleado.idEmpleado = :id " + "ORDER BY v.fechaHora DESC",
					Venta.class).setParameter("id", idEmpleado).getResultList();
		} finally {
			em.close();
		}
	}

	public List<Venta> listarPorCliente(int idCliente) {
		EntityManager em = emf.createEntityManager();
		try {
			return em
					.createQuery("SELECT v FROM Venta v WHERE v.cliente.idCliente = :id " + "ORDER BY v.fechaHora DESC",
							Venta.class)
					.setParameter("id", idCliente).getResultList();
		} finally {
			em.close();
		}
	}

	public List<Venta> listarPendientes() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery(
					"SELECT v FROM Venta v WHERE v.estadoVenta.nombre = :estado " + "ORDER BY v.fechaHora ASC",
					Venta.class).setParameter("estado", "Pendiente").getResultList();
		} finally {
			em.close();
		}
	}

	public List<Venta> listarPendientesVencidas() {
		EntityManager em = emf.createEntityManager();
		try {
			LocalDateTime limite = LocalDateTime.now().minusHours(24);
			return em
					.createQuery("SELECT v FROM Venta v WHERE v.estadoVenta.nombre = :estado "
							+ "AND v.fechaHora <= :limite ORDER BY v.fechaHora ASC", Venta.class)
					.setParameter("estado", "Pendiente").setParameter("limite", limite).getResultList();
		} finally {
			em.close();
		}
	}

	// Entidad DetalleVenta

	public void guardarDetalle(DetalleVenta detalle) {
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

	public void eliminarDetalle(DetalleVenta detalle) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			DetalleVenta managed = em.merge(detalle);
			em.remove(managed);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public List<DetalleVenta> listarDetallesPorVenta(int idVenta) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT d FROM DetalleVenta d WHERE d.venta.idVenta = :id", DetalleVenta.class)
					.setParameter("id", idVenta).getResultList();
		} finally {
			em.close();
		}
	}

	public long contarDetallesPorVenta(int idVenta) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT COUNT(d) FROM DetalleVenta d WHERE d.venta.idVenta = :id", Long.class)
					.setParameter("id", idVenta).getSingleResult();
		} finally {
			em.close();
		}
	}

	// Entidad Pago

	public void guardarPago(Pago pago) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(pago);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public void eliminarPagosPorVenta(int idVenta) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.createQuery("DELETE FROM Pago p WHERE p.venta.idVenta = :id").setParameter("id", idVenta)
					.executeUpdate();
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public List<Pago> listarPagosPorVenta(int idVenta) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT p FROM Pago p WHERE p.venta.idVenta = :id", Pago.class)
					.setParameter("id", idVenta).getResultList();
		} finally {
			em.close();
		}
	}

	// Entidad AnulacionVenta

	public void guardarAnulacion(AnulacionVenta anulacion) {
		EntityManager em = emf.createEntityManager();
		try {
			em.getTransaction().begin();
			em.persist(anulacion);
			em.getTransaction().commit();
		} catch (Exception e) {
			em.getTransaction().rollback();
			throw e;
		} finally {
			em.close();
		}
	}

	public AnulacionVenta buscarAnulacionPorVenta(int idVenta) {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT a FROM AnulacionVenta a WHERE a.venta.idVenta = :id", AnulacionVenta.class)
					.setParameter("id", idVenta).getSingleResult();
		} catch (NoResultException e) {
			return null;
		} finally {
			em.close();
		}
	}

	public List<AnulacionVenta> listarTodasAnulaciones() {
		EntityManager em = emf.createEntityManager();
		try {
			return em.createQuery("SELECT a FROM AnulacionVenta a ORDER BY a.fechaHora DESC", AnulacionVenta.class)
					.getResultList();
		} finally {
			em.close();
		}
	}
}
