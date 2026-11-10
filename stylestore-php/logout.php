<?php
// Cierra la sesion y vuelve al login.
require_once __DIR__ . '/includes/sesion.php';
cerrarSesion();
header('Location: ' . (defined('URL_BASE') ? URL_BASE : '') . '/index.php');
exit;
