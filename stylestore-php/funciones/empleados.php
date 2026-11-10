<?php
// CRUD de empleados (usuarios del sistema). Solo lo usa el Administrador.
// Traducido de EmpleadoService.java / EmpleadoBean.java, con las reglas del manual.

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/auth.php'; // para desbloquearCuenta()

// Lista todos los empleados con su cargo y estados (ordenados por apellido, como el original)
function listarEmpleados($pdo) {
    $sql = "SELECT e.id_Empleado, e.Num_Documento, e.Nombre, e.Apellido, e.Correo, e.Usuario,
                   e.Intentos_fallidos, c.Tipo AS Cargo,
                   ee.Nombre AS EstadoEmpleado, eb.Nombre AS EstadoBloqueo
            FROM empleados e
            JOIN cargo c            ON c.id_Cargo = e.id_Cargo
            JOIN estado_empleado ee ON ee.id_Estado_Emp = e.id_Estado_Emp
            JOIN estado_bloqueo eb  ON eb.id_Estado_Bloq = e.id_Estado_Bloq
            ORDER BY e.Apellido";
    return $pdo->query($sql)->fetchAll();
}

// Busca un empleado por id (para cargar el formulario de edicion)
function buscarEmpleado($pdo, $id) {
    $stmt = $pdo->prepare("SELECT e.*, c.Tipo AS Cargo FROM empleados e
                           JOIN cargo c ON c.id_Cargo = e.id_Cargo
                           WHERE e.id_Empleado = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Lista de cargos para el formulario
function listarCargos($pdo) {
    return $pdo->query("SELECT id_Cargo, Tipo FROM cargo ORDER BY id_Cargo")->fetchAll();
}

// Registra un empleado (lo crea Activo y Desbloqueado, como el original)
function registrarEmpleado($pdo, $datos) {
    // Documento unico
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Num_Documento = :doc");
    $stmt->execute([':doc' => $datos['documento']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un empleado con ese documento');
    }
    // Usuario unico
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Usuario = :usuario");
    $stmt->execute([':usuario' => $datos['usuario']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El usuario ya está en uso');
    }
    // Correo unico
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Correo = :correo");
    $stmt->execute([':correo' => $datos['correo']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un empleado con ese correo');
    }

    $sql = "INSERT INTO empleados
                (Num_Documento, Nombre, Apellido, Correo, Usuario, Contrasena,
                 Intentos_fallidos, id_Cargo, id_Estado_Emp, id_Estado_Bloq)
            VALUES
                (:doc, :nombre, :apellido, :correo, :usuario, :hash, 0, :cargo,
                 (SELECT id_Estado_Emp FROM estado_empleado WHERE Nombre = 'Activo'),
                 (SELECT id_Estado_Bloq FROM estado_bloqueo WHERE Nombre = 'Desbloqueado'))";
    $pdo->prepare($sql)->execute([
        ':doc'      => $datos['documento'],
        ':nombre'   => $datos['nombre'],
        ':apellido' => $datos['apellido'],
        ':correo'   => $datos['correo'],
        ':usuario'  => $datos['usuario'],
        ':hash'     => password_hash($datos['contrasena'], PASSWORD_DEFAULT),
        ':cargo'    => $datos['id_Cargo'],
    ]);
}

// Edita los datos de un empleado (sin cambiar la contrasena)
function editarEmpleado($pdo, $datos) {
    $id = $datos['id_Empleado'];
    // Validar duplicados contra OTROS empleados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Num_Documento = :doc AND id_Empleado <> :id");
    $stmt->execute([':doc' => $datos['documento'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe otro empleado con ese documento');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Usuario = :usuario AND id_Empleado <> :id");
    $stmt->execute([':usuario' => $datos['usuario'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El usuario ya está en uso');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Correo = :correo AND id_Empleado <> :id");
    $stmt->execute([':correo' => $datos['correo'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe otro empleado con ese correo');
    }

    $pdo->prepare("UPDATE empleados SET Num_Documento = :doc, Nombre = :nombre, Apellido = :apellido,
                     Correo = :correo, Usuario = :usuario, id_Cargo = :cargo
                   WHERE id_Empleado = :id")
        ->execute([
            ':doc'      => $datos['documento'],
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':correo'   => $datos['correo'],
            ':usuario'  => $datos['usuario'],
            ':cargo'    => $datos['id_Cargo'],
            ':id'       => $id,
        ]);
}

// Cuenta ventas pendientes del empleado (regla del original para desactivar)
function empleadoTieneVentasPendientes($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM venta v
                           JOIN estado_venta ev ON ev.id_Estado_Venta = v.id_Estado_Venta
                           WHERE v.id_Empleado = :id AND ev.Nombre = 'Pendiente'");
    $stmt->execute([':id' => $id]);
    return $stmt->fetchColumn() > 0;
}

// Desactiva un empleado (no se puede si tiene ventas pendientes)
function desactivarEmpleado($pdo, $id) {
    if (empleadoTieneVentasPendientes($pdo, $id)) {
        throw new Exception('El empleado tiene ventas pendientes. Anúlelas antes de desactivarlo');
    }
    $pdo->prepare("UPDATE empleados SET id_Estado_Emp =
                     (SELECT id_Estado_Emp FROM estado_empleado WHERE Nombre = 'Inactivo')
                   WHERE id_Empleado = :id")
        ->execute([':id' => $id]);
}

// Activa un empleado
function activarEmpleado($pdo, $id) {
    $pdo->prepare("UPDATE empleados SET id_Estado_Emp =
                     (SELECT id_Estado_Emp FROM estado_empleado WHERE Nombre = 'Activo')
                   WHERE id_Empleado = :id")
        ->execute([':id' => $id]);
}

// Indica si el empleado tiene movimientos (ventas, entradas o ajustes)
function empleadoTieneMovimientos($pdo, $id) {
    $stmt = $pdo->prepare("SELECT
        (SELECT COUNT(*) FROM venta WHERE id_Empleado = :id1) +
        (SELECT COUNT(*) FROM entrada_mercancia WHERE id_Empleado = :id2) +
        (SELECT COUNT(*) FROM ajuste_inventario WHERE id_Empleado = :id3) AS total");
    $stmt->execute([':id1' => $id, ':id2' => $id, ':id3' => $id]);
    return (int)$stmt->fetchColumn() > 0;
}

// Elimina un empleado.
// Borrado REAL solo si no tiene ventas, entradas ni ajustes; si los tiene, solo se desactiva.
// Devuelve 'eliminado' o 'desactivado' para avisar al usuario.
function eliminarEmpleado($pdo, $id) {
    if (empleadoTieneMovimientos($pdo, $id)) {
        desactivarEmpleado($pdo, $id);
        return 'desactivado';
    }
    // Borrar tokens de recuperacion asociados para no romper la FK
    $pdo->prepare("DELETE FROM password_reset WHERE id_Empleado = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM empleados WHERE id_Empleado = :id")->execute([':id' => $id]);
    return 'eliminado';
}
