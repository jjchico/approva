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

$idAgrupamiento = $_GET['idAgrupamiento'];
$nombreProyecto = $_GET['nombreProyecto'];

//datos del proyecto para presentar las rúbricas

//montar tabla: por filas, los alumnos, por columnas, las casillas de la rúbrica
$queryEstandares="SELECT estandares.estandar,proyectos.id,proyectos.peso FROM estandares,proyectos WHERE proyectos.proyecto = '$nombreProyecto' and 
proyectos.agrupamiento_id = '$idAgrupamiento' and proyectos.estandar_id = estandares.id";
$resultEstandares=mysqli_query($con_mysql,$queryEstandares)or die('ERROR:'.mysqli_error());
$numEstandares=mysqli_num_rows($resultEstandares);
if($numEstandares>0){
    for($e=0;$e<$numEstandares;$e++){
        $rowEstandares = mysqli_fetch_array($resultEstandares,MYSQLI_ASSOC);
        echo '<h2>Rúbricas para el proyecto '.$nombreProyecto.'</h2>';
        echo '<p><b>Estándar de aprendizaje:</b> '.$rowEstandares['estandar'].'</p>';
        echo '<p><b>Peso en el proyecto:</b> '.$rowEstandares['peso'].' %</p>';
        $idProyecto = $rowEstandares['id'];
        $pesoProyecto = ($rowEstandares['peso']/10);

        //comienzo tabla para cada uno de los estándares de aprendizaje
        echo '<table border="1" style="border-collapse:collapse">';
            echo '<tr>';
                echo '<th style="width:40%;">Apellidos y nombre</th>';
                echo '<th style="width:10%;">A</th>';
                echo '<th style="width:10%;">B</th>';
                echo '<th style="width:10%;">C</th>';
                echo '<th style="width:10%;">D</th>';
                echo '<th style="width:10%;">E</th>';
                echo '<th style="width:10%;">F</th>';
            echo '</tr>';

            //datos para la lista de alumnos
            $query="SELECT * from alumnado where agrupamiento_id = '$idAgrupamiento' order by alumno";
            $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
            $num=mysqli_num_rows($result);
            for($a=0;$a<$num;$a++){
                $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<tr>';
                    echo '<td>'.$row['alumno'].'</td>';
                    $idAlumno = $row['id'];            
                    //compruebo si ya tiene nota para el estándar de aprendizaje en cuestión
                    $querySelect = "select calificacion from calificaciones where alumno_id='$idAlumno' and proyecto_id='$idProyecto' and 
                    proyecto='$nombreProyecto'";
                    $resultSelect = mysqli_query($con_mysql,$querySelect)or die('ERROR:'.mysqli_error());
                    $numSelect = mysqli_num_rows($resultSelect);
                    //si hay nota, la clasifico para colocarla en la celda correspondiente
                    if($numSelect>0){
                        $rowSelect=mysqli_fetch_array($resultSelect,MYSQLI_ASSOC);
                        $calificacionEstandar = ($rowSelect['calificacion']/($pesoProyecto))*10;
                        $calificacionEstandarRubrica = round(($calificacionEstandar/2),0);
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 5){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 4){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 3){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 2){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 1){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                        echo '<td style="text-align:center;">';
                            if($calificacionEstandarRubrica == 0){echo '<big><b>'.$calificacionEstandarRubrica.'</b></big>';echo '<br/>';echo '('.round($calificacionEstandar,2).')';}
                        echo '</td>';
                    }else{//no hay calificaciones -> sale en blanco la tabla
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>'; 
                    }    
                echo '</tr>';
        }
        echo '</table>';
        echo '<p style="font-size:8px">';
            echo '<span>A: Demuestra total comprensión del problema. Todos los requerimientos de la tarea están incluidos en la respuesta.</span><br/>';
            echo '<span>B: Demuestra considerable comprensión del problema. Todos los requerimientos de la tarea están incluidos en la respuesta.</span><br/>';
            echo '<span>C: Demuestra comprensión parcial del problema. La mayor cantidad de requerimientos de la tarea están comprendidos en la respuesta.</span><br/>';
            echo '<span>D: Demuestra poca comprensión del problema. Muchos de los requerimientos de la tarea faltan en la respuesta.</span><br/>';
            echo '<span>E: No comprende el problema.</span><br/>';
            echo '<span>F: No responde. No intentó hacer la tarea.</span><br/>';
        echo '</p>';
        
        //el salto de página
        echo '<p style="page-break-after:always"></p>';
        
    }//fin de for (estándar a estándar)
}//fin if estandares

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);

?>
