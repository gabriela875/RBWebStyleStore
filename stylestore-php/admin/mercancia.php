<?php
// Entrada de mercancia (Administrador).
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Entrada de Mercancía';
$subtituloPagina = 'Registrar productos que ingresan de un proveedor';
$itemActivo = 'mercancia';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/mercancia_vista.php';
require __DIR__ . '/../includes/pie.php';
