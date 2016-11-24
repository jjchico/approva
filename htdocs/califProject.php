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


//si hemos solicitado grabar notas/////////////////////////////////////////////////////////////////////////
if(isset($_POST['stringArrayIdProyecto'])){

        //recogemos nombre del proyecto
        $nombreProyecto = $_POST['nombreProyecto'];

        //recogemos id del agrupamiento
        $idAgrupamiento = $_POST['idAgrupamiento'];

        //recogemos array con los id de proyecto
        $stringArrayIdProyecto = ($_POST['stringArrayIdProyecto']);
        $arrayIdProyecto = explode('@',$stringArrayIdProyecto);

        //seleccionamos alumnado del agrupamiento
        $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        for($a=0;$a<$num;$a++){
            $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
            $idAlumno = $row['id'];

            //ya tenemos alumno por alumno, vamos a ir insertando en la base de datos

            //recogemos calificación y grabamos
            $numEstandares = count($arrayIdProyecto);
            for($n=0;$n<$numEstandares;$n++){
                //echo $arrayIdProyecto[$n];
                if(isset($_POST['txt_'.$idAlumno.'_'.$n.''])&&$_POST['txt_'.$idAlumno.'_'.$n.'']<>""){
                    $calificacion = $_POST['txt_'.$idAlumno.'_'.$n.''];
                    //compruebo si ya tiene nota
                    $querySelect = "select id FROM `$tabla_calificaciones` where alumno_id='$idAlumno' and proyecto_id='$arrayIdProyecto[$n]' and
                    proyecto='$nombreProyecto'";
                    $resultSelect = mysqli_query($con_mysql,$querySelect)or die('ERROR:'.mysqli_error($con_mysql));
                    $numSelect = mysqli_num_rows($resultSelect);
                    //si hay nota, edito
                    if($numSelect>0){
                        $rowSelect = mysqli_fetch_array($resultSelect,MYSQLI_ASSOC);
                        $idSelect = $rowSelect['id'];
                        $queryUpdate = "update `$tabla_calificaciones` set calificacion = '$calificacion' where id = '$idSelect'";
                        $resultUpdate = mysqli_query($con_mysql,$queryUpdate)or die('ERROR:'.mysqli_error($con_mysql));


                    }else{//si no hay nota, inserto
                        $queryInsert="insert into `$tabla_calificaciones` values(NULL,'$idAlumno','$arrayIdProyecto[$n]','$nombreProyecto','$calificacion',now())";
                        $resultInsert=mysqli_query($con_mysql,$queryInsert)or die('ERROR:'.mysqli_error($con_mysql));

                    }

                }//fin hay nota
            }//fin for estándares para un alumno
        }//fin for alumnos
        echo '<script>alert(\'Calificaciones Grabadas\');</script>';
}
//fin grabar notas////////////////////////////////////////////////////////////////////////////////////////

$idAgrupamiento = $_GET['idAgrupamiento'];
$nombreProyecto = $_GET['nombreProyecto'];

echo '<h1>Calificación de Proyecto: '.$nombreProyecto.'</h1>';

//montar tabla: por filas, los alumnos, por columnas, las casillas para guardar la calificación de cada estándar


//datos del proyecto para montar columnas
$query="SELECT `$tabla_estandares`.estandar,`$tabla_proyectos`.id FROM `$tabla_estandares`,`$tabla_proyectos` WHERE `$tabla_proyectos`.proyecto = '$nombreProyecto' and
`$tabla_proyectos`.agrupamiento_id = '$idAgrupamiento' and `$tabla_proyectos`.estandar_id = `$tabla_estandares`.id";
$result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
$numEstandares=mysqli_num_rows($result);
if($numEstandares>0){
    echo '<form id="formCalifica" name="formCalifica">';
    echo '<table>';
    echo '<tr>';
    echo '<td></td>';
    for($e=0;$e<$numEstandares;$e++){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        echo '<td>'.$row['estandar'].'</td>';
        $array_idProyecto[] = $row['id'];
    }
    //array to string
    $stringArray = implode('@',$array_idProyecto);
    echo '</tr>';
    //datos para la lista de alumnos
    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    $num=mysqli_num_rows($result);
    for($a=0;$a<$num;$a++){
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        echo '<tr><td>'.$row['alumno'].'</td>';
        $idAlumno = $row['id'];
        for($i=0;$i<$numEstandares;$i++){
            //compruebo si ya tiene nota
            $querySelect = "select calificacion FROM `$tabla_calificaciones` where alumno_id='$idAlumno' and proyecto_id='$array_idProyecto[$i]' and
            proyecto='$nombreProyecto'";
            $resultSelect = mysqli_query($con_mysql,$querySelect)or die('ERROR:'.mysqli_error($con_mysql));
            $numSelect = mysqli_num_rows($resultSelect);
            //si hay nota, edito
            if($numSelect>0){
                $rowSelect=mysqli_fetch_array($resultSelect,MYSQLI_ASSOC);
                echo '<td style="text-align:center"><input type="text" id="txt_'.$idAlumno.'_'.$i.'" name="txt_'.$idAlumno.'_'.$i.'" size="5" maxlength="5" value="'.$rowSelect['calificacion'].'" /></td>';
            }else{
                echo '<td style="text-align:center"><input type="text" id="txt_'.$idAlumno.'_'.$i.'" name="txt_'.$idAlumno.'_'.$i.'" size="5" maxlength="5" /></td>';
            }
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '</form>';
    echo '<br/><p style="text-align:center;"><a href="#" onclick="califica(\''.$idAgrupamiento.'\',\''.$stringArray.'\',\''.$nombreProyecto.'\')">Grabar Calificaciones</a></p>';

}//fin if estandares

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
