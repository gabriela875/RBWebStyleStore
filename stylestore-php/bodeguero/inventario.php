<?php
// Inventario (Bodeguero): consulta de stock (sin ajustes manuales).
require_once __DIR__ . '/../includes/proteger.php';
protegerPagina('Bodeguero');

$tituloPagina = 'Inventario';
$subtituloPagina = 'Stock por talla y color';
$itemActivo = 'inventario';
$base = defined('URL_BASE') ? URL_BASE : '';
$puedeAjustar = false; // el Bodeguero no registra ajustes (solo el Admin)
require __DIR__ . '/../includes/encabezado.php';
require __DIR__ . '/../includes/inventario_vista.php';
require __DIR__ . '/../includes/pie.php';
