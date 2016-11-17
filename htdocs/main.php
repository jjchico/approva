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

echo '<script>$(function(){$( "#fecha" ).datepicker({dateFormat:\'dd-mm-yy\',firstDay: 1});});</script>';

//config.php
require_once('config.php');

//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
if (!$con_mysql)
{
die("Connection error: " . mysqli_connect_error());
}

//si hemos mandado fecha
if(isset($_POST['fecha'])){


    //montamos fechas
    $date = explode('-', $_POST['fecha']);
    $mysqlDate = $date[2].'-'.$date[1].'-'.$date[0];
}

//si hemos mandado agrupamiento
if(isset($_POST['idAgrupamiento'])){
    $idAgrupamiento = $_POST['idAgrupamiento'];
    $nombreAgrupamiento = $_POST['nombreAgrupamiento'];
}

//si hemos mandado grabar diario
if(isset($_POST['textoDiario'])){
    $textoDiario = $_POST['textoDiario'];

    //vamos a consultar si ya hay contenido en la misma sesión
    $queryDiario="select * FROM `$tabla_diario` where agrupamiento_id='$idAgrupamiento' and sesion='$mysqlDate'";
    $resultDiario=mysqli_query($con_mysql,$queryDiario)or die('ERROR:'.mysqli_error());
    if(mysqli_num_rows($resultDiario)>0){
        $rowDiario=mysqli_fetch_array($resultDiario,MYSQLI_ASSOC);
        $idDiarioActualiza=$rowDiario['id'];
        //actualizamos
        $query="update `$tabla_diario` set diario = '$textoDiario' where id='$idDiarioActualiza';";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        if($result){echo '<script>alert(\'Contenido de la sesión actualizado\')</script>';}
    }else{
        //guardamos en base de datos
        $query="insert into `$tabla_diario` values(NULL,'$mysqlDate','$idAgrupamiento','$textoDiario');";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        if($result){echo '<script>alert(\'Contenido de la sesión guardado\')</script>';}
    }
}
//fin grabar diario

//si queremos eliminar entrada de diario futura
if(isset($_POST['idAviso'])){

    $idAvisoEliminar = $_POST['idAviso'];
    $queryEliminaAviso = "delete FROM `$tabla_diario` where id='$idAvisoEliminar'";
    $resultEliminaAviso=mysqli_query($con_mysql,$queryEliminaAviso)or die('ERROR:'.mysqli_error());
        if($resultEliminaAviso){echo '<script>alert(\'Entrada en diario eliminada\')</script>';}
}
//fin eliminar entrada de diario

//grabar nueva franja
if(isset($_POST['txtNuevaFranja'])){

//functions.php
require_once('functions.php');

    //recogemos variables
    $txtNuevaFranja = $_POST['txtNuevaFranja'];

    //grabamos en base de datos
    for($g=0;$g<5;$g++){
        $agrupGrabar = $_POST['selAgrup_'.$g.''];
        $nombreAgrupGrabar = $_POST['nombreAgrup_'.$g.''];
        $espacioGrabar = $_POST['txtEspacio_'.$g.''];
        $queryGrabaHorario = "insert into `$tabla_horario` values(NULL,'$txtNuevaFranja','$g','$agrupGrabar','$nombreAgrupGrabar','$espacioGrabar');";
        $resultGrabaHorario=mysqli_query($con_mysql,$queryGrabaHorario)or die('ERROR:'.mysqli_error());
    }
}
//fin grabar nueva franja

//selección de agrupamientos para tenerlos en arrays
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error());
        $num=mysqli_num_rows($result);
        if($num>0){
                for($m=0;$m<$num;$m++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idAgrups[]=$row['id'];
                $agrups[]=$row['agrupamiento'];
                }
        }
//fin de selección de agrupamientos


///////////////////////////////horario//////////////////////////////////////////////////////

if(isset($_POST['fecha'])){

//functions.php
require_once('functions.php');

    $diaEnSemana = nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])));
    echo '<p>Sesión Actual: <span style="background:yellow;"><b>'.$diaEnSemana.'</b></span> <input type="text" id="fecha" name="fecha" value="'.$_POST['fecha'].'" onchange="listaDiario()" style="text-align:center;"></p>';
}else{
    $diaEnSemana = nombreDia(date('l'));
    echo '<p>Sesión Actual: <span style="background:yellow;"><b>'.$diaEnSemana.'</b></span> <input type="text" id="fecha" name="fecha" value="'.date('d-m-Y').'" onchange="listaDiario()" style="text-align:center;"></p>';
}



//encabezado tabla horario
echo '<br/><table id="tablaHorario" name="tablaHorario">';
echo '<tr>';
echo '<th></th>';
if($diaEnSemana=='Lunes'){
    echo '<th style="text-align:center;background:yellow;">Lunes</th>';
}else{
    echo '<th style="text-align:center;">Lunes</th>';
}
if($diaEnSemana=='Martes'){
    echo '<th style="text-align:center;background:yellow;">Martes</th>';
}else{
    echo '<th style="text-align:center;">Martes</th>';
}
if($diaEnSemana=='Miércoles'){
    echo '<th style="text-align:center;background:yellow;">Miércoles</th>';
}else{
    echo '<th style="text-align:center;">Miércoles</th>';
}
if($diaEnSemana=='Jueves'){
    echo '<th style="text-align:center;background:yellow;">Jueves</th>';
}else{
    echo '<th style="text-align:center;">Jueves</th>';
}
if($diaEnSemana=='Viernes'){
    echo '<th style="text-align:center;background:yellow;">Viernes</th>';
}else{
    echo '<th style="text-align:center;">Viernes</th>';
}
echo '<th style="text-align:center;"><a href="#" onclick="escondeFranja()">Nueva Franja</a></th>';
echo '</tr>';
//fin encabezado tabla horario

//consulto si hay horario
$queryFranjas = "select distinct franja FROM `$tabla_horario`";
$resultFranjas = mysqli_query($con_mysql,$queryFranjas)or die('ERROR:'.mysqli_error());
$numFranjas = mysqli_num_rows($resultFranjas);
if($numFranjas>0){
    //monto bucle para ir consultando cada franja
    for($f=0;$f<$numFranjas;$f++){
        $rowFranjas = mysqli_fetch_array($resultFranjas,MYSQLI_ASSOC);
        $franja = $rowFranjas['franja'];
        echo '<tr>';
        echo '<td style="text-align:center;background:#dedede;"><b>'.$franja.'</b></td>';
        for($d=0;$d<5;$d++){
            $queryF = "select * FROM `$tabla_horario` where franja = '$franja' and dia='$d'";
            $resultF = mysqli_query($con_mysql,$queryF)or die('ERROR:'.mysqli_error());
            $rowF = mysqli_fetch_array($resultF,MYSQLI_ASSOC);
            if($rowF['agrupamiento']=='RECREO'){
                echo '<td style="text-align:center;background:#33cc33;color:white;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if(($rowF['agrupamiento']=='GUARDIA')){
                echo '<td style="text-align:center;background:#ff3333;color:white;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if(($rowF['agrupamiento']=='AT. PADRES')||($rowF['agrupamiento']=='AT. PADRES TUT.')){
                echo '<td style="text-align:center;background:#cc0088;color:white;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if($rowF['agrupamiento']=='HUECO'){
                echo '<td style="text-align:center;background:orange;color:#ff9900;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if(($rowF['agrupamiento']=='REUNIÓN DEP.')||($rowF['agrupamiento']=='CCP')){
                echo '<td style="text-align:center;background:#3366ff;color:white;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if(($rowF['agrupamiento']=='GUARDIA RECREO')||($rowF['agrupamiento']=='GUARDIA BIBLIO')){
                echo '<td style="text-align:center;background:#85e085;color:#ff3333;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }else if(($rowF['agrupamiento']=='REUNIÓN TUT.')||($rowF['agrupamiento']=='TUTORÍA')){
                echo '<td style="text-align:center;background:#ff99ff;color:white;vertical-align:middle;"><big><b>'.$rowF['agrupamiento'].'</b></big><br/><br/>'.$rowF['espacio'].'</td>';
            }
            else{
                //fabricamos fecha para mandarla en caso de que queramos ver sesión
                if(isset($_POST['fecha'])){
                    $fechaEnSesion = $_POST['fecha'];
                    //en función del día
                    if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Sábado"){
                        $dia = $date[0]+$d+2;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Domingo"){
                        $dia = $date[0]+$d+1;//echo $dia;echo '||';
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Lunes"){
                        $dia = $date[0]+$d;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Martes"){
                        $dia = $date[0]+$d-1;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Miércoles"){
                        $dia = $date[0]+$d-2;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Jueves"){
                        $dia = $date[0]+$d-3;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $date[1], $date[0], $date[2])))=="Viernes"){
                        $dia = $date[0]+$d-4;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $date[1], $dia, $date[2]));
                    }
                }else{
                    $fechaEnSesion = date('d-m-Y');
                    $mes = date('m');
                    $anyo = date('Y');
                    $diaSesion = date('d');
                    //en función del día
                    if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Sábado"){
                        $dia = $diaSesion+$d+2;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Domingo"){
                        $dia = $diaSesion+$d+1;//echo $dia;echo '||';
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Lunes"){
                        $dia = $diaSesion+$d;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Martes"){
                        $dia = $diaSesion+$d-1;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Miércoles"){
                        $dia = $diaSesion+$d-2;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Jueves"){
                        $dia = $diaSesion+$d-3;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }else if(nombreDia(date("l", mktime(0, 0, 0, $mes, $diaSesion, $anyo)))=="Viernes"){
                        $dia = $diaSesion+$d-4;
                        $fechaDestino=date("d-m-Y", mktime(0, 0, 0, $mes, $dia, $anyo));
                    }

                }
                echo '<td style="text-align:center;background:#92b9b9;"><big><b>'.$rowF['agrupamiento'].'</b></big>';
                print ' ';
                echo '<a href="#" onclick="verSesion(\''.$rowF['agrupamiento_id'].'\',\''.$rowF['agrupamiento'].'\',\''.$fechaDestino.'\');" title="Ver sesión"><img src="css/images/sesion.png" alt="Ver sesión" /></a>';
                echo '<br/>'.$rowF['espacio'].'</td>';
            }
        }//fin de for $d
        echo '<td style="text-align:center;background:#dedede;"><b>'.$franja.'</b></td>';
        echo '</tr>';
    }

    echo '<tr class="fila" style="display:none;">';
    echo '<td>';
    echo '<input type="text" maxlength="12" size="12" id="txtNuevaFranja" name="txtNuevaFranja" />';
    echo '</td>';
    for($d=0;$d<5;$d++){
            echo '<td>';
                echo '<select id="selAgrup_'.$d.'" name="selAgrup_'.$d.'">';
                    echo '<option value="0">Elija</option>';
                    if($num>0){
                        for($m=0;$m<$num;$m++){
                            echo '<option value="'.$idAgrups[$m].'">'.$agrups[$m].'</option>';
                        }
                    }
                    echo '<option value="88">HUECO</option>';
                    echo '<option value="89">GUARDIA</option>';
                    echo '<option value="90">GUARDIA RECREO</option>';
                    echo '<option value="91">GUARDIA BIBLIO</option>';
                    echo '<option value="92">REUNIÓN DEP.</option>';
                    echo '<option value="93">REUNIÓN TUT.</option>';
                    echo '<option value="94">CCP</option>';
                    echo '<option value="95">AT. PADRES</option>';
                    echo '<option value="96">AT. PADRES TUT.</option>';
                    echo '<option value="97">TUTORÍA</option>';
                    echo '<option value="98">RECREO</option>';
                    echo '<option value="99">OTRO</option>';
                echo '</select>';
                echo '<br/><br/>';
                echo '<input type="text" placeholder="Espacio / Comentarios" size="24" id="txtEspacio_'.$d.'" name="txtEspacio_'.$d.'" />';
            echo '</td>';
        }
    echo '<td style="text-align:center">';
    echo '<a href="#" onclick="grabaFranja()">Grabar Franja</a>';
    echo '</td>';
    echo '</tr>';

}else{
    echo '<tr>';
        echo '<td>';
            echo '<input type="text" maxlength="12" size="12" id="txtNuevaFranja" name="txtNuevaFranja" />';
        echo '</td>';

        for($d=0;$d<5;$d++){
            echo '<td>';
                echo '<select id="selAgrup_'.$d.'" name="selAgrup_'.$d.'">';
                    echo '<option value="0">Elija</option>';
                    if($num>0){
                        for($m=0;$m<$num;$m++){
                            echo '<option value="'.$idAgrups[$m].'">'.$agrups[$m].'</option>';
                        }
                    }
                    echo '<option value="88">HUECO</option>';
                    echo '<option value="89">GUARDIA</option>';
                    echo '<option value="90">GUARDIA RECREO</option>';
                    echo '<option value="91">GUARDIA BIBLIO</option>';
                    echo '<option value="92">REUNIÓN DEP.</option>';
                    echo '<option value="93">REUNIÓN TUT.</option>';
                    echo '<option value="94">CCP</option>';
                    echo '<option value="95">AT. FAMILIAS</option>';
                    echo '<option value="96">AT. FAMILIAS TUT.</option>';
                    echo '<option value="97">TUTORÍA</option>';
                    echo '<option value="98">RECREO</option>';
                    echo '<option value="99">OTRO</option>';
                echo '</select>';
                echo '<br/><br/>';
                echo '<input type="text" placeholder="Espacio / Comentarios" size="24" id="txtEspacio_'.$d.'" name="txtEspacio_'.$d.'" />';
            echo '</td>';
        }
    echo '<td style="text-align:center"><a href="#" onclick="grabaFranja()">Grabar Franja</a></td>';
    echo '</tr>';
}

//fin tabla
echo '</table>';

/////////////////////fin horario/////////////////////////////////////////////////////////////

///sesiones///////////
if(isset($_POST['nombreAgrupamiento'])){
    //el área de texto para presentar la sesión seleccionada
    echo '<span id="sesion">';
    echo '<p style="text-align:center;">Contenido de la sesión: <input size="20" style="text-align:center;" type="text" id="txtSesionDestino" name="txtSesionDestino" value="'.$_POST['fecha'].'" />&nbsp;<b><span id="spanNombreAgrup" name="spanNombreAgrup">'.$nombreAgrupamiento.'</span></b><input type="hidden" id="hidIdAgrupamiento" name="hidIdAgrupamiento" value="'.$idAgrupamiento.'" /></p>';
    //vamos a consultar si para este agrupamiento y fecha hay sesión guardada
    $queryDiario="select * FROM `$tabla_diario` where agrupamiento_id='$idAgrupamiento' and sesion='$mysqlDate'";
    $resultDiario=mysqli_query($con_mysql,$queryDiario)or die('ERROR:'.mysqli_error());
    if(mysqli_num_rows($resultDiario)>0){//si hay sesión, presento los datos
        $row=mysqli_fetch_array($resultDiario,MYSQLI_ASSOC);
        echo '<p style="text-align:center;"><textarea name="taDiario" id="taDiario" cols="120" rows="10">'.$row['diario'].'</textarea></p>';
    }else{
        echo '<p style="text-align:center;"><textarea name="taDiario" id="taDiario" cols="120" rows="10"></textarea></p>';
    }
    echo '<p style="text-align:center;"><a href="#" onclick="grabaSesion(\''.$nombreAgrupamiento.'\')">Grabar Sesión</a></p>';
    echo '</span>';
}
//fin sesiones

/////AVISOS////////////////////////
$queryAvisos = "select $tabla_diario.id, $tabla_diario.sesion, $tabla_diario.diario, `$tabla_agrupamientos`.agrupamiento FROM `$tabla_diario`, `$tabla_agrupamientos` where $tabla_diario.agrupamiento_id = $tabla_agrupamientos.id and $tabla_diario.sesion>= DATE_SUB(CURDATE(),INTERVAL 0 DAY) order by $tabla_diario.sesion";
$resultAvisos=mysqli_query($con_mysql,$queryAvisos)or die('ERROR:'.mysqli_error($con_mysql));
if(mysqli_num_rows($resultAvisos)>0){
    $numAvisos = mysqli_num_rows($resultAvisos);
    for($a=0;$a<$numAvisos;$a++){
        $rowAvisos = mysqli_fetch_array($resultAvisos,MYSQLI_ASSOC);
        $aviso = $rowAvisos['diario'];
        $avisoSesion = $rowAvisos['sesion'];
        $idAviso = $rowAvisos['id'];
        //montamos fechas
        $fechaAvisoDesmontada = explode('-', $avisoSesion);
        $fechaAviso = $fechaAvisoDesmontada[2].'-'.$fechaAvisoDesmontada[1].'-'.$fechaAvisoDesmontada[0];
        $avisoAgrupamiento = $rowAvisos['agrupamiento'];
        echo '<br/><span style="color:#cc0088;"><b>'.$fechaAviso.' |</b> '.$aviso.' | '.$avisoAgrupamiento.'</span>';
        echo '&nbsp;<a href="#" onclick="eliminaDiario(\''.$idAviso.'\')" title="Eliminar del diario"><img src="css/images/delete.png" alt="Eliminar del diario" /></a>';
        echo'<br/>';
    }
}

//FIN AVISOS/////////////////

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);


?>
