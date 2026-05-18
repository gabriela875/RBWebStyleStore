package util;

import jakarta.persistence.EntityManagerFactory;
import jakarta.persistence.Persistence;

public class JpaUtil {

	private static final String PERSISTENCE_UNIT = "StyleStorePU";
	private static EntityManagerFactory emf;

	private JpaUtil() {
	}

	public static EntityManagerFactory getEntityManagerFactory() {
		if (emf == null || !emf.isOpen()) {
			emf = Persistence.createEntityManagerFactory(PERSISTENCE_UNIT);
		}
		return emf;
	}

	public static void closeEntityManagerFactory() {
		if (emf != null && emf.isOpen()) {
			emf.close();
		}
	}
}
