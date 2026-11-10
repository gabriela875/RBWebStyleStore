<?php
// Logica de autenticacion: login, intentos fallidos, bloqueo, desbloqueo.
// Traducido de AuthService.java / EmpleadoRepository.java del proyecto original.
// Diferencia intencional: las contrasenas se verifican con password_verify.

require_once __DIR__ . '/../config/conexion.php';

define('MAX_INTENTOS', 5); // igual que el original

// Busca un empleado por su usuario (con el nombre de su cargo y sus estados).
// Devuelve un arreglo asociativo o null si no existe.
function buscarEmpleadoPorUsuario($pdo, $usuario) {
    $sql = "SELECT e.*, c.Tipo AS Cargo,
                   ee.Nombre AS EstadoEmpleado, eb.Nombre AS EstadoBloqueo
            FROM empleados e
            JOIN cargo c            ON c.id_Cargo = e.id_Cargo
            JOIN estado_empleado ee ON ee.id_Estado_Emp = e.id_Estado_Emp
            JOIN estado_bloqueo eb  ON eb.id_Estado_Bloq = e.id_Estado_Bloq
            WHERE e.Usuario = :usuario";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':usuario' => $usuario]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// Intenta iniciar sesion. Si algo falla, lanza una excepcion con el mensaje del original.
// Si todo va bien, devuelve el arreglo del empleado.
function intentarLogin($pdo, $usuario, $contrasena) {
    $empleado = buscarEmpleadoPorUsuario($pdo, $usuario);

    if ($empleado === null) {
        throw new Exception('El usuario no existe en el sistema');
    }

    // Verificar bloqueo
    if ($empleado['EstadoBloqueo'] === 'Bloqueado') {
        throw new Exception('La cuenta está bloqueada. Contacte al Administrador para desbloquearla');
    }

    // Verificar estado activo
    if ($empleado['EstadoEmpleado'] !== 'Activo') {
        throw new Exception('El empleado está inactivo y no puede iniciar sesión');
    }

    // Verificar contrasena
    if (!password_verify($contrasena, $empleado['Contrasena'])) {
        registrarIntentoFallido($pdo, $empleado);
        // Releer para saber si quedo bloqueado con este intento
        $actual = buscarEmpleadoPorUsuario($pdo, $usuario);
        if ($actual && $actual['EstadoBloqueo'] === 'Bloqueado') {
            throw new Exception('La cuenta ha sido bloqueada por demasiados intentos fallidos');
        }
        throw new Exception('Contraseña incorrecta');
    }

    // Login exitoso: reiniciar intentos
    $pdo->prepare("UPDATE empleados SET Intentos_fallidos = 0 WHERE id_Empleado = :id")
        ->execute([':id' => $empleado['id_Empleado']]);

    return $empleado;
}

// Suma un intento fallido y bloquea la cuenta al llegar al maximo.
function registrarIntentoFallido($pdo, $empleado) {
    $intentos = (int)$empleado['Intentos_fallidos'] + 1;
    if ($intentos >= MAX_INTENTOS) {
        // Estado 'Bloqueado' = id 2 en estado_bloqueo
        $pdo->prepare("UPDATE empleados SET Intentos_fallidos = :n,
                         id_Estado_Bloq = (SELECT id_Estado_Bloq FROM estado_bloqueo WHERE Nombre = 'Bloqueado')
                       WHERE id_Empleado = :id")
            ->execute([':n' => $intentos, ':id' => $empleado['id_Empleado']]);
    } else {
        $pdo->prepare("UPDATE empleados SET Intentos_fallidos = :n WHERE id_Empleado = :id")
            ->execute([':n' => $intentos, ':id' => $empleado['id_Empleado']]);
    }
}

// Desbloquea una cuenta: reinicia intentos y pone estado 'Desbloqueado'. Solo el Admin la usa.
function desbloquearCuenta($pdo, $idEmpleado) {
    $pdo->prepare("UPDATE empleados SET Intentos_fallidos = 0,
                     id_Estado_Bloq = (SELECT id_Estado_Bloq FROM estado_bloqueo WHERE Nombre = 'Desbloqueado')
                   WHERE id_Empleado = :id")
        ->execute([':id' => $idEmpleado]);
}
