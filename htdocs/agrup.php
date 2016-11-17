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

//config.php
require_once('config.php');
//functions.php
require_once('functions.php');

// if session is not set redirect the user
if(empty($_SESSION['id'])){
	header("Location:login.php");
}

date_default_timezone_set('Europe/Madrid');

echo '<script>$(function(){$( "#fecha" ).datepicker({dateFormat:\'dd-mm-yy\'});});</script>';

if(isset($_POST['agrup'])){

    $agrup = $_POST['agrup'];
    $id = $_POST['id'];

    //inPlace para cambiar nombre de agrupamiento
    echo '<script>';
		echo '$(\'#agrup_'.$id.'\').editInPlace({
			url: \'inplace.php\',
			params: \'script=agrup&field=agrupamiento&table='.$tabla_agrupamientos.'&id='.$id.'\',
			show_buttons: true,
			field_type: "text"
		});';
    echo '</script>';
    //fin inPlace

    echo '<a href="#" onclick="eliminaAgrup(\''.$id.'\')" title="Eliminar Agrupamiento" ><img src="css/images/delete.png" /></a>';
    echo ' ';
    echo '<h1 id="agrup_'.$id.'">'.$agrup.'</h1>';

    if(isset($_POST['fecha'])){
        $fecha = $_POST['fecha'];
        $date = explode('-', $fecha);
        $mysqlDate = $date[2].'-'.$date[1].'-'.$date[0];
        echo 'Sesión: <input style="text-align:center;" type="text" id="fecha" name="fecha" value="'.$fecha.'" onchange="goAgrupFecha(\''.$agrup.'\',\''.$id.'\')" /><br/><br/>';
    }else{
        $fecha = date('d-m-Y');
        $date = explode('-', $fecha);
        $mysqlDate = $date[2].'-'.$date[1].'-'.$date[0];
        echo 'Sesión: <input style="text-align:center;" type="text" id="fecha" name="fecha" value="'.$fecha.'" onchange="goAgrupFecha(\''.$agrup.'\',\''.$id.'\')" /><br/><br/>';
    }

//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
{
die("Connection error: " . mysqli_connect_error());
}

    //si hay petición de eliminar alumno
     if(isset($_POST['idAlumnoElimina'])){
        $idAlumnoElimina = $_POST['idAlumnoElimina'];
        //borramos de la base de datos

        $queryDelAlum="DELETE FROM `$tabla_alumnado` WHERE `$tabla_alumnado`.id = '$idAlumnoElimina'";
        $resultDelAlum=mysqli_query($con_mysql,$queryDelAlum)or die('ERROR:'.mysqli_error($con_mysql));

        $queryDelAlumCalif="DELETE FROM `$tabla_calificaciones` WHERE `$tabla_calificaciones`.alumno_id = '$idAlumnoElimina'";
        $resultDelAlumCalif=mysqli_query($con_mysql,$queryDelAlumCalif)or die('ERROR:'.mysqli_error($con_mysql));
    }
    //fin petición eliminar alumno

    //si hay petición de cambiar alumno de agrupamiento
     if(isset($_POST['idAlumnoCambia'])){
        $idAlumnoCambia = $_POST['idAlumnoCambia'];
        $idAgrupCambio = $_POST['idAgrupCambio'];
        //lo cambiamos de agrupamiento
        $queryCambiaAlum="UPDATE `$tabla_alumnado` SET agrupamiento_id = '$idAgrupCambio' WHERE `$tabla_alumnado`.id = '$idAlumnoCambia'";
        $resultCambiaAlum=mysqli_query($con_mysql,$queryCambiaAlum)or die('ERROR:'.mysqli_error($con_mysql));
    }
    //fin petición eliminar alumno

    //si hay petición matricular un alumno
    if(isset($_POST['nombreAlum'])){
        $nombreAlum = $_POST['nombreAlum'];
        //insertamos en base de datos

        $query="INSERT INTO `$tabla_alumnado` (`id`, `agrupamiento_id`, `alumno`) VALUES (NULL, '$id', '$nombreAlum');";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    }
    //fin petición matricular un alumno

    //si hay petición poner falta
    if(isset($_POST['asistencia'])){
        $asistencia=$_POST['asistencia'];//el tipo de asistencia
        $idAlumno=$_POST['idAlumno'];//el id de alumno

        //si queremos anular falta
        if($asistencia=='0'){
            //borramos
            $queryEliminaAsistencia = "delete FROM `$tabla_asistencia` where alumno_id='$idAlumno' and fecha='$mysqlDate'";
            $resultEliminaAsistencia = mysqli_query($con_mysql,$queryEliminaAsistencia)or die('ERROR:'.mysqli_error($con_mysql));
        }else{
            //consultamos si hay dato
            $queryAsistencia = "select * FROM `$tabla_asistencia` where alumno_id='$idAlumno' and fecha='$mysqlDate'";
            $resultAsistencia = mysqli_query($con_mysql,$queryAsistencia)or die('ERROR:'.mysqli_error($con_mysql));
            if(mysqli_num_rows($resultAsistencia)>0){
                //actualizo
                $queryActualizaAsistencia = "update `$tabla_asistencia` set tipo = '$asistencia' where alumno_id='$idAlumno' and fecha='$mysqlDate'";
                $resultActualizaAsistencia = mysqli_query($con_mysql,$queryActualizaAsistencia)or die('ERROR:'.mysqli_error($con_mysql));
            }else{
                //inserto
                $queryInsertaAsistencia="INSERT INTO `$tabla_asistencia` (`id`, `alumno_id`, `tipo`,`fecha`) VALUES (NULL, '$idAlumno', '$asistencia','$mysqlDate');";
                $resultInsertaAsistencia=mysqli_query($con_mysql,$queryInsertaAsistencia) or die('ERROR:'.mysqli_error($con_mysql));
            }
        }
    }

    //fin petición poner falta

    //consulta alumnado agrupamiento
    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$id' order by `alumno`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){
            echo '<table><tr><td>';
            //primera mitad
            for($a=0;$a<round(($num/2),0);$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumEdita = $row['id'];
                //inPlace para cambiar nombre de alumno
                echo '<script>';
					echo '$(\'#'.$idAlumEdita.'\').editInPlace({
						url: \'inplace.php\',
						params: \'script=agrup&field=alumno&table='.$tabla_alumnado.'&id='.$idAlumEdita.'\',
						show_buttons: true,
						field_type: "text"
					});';
                echo '</script>';
                //fin inPlace
                echo '<li style="list-style:none;">';
                    echo '<a href="#" onclick="eliminaAlum(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\')" title="Eliminar"><img src="css/images/delete.png" /></a>';
                    echo ' ';
                    echo '<select id="selCambiaAgrup" name="selCambiaAgrup" onchange="cambiaAlumAgrup(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\')" style="width:140px;">';
                    echo '<option value="0">Cambio agrupamiento</option>';
                        //consulta de agrupamientos para listar
                        $queryAgCambio="SELECT * FROM `$tabla_agrupamientos` where id <> '$id' order by `agrupamiento`";
                        $resultAgCambio=mysqli_query($con_mysql,$queryAgCambio)or die('ERROR:'.mysqli_error($con_mysql));
                        $numAgCambio=mysqli_num_rows($resultAgCambio);
                        if($numAgCambio>0){
                            for($ac=0;$ac<$numAgCambio;$ac++){
                                $rowAgCambio=mysqli_fetch_array($resultAgCambio,MYSQLI_ASSOC);
                                echo '<option value="'.$rowAgCambio['id'].'">'.$rowAgCambio['agrupamiento'].'</option>';
                            }
                        }
                    echo '</select>';
                    echo ' ';
                    //consultamos si hay dato
                    $queryAsistencia = "select * FROM `$tabla_asistencia` where alumno_id='$idAlumEdita' and fecha='$mysqlDate'";
                    $resultAsistencia = mysqli_query($con_mysql,$queryAsistencia)or die('ERROR:'.mysqli_error($con_mysql));
                    if(mysqli_num_rows($resultAsistencia)>0){
                        $rowAsistencia = mysqli_fetch_array($resultAsistencia,MYSQLI_ASSOC);
                        $tipoAsistencia = $rowAsistencia['tipo'];
                        echo '<select name="selAsis_'.$idAlumEdita.'" id="selAsis_'.$idAlumEdita.'" onchange="asistencia(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\');" style="width:40px;background:orange;">';
                    }else{
                        echo '<select name="selAsis_'.$idAlumEdita.'" id="selAsis_'.$idAlumEdita.'" onchange="asistencia(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\');" style="width:40px;">';
                    }
                        echo '<option value="0">A</option>';
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='f')){
                            echo '<option value="f" selected="selected" style="color:red;">F</option>';
                        }else{
                            echo '<option value="f">F</option>';
                        }
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='r')){
                            echo '<option value="r" selected="selected">R</option>';
                        }else{
                            echo '<option value="r">R</option>';
                        }
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='j')){
                            echo '<option value="j" selected="selected">J</option>';
                        }else{
                            echo '<option value="j">J</option>';
                        }
                    echo '</select>';
                    echo ' ';
                    //enlace para ficha
                    echo '<a href="#" onclick="goFicha(\''.$idAlumEdita.'\')" title="Ficha"><img src="css/images/ficha.png" /></a>';
                    echo ' ';
                    //fin enlace para ficha
                    echo ''.($a+1).'. ';
                    echo '<span id="'.$idAlumEdita.'"><big><b>'.$row['alumno'].'</b></big></span>';

                echo '</li>';
            }
            echo '</td>';
            //fin primera mitad
            //segunda mitad
            echo '<td>';
            $partida=round(($num/2),0);
            for($a=$partida;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumEdita = $row['id'];
                //inPlace para cambiar nombre de alumno
                echo '<script>';
					echo '$(\'#'.$idAlumEdita.'\').editInPlace({
						url: \'inplace.php\',
						params: \'script=agrup&field=alumno&table='.$tabla_alumnado.'&id='.$idAlumEdita.'\',
						show_buttons: true,
						field_type: "text"
					});';
                echo '</script>';
                //fin inPlace
                echo '<li style="list-style:none;">';
                    echo '<a href="#" onclick="eliminaAlum(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\')" title="Eliminar"><img src="css/images/delete.png" /></a>';
                    echo ' ';
                    echo '<select id="selCambiaAgrup" name="selCambiaAgrup" onchange="cambiaAlumAgrup(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\')" style="width:140px;">';
                    echo '<option value="0">Cambio agrupamiento</option>';
                        //consulta de agrupamientos para listar
                        $queryAgCambio="SELECT * FROM `$tabla_agrupamientos` where id <> '$id' order by `agrupamiento`";
                        $resultAgCambio=mysqli_query($con_mysql,$queryAgCambio)or die('ERROR:'.mysqli_error($con_mysql));
                        $numAgCambio=mysqli_num_rows($resultAgCambio);
                        if($numAgCambio>0){
                            for($ac=0;$ac<$numAgCambio;$ac++){
                                $rowAgCambio=mysqli_fetch_array($resultAgCambio,MYSQLI_ASSOC);
                                echo '<option value="'.$rowAgCambio['id'].'">'.$rowAgCambio['agrupamiento'].'</option>';
                            }
                        }
                    echo '</select>';
                    echo ' ';
                    //consultamos si hay dato
                    $queryAsistencia = "select * FROM `$tabla_asistencia` where alumno_id='$idAlumEdita' and fecha='$mysqlDate'";
                    $resultAsistencia = mysqli_query($con_mysql,$queryAsistencia)or die('ERROR:'.mysqli_error($con_mysql));
                    if(mysqli_num_rows($resultAsistencia)>0){
                        $rowAsistencia = mysqli_fetch_array($resultAsistencia,MYSQLI_ASSOC);
                        $tipoAsistencia = $rowAsistencia['tipo'];
                        echo '<select name="selAsis_'.$idAlumEdita.'" id="selAsis_'.$idAlumEdita.'" onchange="asistencia(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\');" style="width:40px;background:orange;">';
                    }else{
                        echo '<select name="selAsis_'.$idAlumEdita.'" id="selAsis_'.$idAlumEdita.'" onchange="asistencia(\''.$idAlumEdita.'\',\''.$id.'\',\''.$agrup.'\');" style="width:40px;">';
                    }
                        echo '<option value="0">A</option>';
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='f')){
                            echo '<option value="f" selected="selected">F</option>';
                        }else{
                            echo '<option value="f">F</option>';
                        }
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='r')){
                            echo '<option value="r" selected="selected">R</option>';
                        }else{
                            echo '<option value="r">R</option>';
                        }
                        if((mysqli_num_rows($resultAsistencia)>0)&&($tipoAsistencia=='j')){
                            echo '<option value="j" selected="selected">J</option>';
                        }else{
                            echo '<option value="j">J</option>';
                        }
                    echo '</select>';
                    echo ' ';
                    //enlace para ficha
                    echo '<a href="#" onclick="goFicha(\''.$idAlumEdita.'\')" title="Ficha"><img src="css/images/ficha.png" /></a>';
                    echo ' ';
                    //fin enlace para ficha
                    echo ''.($a+1).'. ';
                    echo '<span id="'.$idAlumEdita.'"><big><b>'.$row['alumno'].'</b></big></span>';

                echo '</li>';
            }
            echo '</td>';
            //fin segunda mitad
            echo '</tr></table>';
        }else{
            echo '<p>No hay alumnado matriculado en este agrupamiento</p>';
        }
        //fin listado agrupamientos

    echo '<script>$("#txtAlum").focus();</script>';

    //input para añadir alumno al agrupamiento
    echo '<p style="text-align:center;">';
    echo '<span>Añadir alumno/a (apellidos y nombre):</span>';
    echo '<br/>';
    echo '<input type="text" id="txtAlum" name="txtAlum" size="40" />';
    echo '<br/>';
    echo '<br/>';
    echo '<a href="#" onclick="saveAlum(\''.$agrup.'\',\''.$id.'\')">Añadir alumno/a</a>';
    echo '</p>';

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

}

?>
