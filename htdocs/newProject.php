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

echo '<script>jQuery(document).ready(function($){$(\'#proyectoTable\').tableScroll({height:200});});</script>';

echo '<h1>Registro de nuevo proyecto</h1>';

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

//si he solicitado eliminar un estándar de un proyecto
if(isset($_POST['delete'])){
    $idEstandarEliminar=$_POST['delete'];
    $query="delete FROM `$tabla_proyectos` where id='$idEstandarEliminar'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
}

//si hemos enviado formulario, grabamos
if(isset($_GET['save'])){    
    //recojo variables 
    $idAgrupamiento = $_POST['selAgrupCreaProyecto'];
    $nombreProyecto = $_POST['nombreProyecto'];
    $idEstandar = $_POST['selEstandarCreaProyecto'];
    $numItems = $_POST['txtNumItems'];
    $pesoEstandar = $_POST['txtPesoEstandar'];
    if(isset($_POST['cb_CCL'])){$ccl = '1';}else{$ccl = '0';}
    if(isset($_POST['cb_CMCT'])){$cmct = '1';}else{$cmct = '0';}
    if(isset($_POST['cb_CD'])){$cd = '1';}else{$cd = '0';}
    if(isset($_POST['cb_CAA'])){$caa = '1';}else{$caa = '0';}
    if(isset($_POST['cb_CSYC'])){$csyc = '1';}else{$csyc = '0';}
    if(isset($_POST['cb_SIEP'])){$siep = '1';}else{$siep = '0';}
    if(isset($_POST['cb_CEC'])){$cec = '1';}else{$cec = '0';}
    //grabo en tabla estandaresProyecto
    $query="INSERT INTO `$tabla_proyectos` (`id`, `agrupamiento_id`, `estandar_id`, `proyecto`, `fecha`, `num`, `peso`,
    `ccl`, `cmct`, `cd`, `caa`, `csyc`, `siep`, `cec`) VALUES (NULL, '$idAgrupamiento', '$idEstandar','$nombreProyecto',now(),'$numItems', '$pesoEstandar',
    '$ccl','$cmct','$cd','$caa','$csyc','$siep','$cec');";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    
    if($result){
        echo '<script>alert("Se ha grabado la información en el proyecto. Puede darlo por concluido o seguir grabando en el mismo proyecto otro estándar de aprendizaje");</script>';
    }
}

//si venimos de seleccionar agrupamiento
if(isset($_POST['idAgrupamiento'])){
    $idAgrupamiento = $_POST['idAgrupamiento'];
}

//si hemos enviado el nombre del proyecto para comprobar si existe ya el proyecto (función checkProject())
if(isset($_POST['nombreProyecto'])){
    $nombreProyecto = $_POST['nombreProyecto'];
    //vamos a comprobar si existe el proyecto
    $query="SELECT * FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    $num=mysqli_num_rows($result);
    if($num>0){//si existe cargamos valores en variables para ofrecer tabla con información de cómo va el proyecto
        echo '<span>Planteamiento actual del proyecto <b>'.$nombreProyecto.'</b></span>';
            echo '<br/><br/>';
            echo '<table id="proyectoTable" border="1" style="margin:auto;width:80%;font-size:10px;" >';
            echo '<tr>';
            echo '<th>Estándar de aprendizaje</th>';
            echo '<th>Competencias Clave</th>';
            echo '<th>Número de ítems</th>';
            echo '<th>Peso del estándar</th>';
            echo '<th>Acción</th>';
            echo '</tr>';
            for($n=0;$n<$num;$n++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<tr>';
                echo '<td>';
                    $estandar_id = $row['estandar_id'];
                    $queryEstandar = "select estandar FROM `$tabla_estandares` where id='$estandar_id'";
                    $resultEstandar = mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error());
                    $rowEstandar = mysqli_fetch_array($resultEstandar,MYSQLI_ASSOC);
                    echo $rowEstandar['estandar'];
                echo '</td>';
                echo '<td>';
                    if($row['ccl']=='1') echo ' CCL ';
                    if($row['cmct']=='1') echo ' CMCT ';
                    if($row['cd']=='1') echo ' CD ';
                    if($row['caa']=='1') echo ' CAA ';
                    if($row['csyc']=='1') echo ' CSYC ';
                    if($row['siep']=='1') echo ' SIEP ';
                    if($row['cec']=='1') echo ' CEC ';
                echo '</td>';
                echo '<td>'.$row['num'].'</td>';
                echo '<td>'.$row['peso'].' %</td>';
                echo '<td style="text-align:center;"><a href="#" title="Elimina estándar del Proyecto" onclick="eliminaProyectoDesdeCrear(\''.$row['id'].'\',\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')"><img src="css/images/delete.png" /></a></td>';
                echo '</tr>';
            }
            echo '</table><br/><br/>';
    }
}

echo '<form id="formNewProject" name="formNewProject">';
//select con agrupamientos
//consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){            
            echo '<select id="selAgrupCreaProyecto" name="selAgrupCreaProyecto" onchange="listaEstandaresCreaProyecto()">';
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

echo '<br/><br/>';

//nombre del proyecto
echo '<span>Nombre del proyecto:</span>';
echo '<br/>';
if(isset($nombreProyecto)){
    echo '<input type="text" id="nombreProyecto" name="nombreProyecto" size="156" value="'.$nombreProyecto.'" onblur="checkProject()" />';
}else{
    echo '<input type="text" id="nombreProyecto" name="nombreProyecto" size="156" onblur="checkProject()" />';
}
echo '<br/><br/>';

if(isset($nombreProyecto)){
    //seleccionar estándar de aprendizaje
    $query="SELECT * FROM `$tabla_estandares` where agrupamiento_id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
    $num=mysqli_num_rows($result);
    if($num>0){
        echo '<select id="selEstandarCreaProyecto" name="selEstandarCreaProyecto" onchange="estandarToText2()">';
        echo '<option value="0">Seleccione Estándar</option>';
            for($e=0;$e<$num;$e++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<option value="'.$row['id'].'" title="'.$row['estandar'].'">'.$row['estandar'].'</option>';
            }
        echo '</select>';
        echo '<br/><br/>';
        
        //el área de texto para presentar el estándar seleccionado
        echo '<textarea name="taEstandar" id="taEstandar" cols="146" rows="4"></textarea>';
        
        //el peso del estándar en el proyecto
        echo '<br/><br/>';
        echo '<span>Peso del estándar (en tanto por ciento): </span>';
        echo '<input type="text" id="txtPesoEstandar" name="txtPesoEstandar" size="5" maxlength="5" />';
        
        //los checkbox con las competencias clave
        echo '<br/><br/>';
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
            
        //el número de ítems a proponer para trabajar este estándar
        echo '<br/><br/>';
        echo '<span>Número de ítems: </span>';
        echo '<input type="text" id="txtNumItems" name="txtNumItems" size="2" maxlength="2" />';
            
        //el enlace para grabar
        echo '<br/><br/>';
        echo '<a href="#" onclick="saveProject()">Grabar Proyecto</a>';
        
    }else{
        echo '<p>No hay estándares registrados</p>';
    }
    echo '<br/><br/>';
}

echo '</form>';

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
