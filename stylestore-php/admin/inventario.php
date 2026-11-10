<?php
// Inventario (Administrador): consulta y ajustes manuales.
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Administrador');

$tituloPagina = 'Inventario';
$subtituloPagina = 'Stock por talla y color';
$itemActivo = 'inventario';
$base = defined('URL_BASE') ? URL_BASE : '';
$puedeAjustar = true; // el Administrador si puede ajustar
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/inventario_vista.php';
require __DIR__ . '/../includes/pie.php';
