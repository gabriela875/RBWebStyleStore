<?php
// Clientes. El Administrador consulta y edita; el registro ocurre dentro de una venta.
// Traducido de ClienteService.java / ClienteRepository.java, con las reglas del manual.

require_once __DIR__ . '/../config/conexion.php';

// Lista todos los clientes con el nombre de su tipo de documento (ordenados por apellido)
function listarClientes($pdo) {
    $sql = "SELECT c.id_Cliente, c.Num_Documento, c.Nombre, c.Apellido, c.Correo,
                   c.id_Tipo_Doc, td.Nombre AS TipoDocumento
            FROM clientes c
            JOIN tipo_documento td ON td.id_Tipo_Doc = c.id_Tipo_Doc
            ORDER BY c.Apellido";
    return $pdo->query($sql)->fetchAll();
}

function buscarCliente($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_Cliente = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Busca un cliente por documento (se usa al vincularlo a una venta)
function buscarClientePorDocumento($pdo, $documento) {
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE Num_Documento = :doc");
    $stmt->execute([':doc' => $documento]);
    return $stmt->fetch() ?: null;
}

function listarTiposDocumento($pdo) {
    return $pdo->query("SELECT id_Tipo_Doc, Nombre FROM tipo_documento ORDER BY id_Tipo_Doc")->fetchAll();
}

// Registra un cliente (documento unico). Devuelve el id del nuevo cliente.
function registrarCliente($pdo, $datos) {
    if (buscarClientePorDocumento($pdo, $datos['documento']) !== null) {
        throw new Exception('Ya existe un cliente con ese documento');
    }
    $pdo->prepare("INSERT INTO clientes (id_Tipo_Doc, Num_Documento, Nombre, Apellido, Correo)
                   VALUES (:tipo, :doc, :nombre, :apellido, :correo)")
        ->execute([
            ':tipo'     => $datos['id_Tipo_Doc'],
            ':doc'      => $datos['documento'],
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':correo'   => $datos['correo'] !== '' ? $datos['correo'] : null,
        ]);
    return (int)$pdo->lastInsertId();
}

// Edita un cliente. El documento NO se puede cambiar (regla del manual): no se toca esa columna.
function editarCliente($pdo, $datos) {
    $pdo->prepare("UPDATE clientes SET id_Tipo_Doc = :tipo, Nombre = :nombre,
                     Apellido = :apellido, Correo = :correo
                   WHERE id_Cliente = :id")
        ->execute([
            ':tipo'     => $datos['id_Tipo_Doc'],
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':correo'   => $datos['correo'] !== '' ? $datos['correo'] : null,
            ':id'       => $datos['id_Cliente'],
        ]);
}
