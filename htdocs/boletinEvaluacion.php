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
$idAgrupamiento=$_GET['selAgrupInformeEvaluacion'];
$fechaIni=$_GET['fechaIni'];
$fechaIniM=date("Y-m-d", strtotime($fechaIni) );
$fechaFin=$_GET['fechaFin'];
$fechaFinM=date("Y-m-d", strtotime($fechaFin) );

//recojo resto variables (pesos)
//seleccionamos proyectos realizados
$query="SELECT distinct proyecto FROM `$tabla_proyectos` where agrupamiento_id='$idAgrupamiento' and (fecha between '$fechaIniM' and '$fechaFinM')";
$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
$num=mysqli_num_rows($result);
if($num>0){
    for($p=0;$p<$num;$p++){
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            $arrayNombresProyectos[]=$row['proyecto'];
            if(isset($_GET['hid_Peso_'.$p.''])){
                $arrayPesosProyectos[]=$_GET['hid_Peso_'.$p.''];
            }
    }
}
$numProyectos=count($arrayNombresProyectos);
//ya tengo dos arrays, uno con los nombres de proyectos realizados en el período y otro con sus pesos para el informe

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

//seleccionamos el alumnado
//consulta alumnado agrupamiento
    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by `alumno`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){//si hay alumnado comenzamos
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumno = $row['id'];
                echo '<h2 style="margin:auto;text-align:center;">Informe de Evaluación</h2>';
                echo '<h3 style="margin:auto;text-align:center;">Período: Del '.$fechaIni.' al '.$fechaFin.'</h3>';

                echo '<br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr><td style="border:#000000 thin solid;">Agrupamiento: '.$materia.' '.$curso.' '.$nivel.'</td></tr>';
                echo '<tr><td style="border:#000000 thin solid;">Apellidos y nombre: <b>'.$row['alumno'].'</b></td></tr></table>';

                echo '<br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr><th style="border:#000000 thin solid;background-color:#cccccc;">Proyecto</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Calificación</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Peso del proyecto en la evaluación</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Calificación que aporta el proyecto</th>';
                echo '</tr>';

                //vamos a calcular notas de cada proyecto
                for($r=0;$r<$numProyectos;$r++){

                    $nombreProyecto=$arrayNombresProyectos[$r];
                    $pesoProyecto=($arrayPesosProyectos[$r]/100);

                    //seleccionamos las calificaciones que existan de este alumno en este proyecto
                    $queryCalif="SELECT `$tabla_calificaciones`.calificacion,`$tabla_proyectos`.peso FROM `$tabla_calificaciones`,`$tabla_proyectos`,estandares
                    where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto='$nombreProyecto' and
                    `$tabla_calificaciones`.proyecto_id = `$tabla_proyectos`.id and `$tabla_proyectos`.estandar_id = `$tabla_estandares`.id and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                    $resultCalif=mysqli_query($con_mysql,$queryCalif)or die('ERROR:'.mysqli_error());
                    $numCalif=mysqli_num_rows($resultCalif);
                    //si hay calificaciones
                    if($numCalif>0){
                        for($c=0;$c<$numCalif;$c++){
                            $rowCalif=mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);
                            //meto cada calificación de cada estándar del proyecto en un array
                            $arrayCalif[]=$rowCalif['calificacion'];
                        }//fin for de calificaciones de cada estándar de cada proyecto
                        $califProyecto=array_sum($arrayCalif);
                        unset($arrayCalif);
                        echo '<tr>';
                            echo '<td style="text-align:center;border:#000000 thin solid;">'.$nombreProyecto.'</td>';
                            echo '<td style="text-align:center;border:#000000 thin solid;">'.$califProyecto.'</td>';
                            echo '<td style="text-align:center;border:#000000 thin solid;">'.round($pesoProyecto,2).'</td>';
                            echo '<td style="text-align:center;border:#000000 thin solid;">'.round(($califProyecto*$pesoProyecto),2).'</td>';
                        echo '</tr>';
                        $arrayCalifProyectoPond[]=($califProyecto*$pesoProyecto);
                    }//fin if hay calificaciones
                }//fin de for proyectos
                if($numCalif>0){
                    echo '<tr><th></th><th></th><th style="border:#000000 thin solid;">Total peso: '.(array_sum($arrayPesosProyectos)/100).'</th><th style="border:#000000 thin solid;background-color:#cccccc;">Calificación Evaluación: '.round(array_sum($arrayCalifProyectoPond),2).'</th>';
                }else{
                    echo '<tr><th></th><th></th><th>Calificación Evaluación</th><th>No existen calificaciones</th>';
                }
                echo '</table>';

                unset($califProyecto);
                unset($arrayCalifProyectoPond);

                //vamos a presentar ahora información sobre estándares de aprendizaje
                $queryEstandar="select `$tabla_estandares`.estandar,`$tabla_calificaciones`.proyecto,`$tabla_calificaciones`.calificacion,`$tabla_proyectos`.peso FROM `$tabla_estandares`,`$tabla_proyectos`,`$tabla_calificaciones` where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto_id=`$tabla_proyectos`.id and `$tabla_proyectos`.estandar_id=`$tabla_estandares`.id and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultEstandar=mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error());
                $numEstandar=mysqli_num_rows($resultEstandar);
                if($numEstandar>0){
                    //montamos tabla
                    echo '<br/><br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                    echo '<tr>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Estándar de aprendizaje trabajados en el período</th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Proyecto en el que se trabaja</th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Calificación en el proyecto</th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Peso en el proyecto</th>';
                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Calificación sobre diez puntos</th>';
                        for($e=0;$e<$numEstandar;$e++){
                            $rowEstandar=mysqli_fetch_array($resultEstandar,MYSQLI_ASSOC);
                            echo '<tr>';
                                echo '<td style="border:#000000 thin solid;">'.$rowEstandar['estandar'].'</td>';
                                echo '<td style="border:#000000 thin solid;text-align:center;">'.$rowEstandar['proyecto'].'</td>';
                                echo '<td style="border:#000000 thin solid;text-align:center;">'.$rowEstandar['calificacion'].'</td>';
                                echo '<td style="border:#000000 thin solid;text-align:center;">'.$rowEstandar['peso'].'</td>';
                                echo '<td style="border:#000000 thin solid;text-align:center;">'.round((($rowEstandar['calificacion']/$rowEstandar['peso'])*100),2).'</td>';
                            echo '</tr>';
                        }
                    echo '</table>';
                }

                //vamos a presentar ahora información sobre competencias clave trabajadas
                echo '<br/><br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
                echo '<tr>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Competencia Clave</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Comunicación Lingüística</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Matemática, científica y tecnológica</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Digital</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Aprender a aprender</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Social y cívica</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Iniciativa y Espíritu Emprendedor</th>';
                echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Conciencia y expresión cultural</th>';
                echo '</tr>';

                echo '<tr>';

                echo '<td style="border:#000000 thin solid;text-align:center;background-color:#cccccc;">Ocasiones en las que se ha trabajado durante el período >>> </td>';

                $queryCCL="select `$tabla_proyectos`.ccl FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.ccl='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCCL=mysqli_query($con_mysql,$queryCCL)or die('ERROR:'.mysqli_error());
                $numCCL=mysqli_num_rows($resultCCL);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCCL.'</td>';
                $arrayComp[]=$numCCL;

                $queryCMCT="select `$tabla_proyectos`.cmct FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.cmct='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCMCT=mysqli_query($con_mysql,$queryCMCT)or die('ERROR:'.mysqli_error());
                $numCMCT=mysqli_num_rows($resultCMCT);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCMCT.'</td>';
                $arrayComp[]=$numCMCT;

                $queryCD="select `$tabla_proyectos`.cd FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.cd='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCD=mysqli_query($con_mysql,$queryCD)or die('ERROR:'.mysqli_error());
                $numCD=mysqli_num_rows($resultCD);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCD.'</td>';
                $arrayComp[]=$numCD;

                $queryCAA="select `$tabla_proyectos`.caa FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.caa='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCAA=mysqli_query($con_mysql,$queryCAA)or die('ERROR:'.mysqli_error());
                $numCAA=mysqli_num_rows($resultCAA);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCAA.'</td>';
                $arrayComp[]=$numCAA;

                $queryCSYC="select `$tabla_proyectos`.csyc FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.csyc='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCSYC=mysqli_query($con_mysql,$queryCSYC)or die('ERROR:'.mysqli_error());
                $numCSYC=mysqli_num_rows($resultCSYC);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCSYC.'</td>';
                $arrayComp[]=$numCSYC;

                $querySIEP="select `$tabla_proyectos`.siep FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.siep='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultSIEP=mysqli_query($con_mysql,$querySIEP)or die('ERROR:'.mysqli_error());
                $numSIEP=mysqli_num_rows($resultSIEP);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numSIEP.'</td>';
                $arrayComp[]=$numSIEP;

                $queryCEC="select `$tabla_proyectos`.cec FROM `$tabla_proyectos`,`$tabla_,calificaciones` where `$tabla_proyectos`.cec='1' and
                `$tabla_proyectos`.id=`$tabla_calificaciones`.proyecto_id and `$tabla_calificaciones`.alumno_id='$idAlumno' and (`$tabla_calificaciones`.fecha between '$fechaIniM' and '$fechaFinM')";
                $resultCEC=mysqli_query($con_mysql,$queryCEC)or die('ERROR:'.mysqli_error());
                $numCEC=mysqli_num_rows($resultCEC);
                echo '<td style="border:#000000 thin solid;text-align:center;">'.$numCEC.'</td>';
                $arrayComp[]=$numCEC;

                echo '</tr>';

                echo '<tr>';

                $numCompetencias=array_sum($arrayComp);
                $stringCompetencias=implode('@',$arrayComp);
                unset($arrayComp);

                echo '<td style="border:#000000 thin solid;text-align:center;background-color:#cccccc;">Frecuencia de trabajo durante el período >>> </td>';

                if($numCompetencias>0){
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCCL/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCMCT/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCD/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCAA/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCSYC/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numSIEP/$numCompetencias)*100,2).' %</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">'.round(($numCEC/$numCompetencias)*100,2).' %</td>';
                }else{
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;"></td>';
                }

                echo '</tr>';

                echo '</table>';

                //el gráfico
                echo '<br/><p style="margin:auto;text-align:center;"><img src="pie3d_plot.php?data='.$stringCompetencias.'" alt="" border="0"></p>';

                unset($arrayPorcentajes);

                //el salto de página
                echo '<p style="page-break-after:always"></p>';


            }//fin de for alumno
        }else{
            echo '<p>No hay alumnado matriculado en este agrupamiento</p>';
        }
        //fin listado agrupamientos

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
