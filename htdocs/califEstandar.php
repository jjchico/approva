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

echo '<script>jQuery(document).ready(function($){$(\'#califEstandaresTable\').tableScroll({height:340});});</script>';

echo '<h2 style="text-align:center;">Calificación de estándares de Aprendizaje</h2>';

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

if(isset($_POST['idEstandar'])){
    $idEstandar = $_POST['idEstandar'];
}

//si hemos solicitado grabar notas/////////////////////////////////////////////////////////////////////////
if(isset($_GET['nombreAgrupamiento'])){

        //recogemos id del agrupamiento
        $idAgrupamiento = $_POST['selAgrup'];

        //nombre del agrupamiento (me sirve para montar el nombre del proyecto)
        $nombreAgrupamiento = $_GET['nombreAgrupamiento'];

        //recogemos nombre del proyecto
        if(isset($_POST['selProyecto'])&&($_POST['selProyecto'])!='N'){
            $idProyecto = $_POST['selProyecto'];
            $nombreProyecto = $_GET['nombreProyecto'];
        }else{
            $nombreProyecto = ''.$nombreAgrupamiento.'_'.date('d-m-Y h:i:s A').'';
        }


        //el id del estandar
        $idEstandar = $_POST['selEstandarCreaProyecto'];

        //las competencias
        if(isset($_POST['cb_CCL'])){$ccl = '1';}else{$ccl = '0';}
        if(isset($_POST['cb_CMCT'])){$cmct = '1';}else{$cmct = '0';}
        if(isset($_POST['cb_CD'])){$cd = '1';}else{$cd = '0';}
        if(isset($_POST['cb_CAA'])){$caa = '1';}else{$caa = '0';}
        if(isset($_POST['cb_CSYC'])){$csyc = '1';}else{$csyc = '0';}
        if(isset($_POST['cb_SIEP'])){$siep = '1';}else{$siep = '0';}
        if(isset($_POST['cb_CEC'])){$cec = '1';}else{$cec = '0';}

        //grabo en la tabla de proyectos si nunca hemos calificado antes este proyecto (no estoy editando ni cambiando notas)
        if(!isset($_POST['selProyecto'])||isset($_GET['nuevo'])){
            $queryGrabaProyecto="INSERT INTO `$tabla_proyectos` (`id`, `agrupamiento_id`, `estandar_id`, `proyecto`, `fecha`, `num`, `peso`,
        `ccl`, `cmct`, `cd`, `caa`, `csyc`, `siep`, `cec`) VALUES (NULL, '$idAgrupamiento', '$idEstandar','$nombreProyecto',now(),'1', '100',
        '$ccl','$cmct','$cd','$caa','$csyc','$siep','$cec');";
            $resultGrabaProyecto=mysqli_query($con_mysql,$queryGrabaProyecto)or die('ERROR:'.mysqli_error());
            //si ha grabado bien el proyecto, vamos a grabar ahora las notas
            if($resultGrabaProyecto){
                $idProyecto = mysqli_insert_id($con_mysql);
            }
        }
            //seleccionamos alumnado del agrupamiento
            $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
            $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
            $num=mysqli_num_rows($result);
            for($a=0;$a<$num;$a++){
                $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAlumno = $row['id'];
                //recogemos calificación y grabamos
                if(isset($_POST[''.$idAlumno.''])&&$_POST[''.$idAlumno.'']<>""){
                    $calificacion = $_POST[''.$idAlumno.''];
                    //compruebo si ya tiene nota
                    $querySelect = "select id FROM `$tabla_calificaciones` where alumno_id='$idAlumno' and proyecto_id='$idProyecto'";
                    $resultSelect = mysqli_query($con_mysql,$querySelect)or die('ERROR:'.mysqli_error());
                    $numSelect = mysqli_num_rows($resultSelect);
                    //si hay nota, edito
                    if($numSelect>0){
                        $rowSelect = mysqli_fetch_array($resultSelect,MYSQLI_ASSOC);
                        $idSelect = $rowSelect['id'];
                        $queryUpdate = "update `$tabla_calificaciones` set calificacion = '$calificacion' where id = '$idSelect'";
                        $resultUpdate = mysqli_query($con_mysql,$queryUpdate)or die('ERROR:'.mysqli_error());
                    }else{//si no hay nota, inserto
                        $queryInsert="insert into `$tabla_calificaciones` values(NULL,'$idAlumno','$idProyecto','$nombreProyecto','$calificacion',now())";
                        $resultInsert=mysqli_query($con_mysql,$queryInsert)or die('ERROR:'.mysqli_error());
                    }
                }//fin se envió nota
            }//fin de for
        echo '<script>alert(\'Calificaciones Grabadas\');</script>';
}//fin graba nota

//fin grabar notas////////////////////////////////////////////////////////////////////////////////////////

//si hemos seleccionado agrupamiento
if(isset($_POST['idAgrupamiento'])){
    $idAgrupamiento = $_POST['idAgrupamiento'];
}

if(isset($_POST['idProyecto'])){
    $idProyecto = $_POST['idProyecto'];
}


//inicio formulario
echo '<form id="formCalificaEstandar" name="formCalificaEstandar">';

//select con agrupamientos
//consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){
            echo '<p style="text-align:center;"><select id="selAgrup" name="selAgrup" onchange="listaEstandares2()"></p>';
            echo '<option value="0">Seleccione Agrupamiento</option>';
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                if(isset($idAgrupamiento)&&($idAgrupamiento==$row['id'])){
                    echo '<option value="'.$row['id'].'" selected="selected">'.$row['agrupamiento'].'</option>';
                }else{
                    echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';
                }
            }
            echo '</select>';
        }else{
            echo '<p style="text-align:center;">No hay agrupamientos registrados</p>';
        }
//fin listado agrupamientos


        //select con estándares de aprendizaje registrados para el agrupamiento

        //seleccionar estándar de aprendizaje
		if(isset($idAgrupamiento)) {
	        $query="SELECT * FROM `$tabla_estandares` where agrupamiento_id='$idAgrupamiento'";
	        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
	        $num=mysqli_num_rows($result);
	        if($num>0){
	            echo '<br/><br/><select id="selEstandarCreaProyecto" name="selEstandarCreaProyecto" onchange="estandarToText()">';
	            echo '<option value="0">Seleccione Estándar</option>';
	                for($e=0;$e<$num;$e++){
	                    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
	                    if(isset($idEstandar)&&($idEstandar==$row['id'])){
	                        echo '<option value="'.$row['id'].'" title="'.$row['estandar'].'" selected="selected">'.$row['estandar'].'</option>';
	                    }else{
	                        echo '<option value="'.$row['id'].'" title="'.$row['estandar'].'">'.$row['estandar'].'</option>';
	                    }
	                }
	            echo '</select>';
	            echo '<br/><br/>';
	        }
        }
            //el select para discriminar si calificaremos un proyecto nuevo o editaremos uno ya existente
            if(isset($idEstandar)){
                $queryP="SELECT * FROM `$tabla_proyectos` where agrupamiento_id='$idAgrupamiento' and estandar_id='$idEstandar' order by fecha";
                $resultP=mysqli_query($con_mysql,$queryP)or die('ERROR:'.mysqli_error());
                $numP=mysqli_num_rows($resultP);
                if($numP>0){//si ya se ha calificado antes este estándar, debemos elegir
                    echo '<select id="selProyecto" name="selProyecto" onchange="presentaCalifEstandar()">';
                    echo '<option value="0">Seleccione Acción</option>';
                    echo '<option value="N">Nueva calificación</option>';
                        for($p=0;$p<$numP;$p++){
                            $rowP=mysqli_fetch_array($resultP,MYSQLI_ASSOC);
                            if(isset($idProyecto)&&($idProyecto==$rowP['id'])){
                                echo '<option selected="selected" value="'.$rowP['id'].'">'.$rowP['proyecto'].'</option>';
                            }else if(isset($idProyecto)&&($idProyecto=='N')){
                                echo '<option selected="selected" value="N">Nueva calificación</option>';
                            }else{
                                echo '<option value="'.$rowP['id'].'">'.$rowP['proyecto'].'</option>';
                            }

                        }
                    echo '</select>';
                    echo '<br/><br/>';

                    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
                    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
                    $num=mysqli_num_rows($result);
                    if($num>0){
                        if(isset($idProyecto)){
                            echo '<br/><br/><table style="margin:auto;text-align:center;" id="califEstandaresTable" name="califEstandaresTable">';
                            echo '<tr><th>Alumno</th><th>Calificación</th>';
                        }
                        for($a=0;$a<$num;$a++){
                        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
                        $idAlumno = $row['id'];
                        $alumno = $row['alumno'];

                        if(isset($idProyecto)){//venimos de grabar
                            $querySelect = "select * FROM `$tabla_calificaciones` where alumno_id='$idAlumno' and proyecto_id='$idProyecto'";
                            $resultSelect = mysqli_query($con_mysql,$querySelect)or die('ERROR:'.mysqli_error());
                            $numSelect = mysqli_num_rows($resultSelect);
                            //si hay nota, la pongo
                            if($numSelect>0){
                                $rowSelect = mysqli_fetch_array($resultSelect,MYSQLI_ASSOC);
                                $hayNota = $rowSelect['calificacion'];
                                if($a%2==0){
                                    echo '<tr><td>'.$alumno.'</td><td><input type="text" value="'.$hayNota.'" maxlength="5" size="5" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                                }else{
                                    echo '<tr><td style="background-color: #dedede;">'.$alumno.'</td><td style="background-color: #dedede;"><input type="text" maxlength="5" size="5" value="'.$hayNota.'" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                                }
                            }else{//no hay nota, no la pongo
                                if($a%2==0){
                                    echo '<tr><td>'.$alumno.'</td><td><input type="text" maxlength="5" size="5" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                                }else{
                                    echo '<tr><td style="background-color: #dedede;">'.$alumno.'</td><td style="background-color: #dedede;"><input type="text" maxlength="5" size="5" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                                }
                            }
                        }
                        }//fin de for
                        echo '</table>';
                        if(isset($idProyecto)&&($idProyecto=='N')){
                            echo '<br/><p style="text-align:center;"><a href="#" onclick="calificaEstandarNuevo2()">Grabar Calificaciones</a></p>';
                        }else{
                            echo '<br/><p style="text-align:center;"><a href="#" onclick="calificaEstandar()">Grabar Calificaciones</a></p>';
                        }
                    }else{
                        echo '<p style="text-align:center;">No hay alumnado matriculado</p>';
                    }




                }else{//no se ha calificado nunca; primera vez
                    //los checkbox con las competencias clave
                    echo '<br/>';
                    echo '<span>Competencias clave que se trabajarán:</span>';
                    echo '<br/><br/>';
                    echo ' | ';
                    echo '<span>CCL:</span>';
                    echo '<input type="checkbox" id="cb_CCL" name="cb_CCL" />';
                    echo ' | ';
                    echo '<span>CMCT:</span>';
                    echo '<input type="checkbox" id="cb_CMCT" name="cb_CMCT" />';
                    echo ' | ';
                    echo '<span>CD:</span>';
                    echo '<input type="checkbox" id="cb_CD" name="cb_CD" />';
                    echo ' | ';
                    echo '<span>CAA:</span>';
                    echo '<input type="checkbox" id="cb_CAA" name="cb_CAA" />';
                    echo ' | ';
                    echo '<span>CSYC:</span>';
                    echo '<input type="checkbox" id="cb_CSYC" name="cb_CSYC" />';
                    echo ' | ';
                    echo '<span>SIEP:</span>';
                    echo '<input type="checkbox" id="cb_SIEP" name="cb_SIEP" />';
                    echo ' | ';
                    echo '<span>CEC:</span>';
                    echo '<input type="checkbox" id="cb_CEC" name="cb_CEC" />';
                    echo ' | ';
                    //fin competencias clave

                    //seleccionamos alumnado del agrupamiento
                    $query="SELECT * FROM `$tabla_alumnado` where agrupamiento_id = '$idAgrupamiento' order by alumno";
                    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
                    $num=mysqli_num_rows($result);
                    if($num>0){
                        echo '<br/><br/><table style="margin:auto;text-align:center;" id="califEstandaresTable" name="califEstandaresTable">';
                        echo '<tr><th>Alumno</th><th>Calificación</th>';
                        for($a=0;$a<$num;$a++){
                            $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
                            $idAlumno = $row['id'];
                            $alumno = $row['alumno'];
                            if($a%2==0){
                                echo '<tr><td>'.$alumno.'</td><td><input type="text" maxlength="5" size="5" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                            }else{
                                echo '<tr><td style="background-color: #dedede;">'.$alumno.'</td><td style="background-color: #dedede;"><input type="text" maxlength="5" size="5" id="'.$idAlumno.'" name="'.$idAlumno.'" /></td></tr>';
                            }
                        }
                        echo '</table>';
                        echo '<br/><p style="text-align:center;"><a href="#" onclick="calificaEstandarNuevo()">Grabar Calificaciones</a></p>';
                        echo '</form>';
                    }else{
                        echo '<p style="text-align:center;">No hay alumnado matriculado</p>';
                    }
                }//fin de else; primera vez que se califica














            }
            //fin acción nueva calificación o edición de calificación

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
