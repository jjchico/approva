<?php //session_start();
/*
This file is part of APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje).

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is developed by Ram&oacute;n Castro P&eacute;rez. You can get more information at http://www.siestta.org

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You cand find a copy of the GNU General Public License in the "license" directory.

You should have received a copy of the GNU General Public License along with APPROVA; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
*/

include("session.php");

// if session is not set redirect the user
if(empty($_SESSION['id'])){
	header("Location:login.php");
}


/*
En este script llevamos a cabo las acciones
que provengan de los diferentes scripts que
ejecutan el editInPlace

Clasificaré por parámetros:
1) parámetro "script" para identificar de qué script vengo
2) parámetro "table" para identificar qué tabla tengo que actualizar
3) parámetro "update_value" que es el nuevo valor que ha de registrarse en la tabla
*/

//config.php
require_once('config.php');
//functions.php
require_once('functions.php');
//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
  {
  die("Connection error: " . mysqli_connect_error());
  }

$script=$_POST['script'];
$value=$_POST['update_value'];

//en función del script vamos aplicando la lógica
if($script=='agrup'){
	$field=$_POST['field'];
	$table=$_POST['table'];
	if(isset($_POST['id'])){
		$id=$_POST['id'];
		$query="update ".$table." set ".$field."='$value' where id='$id'";
	}
	$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
	if($result){
		if($field=='alumno'){echo '<big><b>'.$value.'</b></big>';}
        if($field=='agrupamiento'){echo '<h1>'.$value.'</h1>';}
	}else{
		echo 'Error. No se ha realizado ningún cambio.';
	}
}

if($script=='estandar'){
	$field=$_POST['field'];
	$table=$_POST['table'];
	if(isset($_POST['id'])){
		$id=$_POST['id'];
		$query="update ".$table." set ".$field."='$value' where id='$id'";
	}
	$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
	if($result){
		echo $value;
	}else{
		echo 'Error. No se ha realizado ningún cambio.';
	}
}

if($script=='project'){
    $idAgrupamiento = $_POST['idAgrupamiento'];
    $nombreProyecto = $_POST['proyecto'];
    $query="update `$tabla_proyectos` set proyecto='$value' where agrupamiento_id='$idAgrupamiento' and proyecto='$nombreProyecto'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    if($result){
		echo '<b>'.$value.'</b>';
	}else{
		echo 'Error. No se ha realizado ningún cambio.';
	}
}

if($script=='projectPeso'){
    $idProyecto = $_POST['idProyecto'];
    $query="update `$tabla_proyectos` set peso='$value' where id='$idProyecto'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    if($result){
		echo '<b>'.$value.'</b>';
	}else{
		echo 'Error. No se ha realizado ningún cambio.';
	}
}

// Free result set
//mysqli_free_result($result);
mysqli_close($con_mysql);



?>
