<?php
// Nueva venta (Administrador).
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Nueva Venta';
$subtituloPagina = 'Registrar una venta presencial';
$itemActivo = 'nueva-venta';
$base = defined('URL_BASE') ? URL_BASE : '';
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/venta_nueva_vista.php';
require __DIR__ . '/../includes/pie.php';
