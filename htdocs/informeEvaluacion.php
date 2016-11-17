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

echo '<script>$(function(){$( "#fechaIni" ).datepicker({dateFormat:\'dd-mm-yy\',firstDay: 1});});</script>';
echo '<script>$(function(){$( "#fechaFin" ).datepicker({dateFormat:\'dd-mm-yy\',firstDay: 1});});</script>';

echo '<h2 style="text-align:center;">Informes de Evaluación por Proyectos</h2>';

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


if(isset($_POST['idAgrupamiento'])){
    $idAgrupamiento=$_POST['idAgrupamiento'];
}

if(isset($_POST['fechaIni'])){
    $fechaIni=$_POST['fechaIni'];
    $fechaIniM=date("Y-m-d", strtotime($fechaIni) );
}

if(isset($_POST['fechaFin'])){
    $fechaFin=$_POST['fechaFin'];
    $fechaFinM=date("Y-m-d", strtotime($fechaFin) );
}

//comienzo de formulario
echo '<form id="formInformeEvaluacion" name="formInformeEvaluacion" >';

//select con agrupamientos
//consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){
            echo '<select id="selAgrupInformeEvaluacion" name="selAgrupInformeEvaluacion">';
            echo '<option value="0">Seleccione Agrupamiento</option>';
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                if(isset($idAgrupamiento)&&$idAgrupamiento==$row['id']){
                    echo '<option value="'.$row['id'].'" selected="selected">'.$row['agrupamiento'].'</option>';
                }else{
                    echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';
                }
            }
            echo '</select>';
        }else{
            echo '<p>No hay agrupamientos registrados</p>';
        }
//fin listado agrupamientos

//período de evaluación
if(isset($fechaIni)){
    echo '<p>Inicio Período: <input type="text" id="fechaIni" name="fechaIni" value="'.$fechaIni.'"></p>';
}else{
    echo '<p>Inicio Período: <input type="text" id="fechaIni" name="fechaIni"></p>';
}
if(isset($fechaFin)){
    echo '<p>Fin Período: <input type="text" id="fechaFin" name="fechaFin" value="'.$fechaFin.'" onchange="listaProyectosEvaluacion()"></p>';
}else{
    echo '<p>Fin Período: <input type="text" id="fechaFin" name="fechaFin" onchange="listaProyectosEvaluacion()"></p>';
}

//listado de proyectos realizado durante el período seleccionado
if(isset($idAgrupamiento)&&isset($fechaIni)&&isset($fechaFin)){
    //seleccionamos proyectos realizados

    //$query="SELECT distinct proyecto FROM `$tabla_proyectos` where agrupamiento_id='$idAgrupamiento' and fecha <='$fechaFin' and fecha >='$fechaIni'";
    $query="SELECT distinct proyecto FROM `$tabla_proyectos` where agrupamiento_id='$idAgrupamiento' and (fecha between '$fechaIniM' and '$fechaFinM')";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    $num=mysqli_num_rows($result);
    if($num>0){
        echo '<table style="margin:auto;width="90%;">';
        echo '<tr><th>Proyecto</th><th>Peso en la evaluación (%)</th></tr>';
        for($p=0;$p<$num;$p++){
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            echo '<tr>';
                echo '<td><input type="checkbox" id="cb_'.$p.'" name="cb_'.$p.'" />'.$row['proyecto'].'</td>';
                echo '<td>';
                echo '<input type="text" size="5" maxlength="5" id="txt_Peso_'.$p.'" name="txt_Peso_'.$p.'" value="0" disabled="disabled" />';
                echo '<input type="hidden" size="5" maxlength="5" id="hid_Peso_'.$p.'" name="hid_Peso_'.$p.'" />';
                echo '</td>';
            echo '</tr>';
        }
        echo '<tr><th>Total</th><th><input type="text" name="txtTotalPeso" id="txtTotalPeso" disabled="disabled" /></th></tr>';
        echo '</table>';
        echo '<br/><br/>';
        echo '<span>Asignar peso a los proyectos seleccionados (%): </span>';
        echo '<input type="text" name="txtAsignaPeso" id="txtAsignaPeso" onblur="asignaPeso(\''.$num.'\')" size="5" maxlength="5" />';
        //enlace para generar informe de evaluación
        echo '<br/><br/>';
        echo '<a href="#" onclick="generaInformeEval()">Generar Informe de Evaluación</a>';
    }else{
        echo '<p>No se han realizado proyectos durante el período seleccionado para este agrupamiento</p>';
    }
}

//fin de formulario
echo '</form>';

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
