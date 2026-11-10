<?php
// CRUD de proveedores. Solo lo usa el Administrador.
// Traducido de ProveedorService.java / ProveedorBean.java, con las reglas del manual.

require_once __DIR__ . '/../config/conexion.php';

// Lista todos los proveedores ordenados por nombre (como el original)
function listarProveedores($pdo) {
    return $pdo->query("SELECT id_Proveedor, Nombre, Telefono, Correo
                        FROM proveedor ORDER BY Nombre")->fetchAll();
}

// Busca un proveedor por id
function buscarProveedor($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM proveedor WHERE id_Proveedor = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Registra un proveedor (nombre y correo deben ser unicos)
function registrarProveedor($pdo, $datos) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proveedor WHERE Nombre = :nombre");
    $stmt->execute([':nombre' => $datos['nombre']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un proveedor con ese nombre');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proveedor WHERE Correo = :correo");
    $stmt->execute([':correo' => $datos['correo']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un proveedor con ese correo');
    }

    $pdo->prepare("INSERT INTO proveedor (Nombre, Telefono, Correo)
                   VALUES (:nombre, :telefono, :correo)")
        ->execute([
            ':nombre'   => $datos['nombre'],
            ':telefono' => $datos['telefono'],
            ':correo'   => $datos['correo'],
        ]);
}

// Edita un proveedor (valida unicidad contra OTROS proveedores)
function editarProveedor($pdo, $datos) {
    $id = $datos['id_Proveedor'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proveedor WHERE Nombre = :nombre AND id_Proveedor <> :id");
    $stmt->execute([':nombre' => $datos['nombre'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe otro proveedor con ese nombre');
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proveedor WHERE Correo = :correo AND id_Proveedor <> :id");
    $stmt->execute([':correo' => $datos['correo'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe otro proveedor con ese correo');
    }

    $pdo->prepare("UPDATE proveedor SET Nombre = :nombre, Telefono = :telefono, Correo = :correo
                   WHERE id_Proveedor = :id")
        ->execute([
            ':nombre'   => $datos['nombre'],
            ':telefono' => $datos['telefono'],
            ':correo'   => $datos['correo'],
            ':id'       => $id,
        ]);
}

// Indica si el proveedor tiene productos asociados
function proveedorTieneProductos($pdo, $id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM producto WHERE id_Proveedor = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetchColumn() > 0;
}

// Elimina un proveedor (no se puede si tiene productos asociados)
function eliminarProveedor($pdo, $id) {
    if (proveedorTieneProductos($pdo, $id)) {
        throw new Exception('No se puede eliminar un proveedor con productos asociados');
    }
    $pdo->prepare("DELETE FROM proveedor WHERE id_Proveedor = :id")->execute([':id' => $id]);
}
