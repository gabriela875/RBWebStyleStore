-- =============================================================
--  StyleStore - Base de datos (version PHP/MySQL)
--  Basado en el script original docs/bd_stylestore.sql.
--  Las correcciones aplicadas estan documentadas en CAMBIOS_BD.md
-- =============================================================

CREATE DATABASE IF NOT EXISTS bd_stylestore;
USE bd_stylestore;

-- ---------- Tablas de catalogo ----------

CREATE TABLE cargo (
    id_Cargo INT PRIMARY KEY AUTO_INCREMENT,
    Tipo VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE categoria (
    id_Categoria INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE talla (
    id_Talla INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(10) NOT NULL UNIQUE
);

CREATE TABLE color (
    id_Color INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE tipo_documento (
    id_Tipo_Doc INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE genero (
    id_Genero INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE tipo_descuento (
    id_Tipo_Desc INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE metodo_pago (
    id_Metodo_Pago INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE estado_producto (
    id_Estado_Prod INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE estado_venta (
    id_Estado_Venta INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE estado_empleado (
    id_Estado_Emp INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE estado_descuento (
    id_Estado_Desc INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(30) NOT NULL UNIQUE
);

-- CAMBIO 2: Nombre pasa a VARCHAR(20) para poder guardar 'Desbloqueado'
CREATE TABLE estado_bloqueo (
    id_Estado_Bloq INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(20) NOT NULL UNIQUE
);

-- ---------- Tablas principales ----------

CREATE TABLE proveedor (
    id_Proveedor INT PRIMARY KEY AUTO_INCREMENT,
    Nombre VARCHAR(100) NOT NULL UNIQUE,
    Telefono VARCHAR(20) NOT NULL,
    Correo VARCHAR(100) NOT NULL UNIQUE
);

-- CAMBIO 4: se agrega la columna Correo (unica) para recuperar la contrasena
CREATE TABLE empleados (
    id_Empleado INT PRIMARY KEY AUTO_INCREMENT,
    Num_Documento VARCHAR(20) NOT NULL UNIQUE,
    Nombre VARCHAR(100) NOT NULL,
    Apellido VARCHAR(100) NOT NULL,
    Correo VARCHAR(100) NOT NULL UNIQUE,
    Usuario VARCHAR(50) NOT NULL UNIQUE,
    Contrasena VARCHAR(255) NOT NULL,
    Intentos_fallidos INT NOT NULL,
    id_Cargo INT NOT NULL,
    id_Estado_Emp INT NOT NULL,
    id_Estado_Bloq INT NOT NULL,
    FOREIGN KEY (id_Cargo) REFERENCES cargo(id_Cargo),
    FOREIGN KEY (id_Estado_Emp) REFERENCES estado_empleado(id_Estado_Emp),
    FOREIGN KEY (id_Estado_Bloq) REFERENCES estado_bloqueo(id_Estado_Bloq)
);

-- CAMBIO 5: tabla para recuperacion de contrasena
CREATE TABLE password_reset (
    id_Reset INT PRIMARY KEY AUTO_INCREMENT,
    id_Empleado INT NOT NULL,
    Token_hash VARCHAR(255) NOT NULL,
    Expira DATETIME NOT NULL,
    Usado TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado)
);

CREATE TABLE clientes (
    id_Cliente INT PRIMARY KEY AUTO_INCREMENT,
    id_Tipo_Doc INT NOT NULL,
    Num_Documento VARCHAR(20) NOT NULL UNIQUE,
    Nombre VARCHAR(100) NOT NULL,
    Apellido VARCHAR(100) NOT NULL,
    Correo VARCHAR(100),
    FOREIGN KEY (id_Tipo_Doc) REFERENCES tipo_documento(id_Tipo_Doc)
);

CREATE TABLE producto (
    id_Producto INT PRIMARY KEY AUTO_INCREMENT,
    Codigo VARCHAR(50) NOT NULL UNIQUE,
    Nombre VARCHAR(100) NOT NULL,
    Marca VARCHAR(100) NOT NULL,
    Precio_base DECIMAL(12,2) NOT NULL,
    id_Estado_Prod INT NOT NULL,
    id_Genero INT NOT NULL,
    id_Categoria INT NOT NULL,
    id_Proveedor INT NOT NULL,
    FOREIGN KEY (id_Estado_Prod) REFERENCES estado_producto(id_Estado_Prod),
    FOREIGN KEY (id_Genero) REFERENCES genero(id_Genero),
    FOREIGN KEY (id_Categoria) REFERENCES categoria(id_Categoria),
    FOREIGN KEY (id_Proveedor) REFERENCES proveedor(id_Proveedor)
);

CREATE TABLE descuento (
    id_Descuento INT PRIMARY KEY AUTO_INCREMENT,
    id_Tipo_Desc INT NOT NULL,
    Valor DECIMAL(12,2) NOT NULL,
    Fecha_inicio DATE NOT NULL,
    Fecha_fin DATE NOT NULL,
    id_Producto INT NOT NULL,
    id_Estado_Desc INT NOT NULL,
    FOREIGN KEY (id_Tipo_Desc) REFERENCES tipo_descuento(id_Tipo_Desc),
    FOREIGN KEY (id_Producto) REFERENCES producto(id_Producto),
    FOREIGN KEY (id_Estado_Desc) REFERENCES estado_descuento(id_Estado_Desc)
);

CREATE TABLE inventario (
    id_Inventario INT PRIMARY KEY AUTO_INCREMENT,
    Stock INT NOT NULL,
    Stock_Reservado INT NOT NULL,
    id_Producto INT NOT NULL,
    id_Talla INT NOT NULL,
    id_Color INT NOT NULL,
    FOREIGN KEY (id_Producto) REFERENCES producto(id_Producto),
    FOREIGN KEY (id_Talla) REFERENCES talla(id_Talla),
    FOREIGN KEY (id_Color) REFERENCES color(id_Color)
);

CREATE TABLE venta (
    id_Venta INT PRIMARY KEY AUTO_INCREMENT,
    Fecha_hora DATETIME NOT NULL,
    Total DECIMAL(12,2) NOT NULL,
    id_Estado_Venta INT NOT NULL,
    id_Cliente INT NOT NULL,
    id_Empleado INT NOT NULL,
    FOREIGN KEY (id_Estado_Venta) REFERENCES estado_venta(id_Estado_Venta),
    FOREIGN KEY (id_Cliente) REFERENCES clientes(id_Cliente),
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado)
);

CREATE TABLE detalle_venta (
    id_Detalle_Venta INT PRIMARY KEY AUTO_INCREMENT,
    Cantidad INT NOT NULL,
    Precio_unitario DECIMAL(12,2) NOT NULL,
    Descuento_aplicado DECIMAL(12,2),
    id_Venta INT NOT NULL,
    id_Inventario INT NOT NULL,
    FOREIGN KEY (id_Venta) REFERENCES venta(id_Venta),
    FOREIGN KEY (id_Inventario) REFERENCES inventario(id_Inventario)
);

CREATE TABLE pago (
    id_Pago INT PRIMARY KEY AUTO_INCREMENT,
    Valor DECIMAL(12,2) NOT NULL,
    id_Metodo_Pago INT NOT NULL,
    Referencia VARCHAR(100),
    Cambio DECIMAL(12,2),
    id_Venta INT NOT NULL,
    FOREIGN KEY (id_Metodo_Pago) REFERENCES metodo_pago(id_Metodo_Pago),
    FOREIGN KEY (id_Venta) REFERENCES venta(id_Venta)
);

CREATE TABLE anulacion_venta (
    id_Anulacion INT PRIMARY KEY AUTO_INCREMENT,
    Motivo TEXT NOT NULL,
    Fecha_hora DATETIME NOT NULL,
    id_Venta INT NOT NULL,
    id_Empleado INT NOT NULL,
    FOREIGN KEY (id_Venta) REFERENCES venta(id_Venta),
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado)
);

CREATE TABLE entrada_mercancia (
    id_Entrada INT PRIMARY KEY AUTO_INCREMENT,
    Fecha DATETIME NOT NULL,
    id_Empleado INT NOT NULL,
    id_Proveedor INT NOT NULL,
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado),
    FOREIGN KEY (id_Proveedor) REFERENCES proveedor(id_Proveedor)
);

-- CAMBIO 3: se quito id_Proveedor (era redundante con entrada_mercancia y rompia la 3FN)
CREATE TABLE detalle_entrada (
    id_Detalle INT PRIMARY KEY AUTO_INCREMENT,
    Cantidad INT NOT NULL,
    Precio_unitario DECIMAL(12,2) NOT NULL,
    id_Entrada INT NOT NULL,
    id_Inventario INT NOT NULL,
    FOREIGN KEY (id_Entrada) REFERENCES entrada_mercancia(id_Entrada),
    FOREIGN KEY (id_Inventario) REFERENCES inventario(id_Inventario)
);

CREATE TABLE ajuste_inventario (
    id_Ajuste INT PRIMARY KEY AUTO_INCREMENT,
    Motivo TEXT NOT NULL,
    Cantidad_ajustada INT NOT NULL,
    Fecha DATETIME NOT NULL,
    id_Inventario INT NOT NULL,
    id_Empleado INT NOT NULL,
    FOREIGN KEY (id_Inventario) REFERENCES inventario(id_Inventario),
    FOREIGN KEY (id_Empleado) REFERENCES empleados(id_Empleado)
);

-- ---------- Datos base (catalogos) ----------

INSERT INTO cargo VALUES
(1,'Administrador'),(2,'Vendedor'),(3,'Bodeguero');

INSERT INTO categoria VALUES
(1,'Camisetas'),(2,'Pantalones'),(3,'Vestidos'),
(4,'Chaquetas'),(5,'Accesorios'),(6,'Zapatos');

INSERT INTO talla VALUES
(1,'XS'),(2,'S'),(3,'M'),
(4,'L'),(5,'XL'),(6,'XXL'),
(7,'36'),(8,'37'),(9,'38'),
(10,'39'),(11,'40'),(12,'41'),
(13,'42'),(14,'43'),(15,'44');

INSERT INTO color VALUES
(1,'Negro'),(2,'Blanco'),(3,'Azul'),(4,'Rojo');

INSERT INTO tipo_documento VALUES
(1, 'Cédula'), (2, 'Pasaporte');

INSERT INTO genero VALUES
(1, 'Hombre'), (2, 'Mujer'), (3, 'Unisex'), (4, 'Niño');

INSERT INTO tipo_descuento VALUES
(1, 'Porcentaje'), (2, 'Valor Fijo');

INSERT INTO metodo_pago VALUES
(1, 'Efectivo'), (2, 'Tarjeta'), (3, 'Transferencia');

INSERT INTO estado_producto VALUES
(1, 'Disponible'), (2, 'Agotado'), (3, 'Descontinuado');

INSERT INTO estado_venta VALUES
(1, 'Pendiente'), (2, 'Completada'), (3, 'Anulada');

INSERT INTO estado_empleado VALUES
(1, 'Activo'), (2, 'Inactivo');

INSERT INTO estado_descuento VALUES
(1, 'Activo'), (2, 'Inactivo');

-- CAMBIO 2: datos de estado_bloqueo (el codigo compara con estos nombres exactos)
INSERT INTO estado_bloqueo VALUES
(1, 'Desbloqueado'), (2, 'Bloqueado');

-- NOTA (CAMBIO 1): el usuario administrador se crea con database/seed.php,
-- porque la contrasena debe guardarse con password_hash y no se puede escribir
-- a mano aqui. El seed inserta un empleado por cada rol.

-- ---------- Vista de inventario (la usa el modulo de inventario) ----------
CREATE OR REPLACE VIEW vista_inventario_productos AS
SELECT i.id_Inventario, p.Codigo, p.Nombre AS Producto, c.Nombre AS Categoria,
       co.Nombre AS Color, t.Nombre AS Talla, i.Stock, p.Precio_base, pr.Nombre AS Proveedor
FROM inventario i
JOIN producto p ON p.id_Producto = i.id_Producto
JOIN categoria c ON c.id_Categoria = p.id_Categoria
JOIN color co ON co.id_Color = i.id_Color
JOIN talla t ON t.id_Talla = i.id_Talla
JOIN proveedor pr ON pr.id_Proveedor = p.id_Proveedor;
