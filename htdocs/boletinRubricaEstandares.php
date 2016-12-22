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


<?php

//forzamos codificación utf-8
header('Content-Type: text/html; charset=UTF-8');

//zona horaria por defecto
date_default_timezone_set('Europe/Madrid');

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

//recojo primeras variables
$idAgrupamiento=$_GET['idAgrupamiento'];
$nombreAgrupamiento=$_GET['nombreAgrupamiento'];
$fechaIni=$_GET['fechaIni'];
$fechaIniM=date("Y-m-d", strtotime($fechaIni) );
$fechaFin=$_GET['fechaFin'];
$fechaFinM=date("Y-m-d", strtotime($fechaFin) );
    
//recojo los id de los estándares
$query="SELECT distinct estandar_id FROM proyectos where agrupamiento_id='$idAgrupamiento' and (fecha between '$fechaIniM' and '$fechaFinM')";
$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
$num=mysqli_num_rows($result);
for($i=0;$i<$num;$i++){
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
    $arrayIdEstandar[]=$row['estandar_id'];
}
//fin recogida idEstándares
        
//selecciono alumnado del agrupamiento
$queryAlum="SELECT * from alumnado where agrupamiento_id = '$idAgrupamiento' order by alumno";
$resultAlum=mysqli_query($con_mysql,$queryAlum)or die('ERROR:'.mysqli_error());
$numAlum=mysqli_num_rows($resultAlum);
for($a=0;$a<$numAlum;$a++){
    $rowAlum = mysqli_fetch_array($resultAlum,MYSQLI_ASSOC);
    //array para nombres
    $alum[]=$rowAlum['alumno'];
    $idAlum[]=$rowAlum['id'];
}
//fin recogida alumnado
    
//comenzamos alumno por alumno
for($a=0;$a<count($alum);$a++){
    
    echo '<br/><h3 style="text-align:center;">Rúbrica Agrupada de Estándares. Período '.$fechaIni.' a '.$fechaFin.' para el agrupamiento '.$nombreAgrupamiento.'</h3>';
    
    $idCurrentAlumno = $idAlum[$a];
    echo '<p><b>'.$alum[$a].'</b></p>';  
    
    //comenzamos montaje tabla
    echo '<table border="1" style="margin:auto;width="98%;font-size:9px;">';
        echo '<tr>';
            echo '<th>Estándar de aprendizaje</th>';
            //consultamos todos los proyectos realizados para este agrupamiento y período
            $queryProyecto="SELECT distinct proyecto FROM proyectos where agrupamiento_id='$idAgrupamiento' and (fecha between '$fechaIniM' and '$fechaFinM')";
            $resultProyecto=mysqli_query($con_mysql,$queryProyecto)or die('ERROR:'.mysqli_error());
            $numProyecto=mysqli_num_rows($resultProyecto);
            $numColumns = $numProyecto;
            for($n=0;$n<$numProyecto;$n++){
                $rowProyecto=mysqli_fetch_array($resultProyecto,MYSQLI_ASSOC);
                echo '<th style="text-align:center;">'.$rowProyecto['proyecto'].'</th>';  
                //guardamos en array
                $arrayNombreProyecto[]=$rowProyecto['proyecto'];
            }
            
            echo '<th style="text-align:center;">Media</th>';
    
                echo '<th style="width:4%;text-align:center;">A</th>';
                echo '<th style="width:4%;text-align:center;">B</th>';
                echo '<th style="width:4%;text-align:center;">C</th>';
                echo '<th style="width:4%;text-align:center;">D</th>';
                echo '<th style="width:4%;text-align:center;">E</th>';
                echo '<th style="width:4%;text-align:center;">F</th>';
    
        echo '</tr>';
        for($e=0;$e<count($arrayIdEstandar);$e++){
            echo '<tr>';
                $idEstandarAgrup = $arrayIdEstandar[$e];
                $queryEstandar="select estandar from estandares where id='$idEstandarAgrup'";
                $resultEstandar=mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error());
                $rowEstandar=mysqli_fetch_array($resultEstandar,MYSQLI_ASSOC);
                echo '<td>'.$rowEstandar['estandar'].'</td>';
                //buscamos calificaciones para cada proyecto
                for($p=0;$p<count($arrayNombreProyecto);$p++){
                    //consulta para ver si hay calificación
                    $nombreProyectoCalif = $arrayNombreProyecto[$p];
                    $queryCalif="select proyectos.peso,calificaciones.calificacion from calificaciones,proyectos where proyectos.agrupamiento_id = '$idAgrupamiento' and (calificaciones.fecha between '$fechaIniM' and '$fechaFinM') and proyectos.estandar_id='$idEstandarAgrup' and proyectos.proyecto = '$nombreProyectoCalif' and calificaciones.alumno_id = '$idCurrentAlumno' and proyectos.id = calificaciones.proyecto_id";
                    $resultCalif=mysqli_query($con_mysql,$queryCalif)or die('ERROR:'.mysqli_error());
                    $numCalif=mysqli_num_rows($resultCalif);
                    if($numCalif>0){
                        $rowCalif = mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);
                        $califPondEst = round((($rowCalif['calificacion']/$rowCalif['peso'])*100),2);
                        echo '<td style="text-align:center;">'.$rowCalif['calificacion'].'<br/><b>('.$califPondEst.')</b></td>';
                        //guardo en array
                        $califCelda[]=$califPondEst;
                    }else{
                        echo '<td style="background:#d9f2e6;"></td>';    
                    }
                if(count($califCelda)>0){
                    $califMediaEstandar = round((array_sum($califCelda)/count($califCelda)),2);    
                }
                
                }//fin for de fila
                unset($califCelda);
                echo '<td style="text-align:center;"><b>'.$califMediaEstandar.'</b></td>';
                $arrayCalifMediaEstandar[] = $califMediaEstandar;
            
                $califMediaEstandarRubrica = round(($califMediaEstandar/2),0);    
            
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 5){echo '<b>X</b>';}
                echo '</td>';
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 4){echo '<b>X</b>';}
                echo '</td>';
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 3){echo '<b>X</b>';}
                echo '</td>';
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 2){echo '<b>X</b>';}
                echo '</td>';
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 1){echo '<b>X</b>';}
                echo '</td>';
                echo '<td style="width:4%;text-align:center;">';
                    if($califMediaEstandarRubrica == 0){echo '<b>X</b>';}
                echo '</td>';
            
            
            echo '</tr>';
            
        }
    
    echo '<tr><th>Media Estándares de Aprendizaje</th>';
            
    for($nc=0;$nc<$numColumns;$nc++){
        echo '<th></th>';
    }
            
        echo '<th style="text-align:center;">'.round((array_sum($arrayCalifMediaEstandar)/count($arrayCalifMediaEstandar)),2).'</th>';
    
        echo '<th style="width:4%;text-align:center;">A</th>';
        echo '<th style="width:4%;text-align:center;">B</th>';
        echo '<th style="width:4%;text-align:center;">C</th>';
        echo '<th style="width:4%;text-align:center;">D</th>';
        echo '<th style="width:4%;text-align:center;">E</th>';
        echo '<th style="width:4%;text-align:center;">F</th>';
            
    echo '</tr>';
    
    echo '</table>';
    //fin tabla
    
    echo '<p style="font-size:8px;text-align:justify;margin:8px;">';
            echo '<span>A: Demuestra total comprensión del problema. Todos los requerimientos de la tarea están incluidos en la respuesta.</span><br/>';
            echo '<span>B: Demuestra considerable comprensión del problema. Todos los requerimientos de la tarea están incluidos en la respuesta.</span><br/>';
            echo '<span>C: Demuestra comprensión parcial del problema. La mayor cantidad de requerimientos de la tarea están comprendidos en la respuesta.</span><br/>';
            echo '<span>D: Demuestra poca comprensión del problema. Muchos de los requerimientos de la tarea faltan en la respuesta.</span><br/>';
            echo '<span>E: No comprende el problema.</span><br/>';
            echo '<span>F: No responde. No intentó hacer la tarea.</span><br/>';
    echo '</p>';

unset($arrayCalifMediaEstandar);    
unset($califMediaEstandar);    
unset($arrayNombreProyecto);  
    
//el salto de página
echo '<p style="page-break-after:always"></p>';
    
}//fin alumnado //no olvides unset arrays //no olvides salto de página

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);
        
?>
