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

date_default_timezone_set('Europe/Madrid');
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
    
    //seleccionamos lista de alumnos
    //consulta alumnado agrupamiento
    $query="SELECT * FROM `alumnado` where agrupamiento_id = '$idAgrupamiento' order by `alumno`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){//si hay alumnado comenzamos
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumno = $row['id'];
                echo '<h2 style="margin:auto;text-align:center;">Informe de Proyecto</h2>';
                echo '<br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr><th style="border:#000000 thin solid;background-color:#cccccc;">Proyecto: <b>'.$nombreProyecto.'</b></th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Fecha de generación: '.date('d-m-Y').'</th></tr></table>';
                echo '<table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr><td style="border:#000000 thin solid;">Agrupamiento: '.$materia.' '.$curso.' '.$nivel.'</td></tr>';
                echo '<tr><td style="border:#000000 thin solid;">Apellidos y nombre: '.$row['alumno'].'</td></tr></table>';	
                
                //tabla con estándares y notas
                echo '<br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr><th style="border:#000000 thin solid;background-color:#cccccc;">Estándar de aprendizaje</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Calificación parcial</th></tr>';
                //seleccionamos las calificaciones que existan de este alumno en este proyecto
                $queryCalif="SELECT calificaciones.calificacion,proyectos.peso,proyectos.ccl,proyectos.cmct,proyectos.cd,proyectos.caa,proyectos.csyc,
                proyectos.siep,proyectos.cec, estandares.estandar FROM calificaciones,proyectos,estandares 
                where calificaciones.alumno_id='$idAlumno' and calificaciones.proyecto='$nombreProyecto' and 
                calificaciones.proyecto_id = proyectos.id and proyectos.estandar_id = estandares.id";
                $resultCalif=mysqli_query($con_mysql,$queryCalif)or die('ERROR:'.mysqli_error());
                $numCalif=mysqli_num_rows($resultCalif);
                //si hay calificaciones
                if($numCalif>0){
                    for($c=0;$c<$numCalif;$c++){
                        $rowCalif=mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);
                        echo '<tr>';
                            echo '<td style="border:#000000 thin solid;">'.$rowCalif['estandar'].'</td>';
                            echo '<td style="border:#000000 thin solid;text-align:center;">'.$rowCalif['calificacion'].'<br/>('.round((($rowCalif['calificacion']/$rowCalif['peso'])*100),2).')</td>';
                            $arrayCalif[]=$rowCalif['calificacion'];
                            //multiplico la competencia por el peso del estándar
                            $arrayCCL[]=$rowCalif['ccl']*$rowCalif['peso'];
                            $arrayCMCT[]=$rowCalif['cmct']*$rowCalif['peso'];
                            $arrayCAA[]=$rowCalif['cd']*$rowCalif['peso'];
                            $arrayCD[]=$rowCalif['caa']*$rowCalif['peso'];
                            $arrayCSYC[]=$rowCalif['csyc']*$rowCalif['peso'];
                            $arraySIEP[]=$rowCalif['siep']*$rowCalif['peso'];
                            $arrayCEC[]=$rowCalif['cec']*$rowCalif['peso'];
                        echo '</tr>';
                    }//fin de for
                    echo '<tr>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;"><big><big>Calificación Total</big></big></th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;"><big><big>'.array_sum($arrayCalif).'</big></big></th>';
                    echo '</tr>';
                    //la información acerca de las competencias
                    $sumaTotalComp = (array_sum($arrayCCL)+array_sum($arrayCMCT)+array_sum($arrayCD)+array_sum($arrayCAA)+
                        array_sum($arrayCSYC)+array_sum($arraySIEP)+array_sum($arrayCEC));
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia en Comunicación Lingüística (CCL) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCCL)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia Científica y Matemática (CMCT) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCMCT)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia Digital (CD) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCD)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia Aprender a Aprender (CAA) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCAA)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia Social y Cívica (CSYC) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCSYC)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia Sentido e Iniciativa Emprendedora (SIEP) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arraySIEP)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Participación Competencia en Conciencia y Expresión Cultural (CEC) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;">'.round(((array_sum($arrayCEC)/$sumaTotalComp)*100),0).' %</th>';                    
                    echo '</tr>';
                    unset($arrayCalif);
                    unset($arrayCCL);
                    unset($arrayCMCT);
                    unset($arrayCD);
                    unset($arrayCAA);
                    unset($arrayCSYC);
                    unset($arraySIEP);
                    unset($arrayCEC);
                }else{
                    echo '<tr><th>No existen calificaciones para este alumno y proyecto</th><th></th></tr>';
                }
                echo '</table>';
                
                //el salto de página
                echo '<p style="page-break-after:always"></p>';
                
                
            }//fin de for alumno
        }else{
            echo '<p>No hay alumnado matriculado en este agrupamiento</p>';
        }
        //fin listado agrupamientos
   
        
    
    
    
}

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>

</body></html>

<?php 

?>
