<?php
// =============================================================
//  seed.php - Crea un usuario de prueba por cada rol.
//  Las contrasenas se guardan cifradas con password_hash.
//
//  Ejecutar UNA VEZ abriendo en el navegador:
//    http://localhost/stylestore-php/database/seed.php
//
//  IMPORTANTE: despues de ejecutarlo, BORRA o renombra este archivo.
// =============================================================

require_once __DIR__ . '/../config/conexion.php';

header('Content-Type: text/html; charset=utf-8');

// Usuarios de prueba: usuario, contrasena, nombre, apellido, documento, correo, id_Cargo
$usuarios = [
    ['admin',     'admin123',     'Admin',    'StyleStore', '12345678', 'admin@stylestore.com',     1], // Administrador
    ['vendedor',  'vendedor123',  'Vendedor', 'StyleStore', '22222222', 'vendedor@stylestore.com',  2], // Vendedor
    ['bodeguero', 'bodeguero123', 'Bodeguero','StyleStore', '33333333', 'bodeguero@stylestore.com', 3], // Bodeguero
];

try {
    $pdo = conectar();

    $sql = "INSERT INTO empleados
                (Num_Documento, Nombre, Apellido, Correo, Usuario, Contrasena,
                 Intentos_fallidos, id_Cargo, id_Estado_Emp, id_Estado_Bloq)
            VALUES
                (:doc, :nombre, :apellido, :correo, :usuario, :hash, 0, :cargo, 1, 1)";
    $stmt = $pdo->prepare($sql);

    $creados = [];
    foreach ($usuarios as $u) {
        list($usuario, $pass, $nombre, $apellido, $doc, $correo, $cargo) = $u;

        // Evitar duplicados si el seed se ejecuta mas de una vez
        $verif = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE Usuario = :usuario");
        $verif->execute([':usuario' => $usuario]);
        if ($verif->fetchColumn() > 0) {
            $creados[] = "Ya existia: $usuario";
            continue;
        }

        $stmt->execute([
            ':doc'      => $doc,
            ':nombre'   => $nombre,
            ':apellido' => $apellido,
            ':correo'   => $correo,
            ':usuario'  => $usuario,
            ':hash'     => password_hash($pass, PASSWORD_DEFAULT), // contrasena cifrada
            ':cargo'    => $cargo,
        ]);
        $creados[] = "Creado: $usuario / $pass";
    }

    echo "<h2>Usuarios de prueba</h2><ul>";
    foreach ($creados as $linea) {
        echo "<li>" . htmlspecialchars($linea) . "</li>";
    }
    echo "</ul>";
    echo "<p><strong>Listo. Ahora BORRA o renombra este archivo (seed.php) por seguridad.</strong></p>";

} catch (PDOException $e) {
    echo "<h2>Error</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Revisa que importaste bd_stylestore.sql y que config/conexion.php esta configurado.</p>";
}
