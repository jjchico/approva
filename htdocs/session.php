<?php

// Comprueba e inicia la sesion

require_once('config.php');

// Usar un nombre de sesión específico para cada instalación de la aplicación
// permite usar varias aplicaciones instaladas en el mismo servidor web
session_name(SESSION_ID);
session_start();

?>
