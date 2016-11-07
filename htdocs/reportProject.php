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

// if session is not set redirect the user
if(empty($_SESSION['id'])){
	header("Location:login.php");	
}

?>

<html>
<head>
	<title>APPROVA</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
</head>
    
<body>
    
<?php

if(isset($_GET['nombreProyecto'])){
    $nombreProyecto = $_GET['nombreProyecto'];  
    $idAgrupamiento = $_GET['idAgrupamiento'];

    //config
require('config.php');
//functions.php
require('functions.php');

//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
  {
  die("Connection error: " . mysqli_connect_error());
  }   
    
    //datos sobre el agrupamiento
    $query="SELECT * FROM `agrupamientos` where id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    $num=mysqli_num_rows($result);
    if($num>0){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $agrupamiento = $row['agrupamiento'];
        $curso = $row['curso'];
        $materia = $row['materia'];
        $nivel = $row['nivel'];
    }
   
    echo '<br/><table style="width:40%"><tr><td style="width:80%;">Proyecto: <b>'.$nombreProyecto.'</b></td><td>Fecha:</td></tr></table>';
    echo '<table style="width:40%"><tr><td>Agrupamiento: '.$materia.' '.$curso.' '.$nivel.'</td></tr>';
    echo '<tr><td>Apellidos y nombre:</td></tr></table>';    
    
    //datos del proyecto
    $query="SELECT * FROM `proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    $num=mysqli_num_rows($result);
    if($num>0){
       for($a=0;$a<$num;$a++){
           $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
           //consulto para extraer texto del estándar
           $estandar_id = $row['estandar_id'];
           $queryEstandar = "select estandar from estandares where id='$estandar_id'";
           $resultEstandar = mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error());
           $rowEstandar = mysqli_fetch_array($resultEstandar,MYSQLI_ASSOC);
           echo '<br/><table style="width:40%"><tr><td style="width:90%;">Estándar de aprendizaje: '.$rowEstandar['estandar'].'</td><td>Calificación (máximo '.($row['peso']/10).' puntos):<br/><br/><br/><br/></td></tr></table>';
           echo '<table style="width:40%"><tr>';
           echo '<td>Competencias Clave: ';
                if($row['ccl']=='1') echo ' CCL ';
                if($row['cmct']=='1') echo ' CMCT ';
                if($row['cd']=='1') echo ' CD ';
                if($row['caa']=='1') echo ' CAA ';
                if($row['csyc']=='1') echo ' CSYC ';
                if($row['siep']=='1') echo ' SIEP ';
                if($row['cec']=='1') echo ' CEC ';
           echo '</td></tr></table>';
           $numItems = $row['num'];
           for($n=0;$n<$numItems;$n++){
                echo '<table style="width:40%"><tr><td style="width:90%;">Ítem '.($n+1).'</td><td>Puntuación:<br/><br/><br/><br/></td></tr></table>';
           }//fin de for items           
       }//fin de for estándares
    }//fin de if Num
    
}

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
    
</body></html>
