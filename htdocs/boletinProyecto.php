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
    $query="SELECT * FROM `$tabla_agrupamientos` where id='$idAgrupamiento'";
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
    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by `alumno`";
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
                $queryCalif="SELECT `$tabla_calificaciones`.calificacion,`$tabla_proyectos`.peso,`$tabla_proyectos`.ccl,`$tabla_proyectos`.cmct,`$tabla_proyectos`.cd,`$tabla_proyectos`.caa,`$tabla_proyectos`.csyc,
                `$tabla_proyectos`.siep,`$tabla_proyectos`.cec, `$tabla_estandares`.estandar FROM `$tabla_calificaciones`,`$tabla_proyectos`,`$tabla_estandares`
                where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto='$nombreProyecto' and
                `$tabla_calificaciones`.proyecto_id = `$tabla_proyectos`.id and `$tabla_proyectos`.estandar_id = `$tabla_estandares`.id";
                $resultCalif=mysqli_query($con_mysql,$queryCalif)or die('ERROR:'.mysqli_error());
                $numCalif=mysqli_num_rows($resultCalif);
                //si hay calificaciones
                if($numCalif>0){
                    for($c=0;$c<$numCalif;$c++){
                        $rowCalif=mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);
                        echo '<tr>';
                            echo '<td style="border:#000000 thin solid;">';
                            echo $rowCalif['estandar'];
                            if($rowCalif['ccl']<>0){echo ' CCL ';}
                            if($rowCalif['cmct']<>0){echo ' CMCT ';}
                            if($rowCalif['cd']<>0){echo ' CD ';}
                            if($rowCalif['caa']<>0){echo ' CAA ';}
                            if($rowCalif['csyc']<>0){echo ' CSYC ';}
                            if($rowCalif['siep']<>0){echo ' SIEP ';}
                            if($rowCalif['cec']<>0){echo ' CEC ';}
                            echo '</td>';
                            echo '<td style="border:#000000 thin solid;text-align:center;">'.$rowCalif['calificacion'].'<br/>('.round((($rowCalif['calificacion']/$rowCalif['peso'])*100),2).')</td>';
                            $arrayCalif[]=$rowCalif['calificacion'];
                            //multiplico la competencia por el la calificación del estándar sobre 10
                            if($rowCalif['ccl']<>0){
                            $arrayCCL[]=$rowCalif['ccl']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['cmct']<>0){
                            $arrayCMCT[]=$rowCalif['cmct']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['cd']<>0){
                            $arrayCAA[]=$rowCalif['cd']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['caa']<>0){
                            $arrayCD[]=$rowCalif['caa']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['csyc']<>0){
                            $arrayCSYC[]=$rowCalif['csyc']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['siep']<>0){
                            $arraySIEP[]=$rowCalif['siep']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                            if($rowCalif['cec']<>0){
                            $arrayCEC[]=$rowCalif['cec']*($rowCalif['calificacion']/$rowCalif['peso'])*100;
                            }
                        echo '</tr>';
                    }//fin de for
                    echo '<tr>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;"><big><big>Calificación Total</big></big></th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;text-align:center;"><big><big>'.array_sum($arrayCalif).'</big></big></th>';
                    echo '</tr>';
                    //la información acerca de las competencias
                    //$sumaTotalComp = (array_sum($arrayCCL)+array_sum($arrayCMCT)+array_sum($arrayCD)+array_sum($arrayCAA)+
                    //    array_sum($arrayCSYC)+array_sum($arraySIEP)+array_sum($arrayCEC));
                    if($arrayCCL){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia en Comunicación Lingüística (CCL) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCCL)/count($arrayCCL))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arrayCMCT){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia Científica y Matemática (CMCT) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCMCT)/count($arrayCMCT))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arrayCD){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia Digital (CD) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCD)/count($arrayCD))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arrayCAA){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia Aprender a Aprender (CAA) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCAA)/count($arrayCAA))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arrayCSYC){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia Social y Cívica (CSYC) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCSYC)/count($arrayCSYC))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arraySIEP){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia Sentido e Iniciativa Emprendedora (SIEP) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arraySIEP)/count($arraySIEP))),2).'</th>';
                    echo '</tr>';
                    }
                    if($arrayCEC){
                    echo '<tr>';
                    echo '<td style="border:#000000 thin solid;">Calificación Competencia en Conciencia y Expresión Cultural (CEC) en el proyecto</td>';
                    echo '<th style="border:#000000 thin solid;background-color:#E0E0E0;text-align:center;">'.round(((array_sum($arrayCEC)/count($arrayCEC))),2).'</th>';
                    echo '</tr>';
                    }
                    unset($arrayCalif);
                    if($arrayCCL){unset($arrayCCL);}
                    if($arrayCCL){unset($arrayCMCT);}
                    if($arrayCCL){unset($arrayCD);}
                    if($arrayCCL){unset($arrayCAA);}
                    if($arrayCCL){unset($arrayCSYC);}
                    if($arrayCCL){unset($arraySIEP);}
                    if($arrayCCL){unset($arrayCEC);}
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
