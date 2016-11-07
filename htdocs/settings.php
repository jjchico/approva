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

echo '<h1>Ajustes</h1>';

//config.php
require('config.php');
//functions.php
require('functions.php');
//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
{
die("Connection error: " . mysqli_connect_error());
}

//ACCIONES A EJECUTAR SI HEMOS ENVIADOR ORDEN

//Copiar Estándares de Aprendizaje
if(isset($_POST['idAgrupamientoOrigen'])&&isset($_POST['idAgrupamientoDestino'])){
    $idOrigen = $_POST['idAgrupamientoOrigen'];
    $idDestino = $_POST['idAgrupamientoDestino'];
    //seleccionamos los estándares de aprendizaje del agrupamiento origen
    //seleccionar estándares para el agrupamiento seleccionado
                $query="SELECT * FROM `estandares` where agrupamiento_id='$idOrigen'";
                $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
                $num=mysqli_num_rows($result);
                if($num>0){
                    for($e=0;$e<$num;$e++){
                        $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                        $estandar = $row['estandar'];
                        //ahora lo grabo con el id del agrupamiento destino
                        $queryInsert="INSERT INTO `estandares` (`id`, `agrupamiento_id`, `estandar`) VALUES (NULL, '$idDestino', '$estandar');";
                        $resultInsert=mysqli_query($con_mysql,$queryInsert)or die('ERROR:'.mysqli_error());
                    }
                //doy aviso
                echo '<script>alert(\'Estándares Copiados. Compruebe que así es en la opción Estándares.\');</script>';
                }    
}//fin copiar estándares de aprendizaje

//FIN ACCIONES



//AJUSTES///////////////////////////////////////////////////

//Añadir agrupamiento
echo '<h3>Añadir Nuevo Agrupamiento</h3>';
echo '<a href="#" onclick="goAddAgrup()">Añadir Agrupamiento</a>';
//fin añadir agrupamiento

//Copiar estándares de aprendizaje a otro agrupamiento
echo '<h3>Copiar estándares de aprendizaje</h3>';

//select con agrupamientos origen
//consulta de agrupamientos para listar
        $query="SELECT * FROM `agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){            
            echo '<select id="selAgrupOrigen" name="selAgrupOrigen">';
            echo '<option value="0">Seleccione Agrupamiento Origen</option>';
            for($o=0;$o<$num;$o++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';                              
            }
            echo '</select>';
        }else{
            echo '<p>No hay agrupamientos registrados</p>';
        }
//fin listado agrupamientos origen

print '<br/><br/>';

//select con agrupamientos destino
//consulta de agrupamientos para listar
        $query="SELECT * FROM `agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){            
            echo '<select id="selAgrupDestino" name="selAgrupDestino" onchange="copiaEstandares()">';
            echo '<option value="0">Seleccione Agrupamiento Destino</option>';
            for($d=0;$d<$num;$d++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';                              
            }
            echo '</select>';
        }else{
            echo '<p>No hay agrupamientos registrados</p>';
        }
//fin listado agrupamientos origen

//Exportar estándares de aprendizaje para programación
echo '<h3>Exportar estándares de aprendizaje para programación</h3>';

echo '<a href="#" onclick="exportarEstandaresProgramacion();">Exportar</a>';

//Hacer copia de seguridad
echo '<h3>Realizar copia de seguridad</h3>';

echo '<a href="#" onclick="backUp();">Copia de Seguridad</a>';

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
