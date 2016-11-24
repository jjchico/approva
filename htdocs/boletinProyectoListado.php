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
    $numEstandares = $_GET['numEstandares'];

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


    //datos sobre el agrupamiento
    $query="SELECT * FROM `$tabla_agrupamientos` where id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    $num=mysqli_num_rows($result);
    if($num>0){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $agrupamiento = $row['agrupamiento'];
        $curso = $row['curso'];
        $materia = $row['materia'];
        $nivel = $row['nivel'];
    }

    //encabezamiento del informe
    echo '<h2 style="margin:auto;text-align:center;">Informe Global de Proyecto</h2>';
    echo '<br/><table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
    echo '<tr><th style="border:#000000 thin solid;background-color:#cccccc;">Proyecto: <b>'.$nombreProyecto.'</b></th>';
    echo '<th style="border:#000000 thin solid;background-color:#cccccc;">Fecha de generación del informe: '.date('d-m-Y').'</th></tr></table>';
    echo '<table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
    echo '<tr><td style="border:#000000 thin solid;">Agrupamiento: '.$materia.' '.$curso.' '.$nivel.'</td></tr></table>';

    //encabezamiento tabla del informe
    echo '<table style="margin:auto;border:#000000 thin solid;font-size:11px;width:90%;border-collapse: collapse;">';
    echo '<thead>';
        echo '<tr>';
            echo '<th>Alumno</th>';
            for($c=0;$c<$numEstandares;$c++){
                echo '<th style="text-align:center;">Estandar '.($c+1).'</th>';
            }
            echo '<th style="text-align:center;">Calificación</th>';
            echo '<th style="text-align:center;">CCL</th><th style="text-align:center;">CMCT</th><th style="text-align:center;">CD</th><th style="text-align:center;">CAA</th><th style="text-align:center;">CSYC</th><th style="text-align:center;">SIEP</th><th style="text-align:center;">CEC</th>';
        echo '</tr>';
    echo '</thead>';

    //seleccionamos lista de alumnos
    //consulta alumnado agrupamiento
    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by `alumno`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){//si hay alumnado comenzamos
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumno = $row['id'];

                //datos para cada alumno

                echo '<tr><td style="border:#000000 thin solid;">'.$row['alumno'].'</td>';

                //seleccionamos las calificaciones que existan de este alumno en este proyecto
                $queryCalif="SELECT `$tabla_calificaciones`.calificacion,`$tabla_proyectos`.peso,`$tabla_proyectos`.ccl,`$tabla_proyectos`.cmct,`$tabla_proyectos`.cd,`$tabla_proyectos`.caa,`$tabla_proyectos`.csyc,
                `$tabla_proyectos`.siep,`$tabla_proyectos`.cec, `$tabla_estandares`.estandar FROM `$tabla_calificaciones`,`$tabla_proyectos`,`$tabla_estandares`
                where `$tabla_calificaciones`.alumno_id='$idAlumno' and `$tabla_calificaciones`.proyecto='$nombreProyecto' and
                `$tabla_calificaciones`.proyecto_id = `$tabla_proyectos`.id and `$tabla_proyectos`.estandar_id = `$tabla_estandares`.id";
                $resultCalif=mysqli_query($con_mysql,$queryCalif)or die('ERROR:'.mysqli_error($con_mysql));
                $numCalif=mysqli_num_rows($resultCalif);
                //si hay calificaciones
                if($numCalif>0){
					// inicializa arrays para acumular calificaciones de
					// competencias
					$arrayCCL=array(); $arrayCMCT=array(); $arrayCAA=array();
					$arrayCD=array(); $arrayCSYC=array(); $arraySIEP=array();
					$arrayCEC=array();

                    for($c=0;$c<$numCalif;$c++){
                        $rowCalif=mysqli_fetch_array($resultCalif,MYSQLI_ASSOC);

                        //meto el texto del estándar en un array para ofrecerlo luego a pie de página
                        if($a==0){
                            $arrayTextoEstandar[] = $rowCalif['estandar'];
                        }


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
                        echo '</td>';
                    }//fin de for

                    echo '<th style="border:#000000 thin solid;background-color:#cccccc;text-align:center;"><big><big>'.array_sum($arrayCalif).'</big></big></th>';

                    //la información acerca de las competencias
                    //$sumaTotalComp = (array_sum($arrayCCL)+array_sum($arrayCMCT)+array_sum($arrayCD)+array_sum($arrayCAA)+
                    //    array_sum($arrayCSYC)+array_sum($arraySIEP)+array_sum($arrayCEC));

                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCCL){
                            echo round(((array_sum($arrayCCL)/count($arrayCCL))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCMCT){
                            echo round(((array_sum($arrayCMCT)/count($arrayCMCT))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCD){
                            echo round(((array_sum($arrayCD)/count($arrayCD))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCAA){
                            echo round(((array_sum($arrayCAA)/count($arrayCAA))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCSYC){
                            echo round(((array_sum($arrayCSYC)/count($arrayCSYC))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arraySIEP){
                            echo round(((array_sum($arraySIEP)/count($arraySIEP))),2);
                        }
                    echo '</td>';
                    echo '<td style="border:#000000 thin solid;text-align:center;">';
                        if($arrayCEC){
                            echo round(((array_sum($arrayCEC)/count($arrayCEC))),2);
                        }
                    echo '</td>';
                    unset($arrayCalif);
                    if($arrayCCL){unset($arrayCCL);}
                    if($arrayCMCT){unset($arrayCMCT);}
                    if($arrayCD){unset($arrayCD);}
                    if($arrayCAA){unset($arrayCAA);}
                    if($arrayCSYC){unset($arrayCSYC);}
                    if($arraySIEP){unset($arraySIEP);}
                    if($arrayCEC){unset($arrayCEC);}
                }else{

                    for($c=0;$c<$numEstandares;$c++){
                        echo '<th style="text-align:center;"></th>';
                    }
                    echo '<th style="text-align:center;">Sin Calificación</th>';
                    echo '<th style="text-align:center;"></th><th style="text-align:center;"></th><th style="text-align:center;"></th><th style="text-align:center;"></th><th style="text-align:center;"></th><th style="text-align:center;"></th><th style="text-align:center;"></th>';
                }
                echo '</tr>';

            }//fin de for alumno

            echo '</table>';
            
            //pie de tabla
            echo '<p style="text-align:justify;margin-left:5%;">';
            for($e=0;$e<count($arrayTextoEstandar);$e++){
                echo '<span style="font-size:10px;"><b>Estándar '.($e+1).':</b> '.$arrayTextoEstandar[$e].'</span><br/>';
            }
            echo '</p>';



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
