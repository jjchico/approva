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

if(isset($_POST['idAlumno'])){

    $idAlumno = $_POST['idAlumno'];

    //consultamos nombre de alumno y agrupamiento en el que está matriculado
    $query = "SELECT $tabla_alumnado.alumno, $tabla_alumnado.agrupamiento_id, $tabla_agrupamientos.agrupamiento, $tabla_agrupamientos.curso, $tabla_agrupamientos.materia, $tabla_agrupamientos.nivel FROM `$tabla_alumnado`, `$tabla_agrupamientos` WHERE $tabla_alumnado.id='$idAlumno' and $tabla_alumnado.agrupamiento_id = $tabla_agrupamientos.id";

    $result = mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());

	if(mysqli_num_rows($result)>0){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $nombreAlumno = $row['alumno'];
        $agrupamiento = $row['agrupamiento'];
        $curso = $row['curso'];
        $materia = $row['materia'];
        $nivel = $row['nivel'];
        $idAgrupamiento = $row['agrupamiento_id'];

        echo '<h2>Ficha de Estudiante</h2>';

        //montamos select con todos los alumnos del agrupamiento
        echo '<select id="selAlumAgrup" name="selAlumAgrup" onchange="goFichaSelect()" style="width:200px;margin:auto;">';
        $queryAlum = "select * FROM `$tabla_alumnado` where agrupamiento_id='$idAgrupamiento' order by alumno";
        $resultAlum = mysqli_query($con_mysql,$queryAlum)or die('ERROR:'.mysqli_error());
        $numAlum = mysqli_num_rows($resultAlum);
        for($a=0;$a<$numAlum;$a++){
            $rowAlum = mysqli_fetch_array($resultAlum,MYSQLI_ASSOC);
            $idSelectAlum = $rowAlum['id'];
            $nombreSelectAlum = $rowAlum['alumno'];
            if($idAlumno==$idSelectAlum){
                echo '<option value="'.$idSelectAlum.'" selected="selected">'.$nombreSelectAlum.'</option>';
            }else{
                echo '<option value="'.$idSelectAlum.'">'.$nombreSelectAlum.'</option>';
            }
        }

        echo '</select><br/><br/>';

        echo '<img src="css/images/alum/'.$idAlumno.'.gif" alt="'.$idAlumno.'" style="border:1px solid orange;width:114;height:143;" />';

        //la asistencia
        $queryAsistencia = "select * FROM `$tabla_asistencia` where alumno_id = '$idAlumno'";
        $resultAsistencia = mysqli_query($con_mysql,$queryAsistencia)or die('ERROR:'.mysqli_error());
        $numAsistencia = mysqli_num_rows($resultAsistencia);
        if($numAsistencia>0){
            for($f=0;$f<$numAsistencia;$f++){
                $rowAsistencia = mysqli_fetch_array($resultAsistencia,MYSQLI_ASSOC);
                echo '<p>'.$rowAsistencia['fecha'].': '.$rowAsistencia['tipo'].'</p>';
            }
        }else{
            echo '<p>No existen faltas de asistencia</p>';
        }


    }
}

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
