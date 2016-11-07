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

//forzamos codificación utf-8
header('Content-Type: text/html; charset=UTF-8');

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


//consultamos los agrupamientos
$query="select * from agrupamientos";
$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
$num=mysqli_num_rows($result);
    if($num>0){
        for($a=0;$a<$num;$a++){
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            $idAgrup=$row['id'];
            $agrup=$row['agrupamiento'];
            echo '<h1>'.$row['materia'].' '.$row['curso'].'</h1>';
            //comenzamos tabla
            echo '<table border="1" style="border-collapse:collapse">';
            echo '<tr>';
            echo '<th>Contenidos</th><th>Criterios de evaluación</th><th>Estándares de Aprendizaje</th><th>Competencias Clave</th><th>Trimestre</th>';
            echo '</tr>';
            
            //ahora consulto los estándares de este agrupamiento y los listo
            $queryA="SELECT * FROM `estandares` where agrupamiento_id='$idAgrup'";
            $resultA=mysqli_query($con_mysql,$queryA)or die('ERROR:'.mysqli_error());
            $numA=mysqli_num_rows($resultA);
            if($numA>0){                
                for($f=0;$f<$numA;$f++){
                    $rowA=mysqli_fetch_array($resultA,MYSQLI_ASSOC);
                    $estandar=$rowA['estandar'];
                    echo '<tr>';
                    echo '<td></td><td></td><td>'.$estandar.'</td><td></td><td></td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        }//fin for        
    }
// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
