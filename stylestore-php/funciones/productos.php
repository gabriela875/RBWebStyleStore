<?php
// CRUD de productos + precio + estados + descuentos.
// Traducido de ProductoService.java / ProductoRepository.java, con las reglas del manual.

require_once __DIR__ . '/../config/conexion.php';

// Lista todos los productos con sus nombres de catalogo y su estado (ordenados por nombre)
function listarProductos($pdo) {
    $sql = "SELECT p.id_Producto, p.Codigo, p.Nombre, p.Marca, p.Precio_base,
                   ep.Nombre AS Estado, g.Nombre AS Genero, c.Nombre AS Categoria,
                   pr.Nombre AS Proveedor, p.id_Genero, p.id_Categoria, p.id_Proveedor
            FROM producto p
            JOIN estado_producto ep ON ep.id_Estado_Prod = p.id_Estado_Prod
            JOIN genero g           ON g.id_Genero = p.id_Genero
            JOIN categoria c        ON c.id_Categoria = p.id_Categoria
            JOIN proveedor pr       ON pr.id_Proveedor = p.id_Proveedor
            ORDER BY p.Nombre";
    return $pdo->query($sql)->fetchAll();
}

function buscarProducto($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE id_Producto = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

// Catalogos para los selectores del formulario
function catalogosProducto($pdo) {
    return [
        'categorias' => $pdo->query("SELECT id_Categoria, Nombre FROM categoria ORDER BY Nombre")->fetchAll(),
        'generos'    => $pdo->query("SELECT id_Genero, Nombre FROM genero ORDER BY Nombre")->fetchAll(),
        'proveedores'=> $pdo->query("SELECT id_Proveedor, Nombre FROM proveedor ORDER BY Nombre")->fetchAll(),
        'tiposDesc'  => $pdo->query("SELECT id_Tipo_Desc, Nombre FROM tipo_descuento ORDER BY id_Tipo_Desc")->fetchAll(),
    ];
}

function buscarEstadoProductoId($pdo, $nombre) {
    $stmt = $pdo->prepare("SELECT id_Estado_Prod FROM estado_producto WHERE Nombre = :n");
    $stmt->execute([':n' => $nombre]);
    return (int)$stmt->fetchColumn();
}

function nombreEstadoProducto($pdo, $idProducto) {
    $stmt = $pdo->prepare("SELECT ep.Nombre FROM producto p
                           JOIN estado_producto ep ON ep.id_Estado_Prod = p.id_Estado_Prod
                           WHERE p.id_Producto = :id");
    $stmt->execute([':id' => $idProducto]);
    return $stmt->fetchColumn();
}

// Registra un producto (codigo unico, precio > 0; se crea Disponible)
function registrarProducto($pdo, $datos) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM producto WHERE Codigo = :codigo");
    $stmt->execute([':codigo' => $datos['codigo']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un producto con ese código');
    }
    if ((float)$datos['precio'] <= 0) {
        throw new Exception('El precio debe ser mayor a cero');
    }

    $pdo->prepare("INSERT INTO producto
                     (Codigo, Nombre, Marca, Precio_base, id_Estado_Prod, id_Genero, id_Categoria, id_Proveedor)
                   VALUES (:codigo, :nombre, :marca, :precio,
                     (SELECT id_Estado_Prod FROM estado_producto WHERE Nombre = 'Disponible'),
                     :genero, :categoria, :proveedor)")
        ->execute([
            ':codigo'    => $datos['codigo'],
            ':nombre'    => $datos['nombre'],
            ':marca'     => $datos['marca'],
            ':precio'    => $datos['precio'],
            ':genero'    => $datos['id_Genero'],
            ':categoria' => $datos['id_Categoria'],
            ':proveedor' => $datos['id_Proveedor'],
        ]);
}

// Edita un producto: nombre, marca, genero, categoria, proveedor.
// El estado y el precio NO se cambian aqui (igual que el original).
function editarProducto($pdo, $datos) {
    $id = $datos['id_Producto'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM producto WHERE Codigo = :codigo AND id_Producto <> :id");
    $stmt->execute([':codigo' => $datos['codigo'], ':id' => $id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe otro producto con ese código');
    }

    $pdo->prepare("UPDATE producto SET Codigo = :codigo, Nombre = :nombre, Marca = :marca,
                     id_Genero = :genero, id_Categoria = :categoria, id_Proveedor = :proveedor
                   WHERE id_Producto = :id")
        ->execute([
            ':codigo'    => $datos['codigo'],
            ':nombre'    => $datos['nombre'],
            ':marca'     => $datos['marca'],
            ':genero'    => $datos['id_Genero'],
            ':categoria' => $datos['id_Categoria'],
            ':proveedor' => $datos['id_Proveedor'],
            ':id'        => $id,
        ]);
}

// Actualiza solo el precio base (debe ser > 0)
function actualizarPrecioProducto($pdo, $idProducto, $nuevoPrecio) {
    if ((float)$nuevoPrecio <= 0) {
        throw new Exception('El precio debe ser mayor a cero');
    }
    $pdo->prepare("UPDATE producto SET Precio_base = :precio WHERE id_Producto = :id")
        ->execute([':precio' => $nuevoPrecio, ':id' => $idProducto]);
}

// Marca un producto como agotado manualmente
function marcarAgotado($pdo, $idProducto) {
    $estado = nombreEstadoProducto($pdo, $idProducto);
    if ($estado === 'Descontinuado') {
        throw new Exception('El producto está descontinuado y no puede modificarse');
    }
    if ($estado === 'Agotado') {
        throw new Exception('El producto ya está marcado como agotado');
    }
    $pdo->prepare("UPDATE producto SET id_Estado_Prod = :e WHERE id_Producto = :id")
        ->execute([':e' => buscarEstadoProductoId($pdo, 'Agotado'), ':id' => $idProducto]);
}

// Descontinua un producto (permanente) y desactiva su descuento activo si lo tiene
function descontinuarProducto($pdo, $idProducto) {
    $estado = nombreEstadoProducto($pdo, $idProducto);
    if ($estado === 'Descontinuado') {
        throw new Exception('El producto ya está descontinuado');
    }
    $pdo->beginTransaction();
    try {
        // Desactivar cualquier descuento activo del producto
        $pdo->prepare("UPDATE descuento SET id_Estado_Desc =
                         (SELECT id_Estado_Desc FROM estado_descuento WHERE Nombre = 'Inactivo')
                       WHERE id_Producto = :id AND id_Estado_Desc =
                         (SELECT id_Estado_Desc FROM estado_descuento WHERE Nombre = 'Activo')")
            ->execute([':id' => $idProducto]);
        $pdo->prepare("UPDATE producto SET id_Estado_Prod = :e WHERE id_Producto = :id")
            ->execute([':e' => buscarEstadoProductoId($pdo, 'Descontinuado'), ':id' => $idProducto]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Indica si el producto tiene ventas registradas
function productoTieneVentas($pdo, $idProducto) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM detalle_venta dv
                           JOIN inventario i ON i.id_Inventario = dv.id_Inventario
                           WHERE i.id_Producto = :id");
    $stmt->execute([':id' => $idProducto]);
    return $stmt->fetchColumn() > 0;
}

// Elimina un producto: solo si esta Descontinuado y no tiene ventas
function eliminarProducto($pdo, $idProducto) {
    $estado = nombreEstadoProducto($pdo, $idProducto);
    if ($estado !== 'Descontinuado') {
        throw new Exception('Solo se pueden eliminar productos descontinuados');
    }
    if (productoTieneVentas($pdo, $idProducto)) {
        throw new Exception('No se puede eliminar un producto que tiene ventas registradas');
    }
    $pdo->beginTransaction();
    try {
        // Quitar dependencias que no son ventas: descuentos e inventario sin ventas
        $pdo->prepare("DELETE FROM descuento WHERE id_Producto = :id")->execute([':id' => $idProducto]);
        $pdo->prepare("DELETE FROM inventario WHERE id_Producto = :id")->execute([':id' => $idProducto]);
        $pdo->prepare("DELETE FROM producto WHERE id_Producto = :id")->execute([':id' => $idProducto]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Busca el descuento activo y vigente de un producto (estado Activo y fecha de hoy en el rango)
function buscarDescuentoActivo($pdo, $idProducto) {
    $stmt = $pdo->prepare("SELECT d.*, td.Nombre AS TipoDescuento
                           FROM descuento d
                           JOIN tipo_descuento td ON td.id_Tipo_Desc = d.id_Tipo_Desc
                           JOIN estado_descuento ed ON ed.id_Estado_Desc = d.id_Estado_Desc
                           WHERE d.id_Producto = :id AND ed.Nombre = 'Activo'
                             AND d.Fecha_inicio <= CURDATE() AND d.Fecha_fin >= CURDATE()
                           LIMIT 1");
    $stmt->execute([':id' => $idProducto]);
    return $stmt->fetch() ?: null;
}

// Registra un descuento para un producto, con todas las reglas del original
function registrarDescuento($pdo, $datos) {
    $idProducto = (int)$datos['id_Producto'];
    $producto = buscarProducto($pdo, $idProducto);
    if (!$producto) {
        throw new Exception('Producto no encontrado');
    }
    if (nombreEstadoProducto($pdo, $idProducto) === 'Descontinuado') {
        throw new Exception('No se puede agregar un descuento a un producto descontinuado');
    }
    if (buscarDescuentoActivo($pdo, $idProducto) !== null) {
        throw new Exception('El producto ya tiene un descuento activo');
    }
    if (!fechaValidaStr($datos['fecha_inicio']) || !fechaValidaStr($datos['fecha_fin'])) {
        throw new Exception('Las fechas no son válidas');
    }
    if ($datos['fecha_fin'] < $datos['fecha_inicio']) {
        throw new Exception('La fecha de fin no puede ser anterior a la fecha de inicio');
    }

    // Validar el valor segun el tipo (Porcentaje < 100; Valor Fijo < precio base)
    $valor = (float)$datos['valor'];
    $tipoNombre = tipoDescuentoNombre($pdo, (int)$datos['id_Tipo_Desc']);
    if ($tipoNombre === 'Porcentaje') {
        if ($valor >= 100) {
            throw new Exception('El porcentaje de descuento debe ser menor a 100');
        }
    } else {
        if ($valor >= (float)$producto['Precio_base']) {
            throw new Exception('El valor del descuento no puede igualar ni superar el precio base');
        }
    }

    $pdo->prepare("INSERT INTO descuento
                     (id_Tipo_Desc, Valor, Fecha_inicio, Fecha_fin, id_Producto, id_Estado_Desc)
                   VALUES (:tipo, :valor, :ini, :fin, :producto,
                     (SELECT id_Estado_Desc FROM estado_descuento WHERE Nombre = 'Activo'))")
        ->execute([
            ':tipo'     => $datos['id_Tipo_Desc'],
            ':valor'    => $valor,
            ':ini'      => $datos['fecha_inicio'],
            ':fin'      => $datos['fecha_fin'],
            ':producto' => $idProducto,
        ]);
}

function tipoDescuentoNombre($pdo, $idTipo) {
    $stmt = $pdo->prepare("SELECT Nombre FROM tipo_descuento WHERE id_Tipo_Desc = :id");
    $stmt->execute([':id' => $idTipo]);
    return $stmt->fetchColumn();
}

// Validacion de fecha simple AAAA-MM-DD (para no depender de includes externos aqui)
function fechaValidaStr($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}
