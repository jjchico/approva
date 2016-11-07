<?php session_start();
/*
This file is part of APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje).

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is developed by Ram&oacute;n Castro P&eacute;rez. You can get more information at http://www.siestta.org

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
	
You cand find a copy of the GNU General Public License in the "license" directory.

You should have received a copy of the GNU General Public License along with APPROVA; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.  
*/

//si venimos de la instalación inicial, tenemos que eliminar el directorio instalación
if(isset($_GET['alta'])){
    system("rm -rf ".escapeshellarg('instalacion'));
    echo '<script>alert(\'Ya puede acceder a la Plataforma con este usuario y contraseña\');</script>';
}
//fin eliminar directorio instalación

//if logout then destroy the session and redirect the user
if(isset($_GET['logout']))
{
	unset($_SESSION['id']);
	session_unset();
    	session_destroy();
    	session_write_close();
    	setcookie(session_name(),'',0,'/');
    	session_regenerate_id(true);	
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>andClass Approva | Plataforma de Evaluación | Acceso</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="css/login.css">
<script type="text/javascript" src="js/jquery-3.0.0.min.js"></script>
</head>
<body onload="$('#username').focus();">
<div id="header">Approva | Plataforma de Evaluación</div>

<div id="login">
	<form method="post" action="index.php" id="login_form">
	<ul>
		<li>Código: <input name="username" type="text" id="username" value="" size="10"  /></li>
		<li>Clave: <input name="password" type="password" id="password" value="" size="10" /></li>
	</ul>
		<input class="button" name="Submit" type="submit" id="submit" value="Acceder" />
	<br/>
	<br/>
	<br/>
	<br/>	
	<br/>
	<br/>
		
	</form>
</div>

<div id="footer"><p>andClass 2016 | Spain </p></div>

</body>
</html>
