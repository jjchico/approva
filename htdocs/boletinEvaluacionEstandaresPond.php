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

?>

<html>
<head>
	<title>APPROVA</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
</head>


<?php

//forzamos codificación utf-8
header('Content-Type: text/html; charset=UTF-8');

//zona horaria por defecto
date_default_timezone_set('Europe/Madrid');

//config
require_once('config.php');
//functions.php
require_once('functions.php');

//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
  {
  die("Connection error: " . mysqli_connect_error());
  }


//recojo primeras variables
$idAgrupamiento=$_GET['selAgrupInformeEvaluacion'];
$nombreAgrupamiento=$_GET['nombreAgrupamiento'];
$fechaIni=$_GET['fechaIni'];
$fechaIniM=date("Y-m-d", strtotime($fechaIni) );
$fechaFin=$_GET['fechaFin'];
$fechaFinM=date("Y-m-d", strtotime($fechaFin) );

//recojo los id de los estándares y también los pesos
$query="SELECT distinct estandar_id FROM `$tabla_proyectos` where agrupamiento_id='$idAgrupamiento' and (fecha between '$fechaIniM' and '$fechaFinM')";
$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
$num=mysqli_num_rows($result);
for($i=0;$i<$num;$i++){
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
    if(isset($_GET['hid_idEstandar_'.$i.''])){
        $arrayIdEstandar[]=$_GET['hid_idEstandar_'.$i.''];
    }
    if(isset($_GET['hid_Peso_'.$i.''])){
        $arrayPesos[]=$_GET['hid_Peso_'.$i.''];
    }
}
//fin recogida idEstándares

//selecciono alumnado del agrupamiento
$queryAlum="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
$resultAlum=mysqli_query($con_mysql,$queryAlum)or die('ERROR:'.mysqli_error($con_mysql));
$numAlum=mysqli_num_rows($resultAlum);
for($a=0;$a<$numAlum;$a++){
    $rowAlum = mysqli_fetch_array($resultAlum,MYSQLI_ASSOC);
    //array para nombres
    $alum[]=$rowAlum['alumno'];
    $idAlum[]=$rowAlum['id'];
}
//fin recogida alumnado

echo '<br/><h3 style="text-align:center;">Informe de Evaluación Período '.$fechaIni.' a '.$fechaFin.' para el agrupamiento '.$nombreAgrupamiento.'</h3>';

echo '<table border="1" style="margin:auto;width="98%;font-size:9px;">';
echo '<tr><th>Estándar de aprendizaje</th>';
for($c=0;$c<count($alum);$c++){
    echo '<th style="height: 250px;vertical-align: bottom;font-size:8px;"><div class="verticalText">'.$alum[$c].'</div></th>';
}
echo '</tr>';

//bucle para ir por bloques de estándares
for($e=0;$e<count($arrayIdEstandar);$e++){
    $idEstandar = $arrayIdEstandar[$e];
    $pesoEstandar = $arrayPesos[$e]/100;
    //selecciono los proyectos realizados por el estándar en el período
    $queryEstandar="select `$tabla_estandares`.estandar,`$tabla_proyectos`.id,`$tabla_proyectos`.proyecto,`$tabla_proyectos`.fecha FROM `$tabla_estandares`,`$tabla_proyectos` where `$tabla_proyectos`.estandar_id='$idEstandar' and `$tabla_proyectos`.estandar_id=`$tabla_estandares`.id and `$tabla_proyectos`.agrupamiento_id='$idAgrupamiento' and (`$tabla_proyectos`.fecha between '$fechaIniM' and '$fechaFinM') order by `$tabla_proyectos`.fecha";
    $resultEstandar=mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error($con_mysql));
    $numEstandar=mysqli_num_rows($resultEstandar);
    for($n=0;$n<$numEstandar;$n++){
        $rowEstandar=mysqli_fetch_array($resultEstandar,MYSQLI_ASSOC);
        $textoEstandar=$rowEstandar['estandar'];
        $fechaIniM=date("Y-m-d", strtotime($fechaIni) );
        $idProyecto=$rowEstandar['id'];
        echo '<tr>';
        //colocamos texto del estándar y proyecto en el que se trabajó
        echo '<td>'.$textoEstandar.' ('.$rowEstandar['proyecto'].')</td>';
        //ahora vamos con las calificaciones de los alumnos
        for($c=0;$c<count($alum);$c++){
            $idAlumno = $idAlum[$c];
            $queryCalif = "select `$tabla_calificaciones`.calificacion,`$tabla_proyectos`.peso FROM `$tabla_calificaciones`,`$tabla_proyectos` where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto_id='$idProyecto' and `$tabla_calificaciones`.proyecto_id=`$tabla_proyectos`.id and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
            $resultCalif = mysqli_query($con_mysql,$queryCalif) or die('ERROR:'.mysqli_error($con_mysql));
            $numCalif = mysqli_num_rows($resultCalif);
            $rowCalif = mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);
            if($numCalif>0){
                echo '<td>'.round(($rowCalif['calificacion']/$rowCalif['peso'])*100,2).'</td>';
            }else{
                echo '<td></td>';
            }
        }//fin de for alumnos
        echo '</tr>';
    }
    echo '<tr><td style="background-color:#dedede;">Calificación Media Ponderada del Estándar ('.$pesoEstandar.')</td>';
    //vamos con las medias de cada estándar
    for($c=0;$c<count($alum);$c++){
        $idAlumno = $idAlum[$c];
     $queryCalifM = "select avg(`$tabla_calificaciones`.calificacion/`$tabla_proyectos`.peso)*100 as media FROM `$tabla_calificaciones`,`$tabla_proyectos` where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto_id=`$tabla_proyectos`.id and `$tabla_proyectos`.estandar_id='$idEstandar' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
        $resultCalifM = mysqli_query($con_mysql,$queryCalifM) or die('ERROR:'.mysqli_error($con_mysql));
        $rowCalifM = mysqli_fetch_array($resultCalifM,MYSQLI_ASSOC);
        echo '<td style="background-color:#dedede;">'.round($rowCalifM['media']*$pesoEstandar,2).'</td>';
        $arrayMediasPond[$e][$c]=$rowCalifM['media']*$pesoEstandar;//echo$arrayMediasPond[0][1];
    }//fin de for calificación media del estándar
    echo '</tr>';
}//fin de for todos los estándares

//cerramos la tabla con la media global
echo '<tr>';
    echo '<td style="background-color:#dedede;"><big><b>Calificación Final</b></big></td>';
    for($c=0;$c<count($alum);$c++){
		$notaFinal = 0;
        for($e=0;$e<count($arrayIdEstandar);$e++){
            $notaFinal += $arrayMediasPond[$e][$c];
        }
        echo '<td style="background-color:#dedede;text-align:center;"><big><b>'.round($notaFinal,2).'</b></big></td>';
        unset($notaFinal);
    }
echo '</tr>';

echo '</table>';

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
