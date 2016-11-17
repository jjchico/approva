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

echo '<script>jQuery(document).ready(function($){$(\'#estandaresTable\').tableScroll({height:200});});</script>';

echo '<h1>Estándares de Aprendizaje</h1>';

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

//si hemos seleccionado agrupamiento
if(isset($_POST['id'])){
    $id = $_POST['id'];
}

//si hemos solicitado grabar estándar
if(isset($_POST['estandar'])){
    $estandar = $_POST['estandar'];
    //insertamos en base de datos
    $query="INSERT INTO `$tabla_estandares` (`id`, `agrupamiento_id`, `estandar`) VALUES (NULL, '$id', '$estandar');";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
}

//si hemos solicitado eliminar estándar de aprendizaje
if(isset($_POST['idEstandarEliminar'])){
    $idEstandarEliminar = $_POST['idEstandarEliminar'];
    //borramos de la base de datos
    $queryDelEstandar="DELETE FROM `$tabla_estandares` WHERE `$tabla_estandares`.id = '$idEstandarEliminar'";
    $resultDelEstandar=mysqli_query($con_mysql,$queryDelEstandar)or die('ERROR:'.mysqli_error());
}
//fin eliminar estándar de aprendizaje

//select con agrupamientos
//consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){
            echo '<p style="text-align:center;"><select id="selAgrup" name="selAgrup" onchange="listaEstandares()"></p>';
            echo '<option value="0">Seleccione Agrupamiento</option>';
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                if(isset($_POST['id'])&&($_POST['id']==$row['id'])){
                    echo '<option value="'.$row['id'].'" selected="selected">'.$row['agrupamiento'].'</option>';
                }else{
                    echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';
                }
            }
            echo '</select>';
        }else{
            echo '<p style="text-align:center;">No hay agrupamientos registrados</p>';
        }
//fin listado agrupamientos


        //tabla scroll con estándares de aprendizaje registrados para el agrupamiento

        echo '<br/><br/><br/><table id="estandaresTable" name="estandaresTable">';
                echo '<tr>';
                    echo '<td>Estándar de Aprendizaje</td><td></td>';
                echo '</tr>';
            if(isset($_POST['id'])){
                //seleccionar estándares para el agrupamiento seleccionado
                $query="SELECT * FROM `$tabla_estandares` where agrupamiento_id='$id'";
                $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
                $num=mysqli_num_rows($result);
                if($num>0){
                    for($a=0;$a<$num;$a++){
                        $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                        //inPlace para cambiar nombre de agrupamiento
                        echo '<script>';
					    echo '$(\'#estandar_'.$row['id'].'\').editInPlace({
						url: \'inplace.php\',
						params: \'script=estandar&field=estandar&table='.$tabla_estandares.'&id='.$row['id'].'\',
						show_buttons: true,
						field_type: "textarea"
					    });';
                        echo '</script>';
                        //fin inPlace
                        echo '<tr>';
                            echo '<td><span id="estandar_'.$row['id'].'">'.$row['estandar'].'</span></td>';
                            echo '<td><a href="#" onclick="eliminaEstandar(\''.$row['id'].'\',\''.$id.'\')" title="Eliminar Estándar" ><img src="css/images/delete.png" /></a></td>';
                        echo '</tr>';
                    }
                }
            }
        echo '</table>';

    //fin seleccionar estándares

    //input para añadir estándar
    if(isset($_POST['id'])){
        echo '<p style="text-align:center;">';
        echo '<span>Texto del estándar de aprendizaje:</span>';
        echo '<br/>';
        echo '<textarea rows="8" cols="138" id="taEstandar" name="taEstandar"></textarea>';
        echo '<br/>';
        echo '<br/>';
        echo '<a href="#" onclick="saveEstandar(\''.$id.'\')">Añadir estándar</a>';
        echo '</p>';
    }

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
