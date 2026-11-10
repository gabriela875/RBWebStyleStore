<?php
// Funciones de validacion en el SERVIDOR.
// Las mismas reglas existen en assets/js/validaciones.js (cliente).

// Solo letras (incluye tildes y ñ) y espacios. Para nombres y apellidos.
function soloLetras($texto) {
    return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ ]+$/u', trim($texto)) === 1;
}

// Solo numeros. Para documento y telefono.
function soloNumeros($texto) {
    return preg_match('/^[0-9]+$/', trim($texto)) === 1;
}

// Email con formato valido.
function emailValido($correo) {
    return filter_var(trim($correo), FILTER_VALIDATE_EMAIL) !== false;
}

// Fecha valida en formato AAAA-MM-DD.
function fechaValida($fecha) {
    $f = trim($fecha);
    $d = DateTime::createFromFormat('Y-m-d', $f);
    return $d && $d->format('Y-m-d') === $f;
}

// Campo obligatorio (no vacio).
function requerido($valor) {
    return isset($valor) && trim($valor) !== '';
}

// Longitud minima (por defecto 6, para contrasenas).
function longitudMinima($texto, $min = 6) {
    return mb_strlen(trim($texto)) >= $min;
}

// Numero mayor que cero (para precios, cantidades).
function mayorQueCero($valor) {
    return is_numeric($valor) && (float)$valor > 0;
}

// Ejecuta una lista de validaciones y devuelve el primer mensaje de error, o null si todo bien.
// $reglas es un arreglo de ['condicion' => bool, 'mensaje' => string].
function primerError($reglas) {
    foreach ($reglas as $regla) {
        if (!$regla['condicion']) {
            return $regla['mensaje'];
        }
    }
    return null;
}
