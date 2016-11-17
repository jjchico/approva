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

echo '<script>jQuery(document).ready(function($){$(\'#detallesProyectoTable\').tableScroll({height:200});});</script>';

echo '<h2>Proyectos <a href="#" onclick="goAddProject()" title="Crear Proyecto"><img src="css/images/add.png" alt="Crear Proyecto" /></a></h2>';
echo '';

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

//si vengo de seleccionar agrupamiento
if(isset($_POST['idAgrupamiento'])){
    $idAgrupamiento = $_POST['idAgrupamiento'];
}

//si vengo de seleccionar proyecto
if(isset($_POST['nombreProyecto'])){
    $nombreProyecto = $_POST['nombreProyecto'];
}

//si he solicitado eliminar el proyecto y sus calificaciones
if(isset($_POST['deleteProyecto'])){
    //seleccionamos proyectos
    $query="select * FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    $num=mysqli_num_rows($result);
    for($p=0;$p<$num;$p++){
        $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $idProyectoEliminar=$row['id'];
        //borramos las calificaciones de este id
        $queryDelCal="delete FROM `$tabla_calificaciones` where proyecto_id='$idProyectoEliminar'";
        $resultDelCal=mysqli_query($con_mysql,$queryDelCal)or die('ERROR:'.mysqli_error($con_mysql));
    }
    //borramos en la tabla proyectos
    $queryDelPro="delete FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
    $resultDelPro=mysqli_query($con_mysql,$queryDelPro)or die('ERROR:'.mysqli_error($con_mysql));
}

//si he solicitado replicar el proyecto
if(isset($_POST['numReplicas'])){
    //número de réplicas
    $numReplicas = $_POST['numReplicas'];
    //consulto características del proyecto
    $query="select * FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    $num=mysqli_num_rows($result);
    for($p=0;$p<$num;$p++){
        $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
        $estandarReplicar=$row['estandar_id'];
        $numItemsReplicar=$row['num'];
        $pesoReplicar=$row['peso'];
        $ccl=$row['ccl'];
        $cmct=$row['cmct'];
        $cd=$row['cd'];
        $caa=$row['caa'];
        $csyc=$row['csyc'];
        $siep=$row['siep'];
        $cec=$row['cec'];
        //ahora grabamos
        for($r=0;$r<$numReplicas;$r++){
            $replica = ''.$nombreProyecto.'_replica_'.$r.'';
            $queryReplica = "insert into `$tabla_proyectos` values(NULL,'$idAgrupamiento','$estandarReplicar','$replica',NOW(),'$numItemsReplicar','$pesoReplicar','$ccl','$cmct','$cd','$caa','$csyc','$siep','$cec')";
            $resultReplica = mysqli_query($con_mysql,$queryReplica)or die('ERROR:'.mysqli_error($con_mysql));
        }
    }//fin de for
    if($resultReplica){
        echo '<script>alert(\'Réplicas generadas\');</script>';
    }
}

//si he solicitado copiar el proyecto
if(isset($_POST['idAgrupamientoDestino'])){
    //agrupamiento de destino
    $idAgrupamientoDestino = $_POST['idAgrupamientoDestino'];
    //seleccionamos todos los registros del proyecto y lo vamos guardando en la base de datos con el id del agrupamiento de destino
    $query="SELECT * FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){
            for($n=0;$n<$num;$n++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $estandar_id = $row['estandar_id'];
                $numItems = $row['num'];
                $peso = $row['peso'];
                $ccl = $row['ccl'];
                $cmct = $row['cmct'];
                $cd = $row['cd'];
                $caa = $row['caa'];
                $csyc = $row['csyc'];
                $siep = $row['siep'];
                $cec = $row['cec'];
                //hacemos el insert para el agrupamiento de destino
                $queryCopia="INSERT INTO `$tabla_proyectos` (`id`, `agrupamiento_id`, `estandar_id`, `proyecto`, `fecha`, `num`, `peso`,
    `ccl`, `cmct`, `cd`, `caa`, `csyc`, `siep`, `cec`) VALUES (NULL, '$idAgrupamientoDestino', '$estandar_id','$nombreProyecto',now(),'$numItems', '$peso',
    '$ccl','$cmct','$cd','$caa','$csyc','$siep','$cec');";
                $resultCopia=mysqli_query($con_mysql,$queryCopia)or die('ERROR:'.mysqli_error($con_mysql));
            }//fin for
            if($resultCopia){
                echo '<script>alert("Se ha copiado el proyecto. Puede consultarlo seleccionando el agrupamiento");</script>';
            }
        }//fin if hay registros

}

//si he solicitado eliminar un estándar de un proyecto
if(isset($_POST['delete'])){
    $idEstandarEliminar=$_POST['delete'];
    $query="delete FROM `$tabla_proyectos` where id='$idEstandarEliminar'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
}

//select con agrupamientos
//consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){
            echo '<select id="selAgrupProyectos" name="selAgrupProyectos" onchange="listaProyectos()">';
            echo '<option value="0">Seleccione Agrupamiento</option>';
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                if($idAgrupamiento==$row['id']){
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
//select con los proyectos grabados en el agrupamiento seleccionado
if(isset($idAgrupamiento)){
    //consultamos los proyecos
    $query="SELECT distinct proyecto FROM `$tabla_proyectos` where agrupamiento_id = '$idAgrupamiento'";
    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
    $num=mysqli_num_rows($result);
    if($num>0){
        echo '<select id="selProyecto" name="selProyecto" onchange="listaDetallesProyecto(\''.$idAgrupamiento.'\')">';
        echo '<option value="0">Seleccione Proyecto</option>';
        for($p=0;$p<$num;$p++){
            $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
            if($nombreProyecto==$row['proyecto']){
                echo '<option value="'.$row['proyecto'].'" selected="selected">'.$row['proyecto'].'</option>';
            }else{
                echo '<option value="'.$row['proyecto'].'">'.$row['proyecto'].'</option>';
            }
        }
        echo '</select>';
    }else{
        echo '<p>No hay proyectos registrados aún para este agrupamiento.</p>';
        }
}

echo '<br/><br/>';

//presentamos información del planteamiento del proyecto y enlace a generación del documento
if(isset($nombreProyecto)){
    $query="SELECT * FROM `$tabla_proyectos` where proyecto='$nombreProyecto' and agrupamiento_id='$idAgrupamiento'";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){

            //inPlace para cambiar nombre de agrupamiento
                echo '<script>';
					echo '$(\'#spanNombreProyecto\').editInPlace({
						url: \'inplace.php\',
						params: \'script=project&idAgrupamiento='.$idAgrupamiento.'&table='.$tabla_proyectos.'&proyecto='.$nombreProyecto.'\',
						show_buttons: true,
						field_type: "text"
					});';
                echo '</script>';
            //fin inPlace



            echo 'Planteamiento actual del proyecto <b><span id="spanNombreProyecto">'.$nombreProyecto.'</span></b>';
            echo '<br/><br/>';
            echo '<table id="detallesProyectoTable" name="detallesProyectoTable">';
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
                    $idProyecto = $row['id'];
                    $estandar_id = $row['estandar_id'];
                    $queryEstandar = "select estandar FROM `$tabla_estandares` where id='$estandar_id'";
                    $resultEstandar = mysqli_query($con_mysql,$queryEstandar)or die('ERROR:'.mysqli_error($con_mysql));
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
                echo '<td style="text-align:center;">'.$row['num'].'</td>';

                //inPlace para cambiar peso del estándar en el proyecto
                echo '<script>';
					echo '$(\'#pesoEstandar_'.$n.'\').editInPlace({
						url: \'inplace.php\',
						params: \'script=projectPeso&idProyecto='.$idProyecto.'&table='.$tabla_proyectos.'\',
						show_buttons: true,
						field_type: "text"
					});';
                echo '</script>';
                //fin inPlace



                echo '<td style="text-align:center;"><span id="pesoEstandar_'.$n.'">'.$row['peso'].'</span> %</td>';
                echo '<td style="text-align:center;"><a href="#" title="Elimina estándar del Proyecto" onclick="eliminaProyecto(\''.$idProyecto.'\',\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')"><img src="css/images/delete.png" /></a></td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<br/>';
            echo '<p style="text-align:center;">';
            echo '<a href="#" onclick="generaDocumentoProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">Generar Documento</a>';
            echo '<br/><br/>';
            echo '<a href="#" onclick="goCalificarProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">Calificar Proyecto</a>';
            echo '<br/>';
            echo '<br/>';
            echo '<a href="#" onclick="generaRubricaProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">Generar Rúbrica (modelo 1)</a>';
            echo '<br/><br/>';
            echo '<a href="#" onclick="generaRubricaProyecto2(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">Generar Rúbrica (modelo 2)</a>';
            echo '<br/><br/>';
            echo '<a href="#" onclick="goInformeProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">';
            echo 'Informe de Proyecto';
            echo '</a>';
            echo '<br/>';
            echo '<br/>';
            echo '<a href="#" onclick="muestraInputReplica()">';
            echo 'Replicar Proyecto';
            echo '</a>';
            echo '<br/>';
            echo '<br/>';
            echo '<span id="numReplicas" name="numReplicas" style="display:none;">Veces a replicar: <input id="txtNumReplicas" name="txtNumReplicas" maxlength="2" size="2" onblur="replicarProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')" /></span>';
            //echo '<br/>';
            echo '<br/>';
            //copiar el proyecto a otro agrupamiento
                //consulta de agrupamientos para listar
                $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
                $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
                $num=mysqli_num_rows($result);
                if($num>0){
                    echo '<select style="width:20%" id="selAgrupCopiaProyecto" name="selAgrupCopiaProyecto" onchange="copiaProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">';
                    echo '<option value="0">Copiar el proyecto al agrupamiento</option>';
                    for($a=0;$a<$num;$a++){
                        $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                        if($idAgrupamiento!=$row['id']){
                            echo '<option value="'.$row['id'].'">'.$row['agrupamiento'].'</option>';
                        }
                    }
                    echo '</select><br/><br/>';
                }else{
                    echo '<p>No hay agrupamientos registrados</p>';
                }
                //fin listado agrupamientos
            //fin copiado proyecto a otro agrupamiento
            echo '<a style="color:red;" href="#" onclick="eliminarProyecto(\''.$nombreProyecto.'\',\''.$idAgrupamiento.'\')">';
            echo 'Eliminar Proyecto';
            echo '</a>';
            echo '</p>';
        }
}


?>
