<?php
// Manejo uniforme de errores de base de datos para los endpoints de api/.
// Siempre escribe el detalle real en el log del servidor (error_log).
// Si MODO_DESARROLLO es true, ademas devuelve el mensaje real de PDO en el JSON,
// para poder diagnosticar. En produccion muestra un mensaje generico.

require_once __DIR__ . '/../config/conexion.php';

// Devuelve el arreglo de respuesta JSON para un error de base de datos.
// $e: la excepcion capturada. $contexto: texto corto para el log (ej. 'completar venta').
function respuestaErrorBD($e, $contexto = '') {
    $real = $e->getMessage();
    // Siempre dejar rastro en el log del servidor
    error_log('[StyleStore] Error BD' . ($contexto ? " ($contexto)" : '') . ': ' . $real);

    $desarrollo = defined('MODO_DESARROLLO') && MODO_DESARROLLO;
    return [
        'ok'      => false,
        'mensaje' => $desarrollo ? ('Error de base de datos: ' . $real) : 'Error de base de datos',
    ];
}
