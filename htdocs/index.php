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

//si acabamos de subir el paquete, debemos configurarlo

// if (file_exists('instalacion/index.php'))
// 	{
// 	$self = str_replace( '/index.php','', strtolower( $_SERVER['PHP_SELF'] ) ). '/';
// 	header("Location: http://" . $_SERVER['HTTP_HOST'] . $self . "instalacion/index.php" );
// 	exit();
// 	}
//fin instalación

//config
require_once('config.php');
//functions.php
require_once('functions.php');

//conexión dataBase
$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD);
if (!$con_mysql) {
    echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
    echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
	echo "Compruebe que ha creado el archivo 'config.php' y que tiene "
	   . "los privilegios apropiados.";
    exit;
}

// Comprobamos que existe la base de datos
// $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='".DB_DATABASE."'";
// $result = mysqli_query($con_mysql, $query) or die(mysqli_error($con_mysql));
// $count = count(mysqli_fetch_row($result));
// if ($count === 0){

// Comprobamos que existe la base de datos
$do_install = False;
$database = DB_DATABASE;
$table_check = $tabla_user;
if ( ! dbExists($con_mysql, $database)) {
    echo "No existe la base de datos $database." . PHP_EOL;
    $do_install = True;
} else {
    // Seleccionamos la base de datos y comprobamos que está iniciada
    mysqli_select_db($con_mysql, $database) or die(mysqli_error($con_mysql));
    if ( ! tableExists($con_mysql, $table_check)) {
        echo "La base de datos $database existe pero está vacía." . PHP_EOL;
        $do_install = True;
    }
}
if ($do_install) {
	$current_dir = dirname($_SERVER['PHP_SELF']);
    echo "¿Primera instalación?" . PHP_EOL;
	echo 'Siga el enlace para hacer la <a href="' . $current_dir . '/install.php">instalación inicial del programa</a>' . PHP_EOL;
	exit;
}

/////////////////////////////////////////////////////////////////////user

if(isset($_POST['username'])){
    //get the posted values
    $username=$_POST['username'];
    $pass=sha1($_POST['password']);

    $sql="SELECT * FROM `$tabla_user` WHERE username='".$username."'";
    $result=mysqli_query($con_mysql,$sql) or die(mysqli_error($con_mysql));
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);

    //if username exists
    if(mysqli_num_rows($result)>0)
    {
        //compare the password
        if(strcmp($row['password'],$pass)==0)
        {
            //now set the session from here if needed
            $_SESSION['id']=$row['id'];
            $_SESSION['username']=$row['username'];
            //cargamos los datos del archivo personal
        }
        else
            header("Location:login.php");
    }
    else{
        header("Location:login.php"); //Invalid Login
    }
}


// if session is not set redirect the user
if(empty($_SESSION['id'])){
	header("Location:login.php");
}
/////////////////////////////////////////////////////////////////fin user

/*OPERATIONS DATABASE////////////////////////////////////////////////////*/

//add an Agrup to dataBase
if(isset($_POST['addAgrup'])){
    //recogemos datos formulario
    $nameAgrup = $_POST['txtNameAgrup'];
    $nameSubj = $_POST['txtNameSubj'];
    $cursoAgrup = $_POST['txtCursoAgrup'];
    $levelAgrup = $_POST['txtLevel'];
    //insertamos en base de datos
    $query="insert into `$tabla_agrupamientos` (agrupamiento, curso, materia, nivel) values('$nameAgrup','$cursoAgrup','$nameSubj','$levelAgrup')";
    $result=mysqli_query($con_mysql,$query) or die(mysqli_error($con_mysql));
}

if(isset($_POST['idAgrupamientoElimina'])){
    //recogemos datos formulario
    $idAgrupamientoElimina = $_POST['idAgrupamientoElimina'];
    //borramos el agrupamiento de la base de datos
    $query="DELETE FROM `$tabla_agrupamientos` WHERE `$tabla_agrupamientos`.id = '$idAgrupamientoElimina'";
    $result=mysqli_query($con_mysql,$query) or die(mysqli_error($con_mysql));
    //borramos estándares del agrupamiento de la base de datos
    $query="DELETE FROM `$tabla_estandares` WHERE `$tabla_estandares`.agrupamiento_id = '$idAgrupamientoElimina'";
    $result=mysqli_query($con_mysql,$query) or die(mysqli_error($con_mysql));
    //borramos proyectos del agrupamiento de la base de datos
        //primero seleccionamos proyectos del agrupamiento
        $query="SELECT * from `$tabla_proyectos` where `$tabla_proyectos`.agrupamiento_id = '$idAgrupamientoElimina'";
        $result=mysqli_query($con_mysql,$query) or die(mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){
            for($e=0;$e<$num;$e++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                $idProyectoElimina = $row['id'];
                //borramos las calificaciones de este proyecto
                $eliminaCal="DELETE FROM `$tabla_calificaciones` WHERE `$tabla_calificaciones.proyecto_id = '$idProyectoElimina'";
                $resultElimina=mysqli_query($con_mysql,$eliminaCal)or die('ERROR:'.mysqli_error($con_mysql));
            }

        }
        //ahora ya borramos los proyectos del agrupamiento
        $query="DELETE FROM `$tabla_proyectos` WHERE `$tabla_proyectos`.agrupamiento_id = '$idAgrupamientoElimina'";
	    $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
}



/*END OPERATIONS DATABASE////////////////////////////////////////////////*/

?>

<html>
<head>
	<title>APPROVA</title>
	<meta charset="utf-8">
	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen">
    <link rel="stylesheet" href="css/jquery-ui.css" type="text/css" media="screen">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">

	<script type="text/javascript" src="js/jquery-3.0.0.min.js"></script>
    <script type="text/javascript" src="js/jquery.tablescroll.js"></script>
    <script type="text/javascript" src="js/jquery-ui.js"></script>
    <script type="text/javascript" src="js/jquery.editinplace.js"></script>
	<script>

        function logout(){
			if( ! confirm("¿Desea salir de la aplicación?") ) {
				return false;
			}
			document.location.href="login.php?logout=logout"
		}

        function goAddAgrup(){
            $.post("addAgrup.php",function(html){$("#center").html(html);});
        }

        function goEstandar(){
            $.post("estandar.php",function(html){$("#center").html(html);});
        }

        function goProject(){
            $.post("project.php",function(html){$("#center").html(html);});
        }

        function goAddProject(){
            $.post("newProject.php",function(html){$("#center").html(html);});
        }

        function goCalifEstandar(){
            $.post("califEstandar.php",function(html){$("#center").html(html);});
        }

        function goAjustes(){
            $.post("settings.php",function(html){$("#center").html(html);});
        }

        function goCalificarProyecto(nombreProyecto,idAgrupamiento){
            $.post("califProject.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"",function(html){$("#center").html(html);});
        }

        function goInformeProyecto(nombreProyecto,idAgrupamiento){
            window.open("boletinProyecto.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"");
        }

        function generaRubricaProyecto(nombreProyecto,idAgrupamiento){
            window.open("rubrica.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"");
        }

        function generaRubricaProyecto2(nombreProyecto,idAgrupamiento){
            window.open("rubrica2.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"");
        }

        function goInformeEvaluacion(){
            $.post("informeEvaluacion.php",function(html){$("#center").html(html);});
        }

        function goInformeEvaluacion2(){
            $.post("informeEvaluacionEstandares.php",function(html){$("#center").html(html);});
        }

        function listaEstandares(){
            //valor del select
            var idAgrup = $("#selAgrup").val();
            if(idAgrup=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }
            $.post("estandar.php","id="+idAgrup+"",function(html){$("#center").html(html);});

        }

        function listaEstandares2(){
            //valor del select
            var idAgrup = $("#selAgrup").val();
            if(idAgrup=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }
            $.post("califEstandar.php","idAgrupamiento="+idAgrup+"",function(html){$("#center").html(html);});

        }

        function listaDiario(){
            //valor del select
            var idAgrup = $("#selAgrup").val();
            var fecha = $("#fecha").val();
            if(idAgrup=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }
            $.post("main.php","idAgrupamiento="+idAgrup+"&fecha="+fecha+"",function(html){$("#center").html(html);});

        }

        function listaEstandaresCreaProyecto(){
            //valor del select
            var idAgrupamiento = $("#selAgrupCreaProyecto").val();
            if(idAgrupamiento=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }
            $.post("newProject.php","idAgrupamiento="+idAgrupamiento+"",function(html){$("#center").html(html);});
        }

        function checkProject(){
            //valor del select
            var idAgrupamiento = $("#selAgrupCreaProyecto").val();
            var nombreProyecto = $("#nombreProyecto").val();
            if(idAgrupamiento=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }else if(nombreProyecto==''){//valor del txtNombreProyecto
                alert('El agrupamiento debe tener un nombre');
                return;
            }else{
                $.post("newProject.php","idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});
            }
        }

        function saveAgrup(){
            //validation
            if($("#txtNameAgrup").val()==''){
                alert('El agrupamiento debe tener un nombre');
                return;
            }else if($("#txtNameSubj").val()==''){
                alert('Debe asignarse una materia al agrupamiento');
                return;
            }else if($("#txtCursoAgrup").val()==''){
                alert('Debe asignarse un curso al agrupamiento');
                return;
            }else if($("#txtLevel").val()==''){
                alert('Debe asignarse nivel educativo al agrupamiento');
                return;
            }else{
                //getting variables
                var stringForm = $("#formAddAgrup").serialize();
                var stringDef = ""+stringForm+"&addAgrup='yes'";
                $.post("index.php",stringDef,function(html){});
                window.location.href="index.php";
                //alert(stringDef);
            }
        }

        function saveAlum(agrupamiento,id){
            //validation
            if($("#txtAlum").val()==''){
                alert('El alumno o la alumna debe tener un nombre');
                return;
            }else{
                var nombreAlum=$("#txtAlum").val();
                $.post("agrup.php","agrup="+agrupamiento+"&id="+id+"&nombreAlum="+nombreAlum+"",function(html){$("#center").html(html);});
            }
        }

        function saveEstandar(id){
            //validation
            if($("#taEstandar").val()==''){
                alert('El estándar de aprendizaje debe contener texto');
                return;
            }else{
                var estandar = $("#taEstandar").val();
                $.post("estandar.php","id="+id+"&estandar="+estandar+"",function(html){$("#center").html(html);});
            }
        }

        function goAgrup(agrupamiento,id){
            $.post("agrup.php","agrup="+agrupamiento+"&id="+id+"",function(html){$("#center").html(html);});
        }

        function goAgrupFecha(agrupamiento,id){
            var fecha = $("#fecha").val();
            $.post("agrup.php","agrup="+agrupamiento+"&id="+id+"&fecha="+fecha+"",function(html){$("#center").html(html);});
        }

        function estandarToText(){
            //valor del select
            var idAgrup = $("#selAgrup").val();
            var idEstandar = $("#selEstandarCreaProyecto").val();
            if(idAgrup=='0'){
                alert('Seleccione un agrupamiento');
                return;
            }else if(idEstandar=='0'){
                alert('Seleccione un estándar de aprendizaje');
                return;
            }else{
                $.post("califEstandar.php","idAgrupamiento="+idAgrup+"&idEstandar="+idEstandar+"",function(html){$("#center").html(html);});
            }
            //alert(textoEstandar);
        }

        function estandarToText2(){
            var textoEstandar = $("#selEstandarCreaProyecto option:selected").text();
            $("#taEstandar").val(textoEstandar);
        }

        function saveProject(){
            //validation
            if($("#nombreProyecto").val()==''){
                alert('El proyecto debe tener un nombre');
                return;
            }else if($("#selEstandarCreaProyecto").val()=='0'){
                alert('Debe seleccionar un estándar de aprendizaje');
                return;
            }else if($("#formNewProject input[type='checkbox']:checked").length == 0){
                alert('Seleccione al menos una competencia básica');
                return;
            }else if($("#txtNumItems").val()==''){
                alert('Indique el número de ítems a trabajar en el estándar');
                return;
            }else if($("#txtPesoEstandar").val()==''){
                alert('El estándar debe contar con una ponderación');
                return;
            }else{
                //adelante
                var stringForm = $("#formNewProject").serialize();
                $.post("newProject.php?save=yes",stringForm,function(html){$("#center").html(html);});
                //alert(stringForm);
            }
        }

        function eliminaProyecto(id,nombreProyecto,idAgrupamiento){
            if( ! confirm("ATENCIÓN: El presente estándar se eliminará definitivamente del proyecto ¿Desea continuar?") ) {
				return false;
			}
	        $.post("project.php","delete="+id+"&idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});
        }

        function eliminaProyectoDesdeCrear(id,nombreProyecto,idAgrupamiento){
            if( ! confirm("ATENCIÓN: El presente estándar se eliminará definitivamente del proyecto ¿Desea continuar?") ) {
				return false;
			}
	        $.post("newProject.php","delete="+id+"&idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});
        }

        function listaProyectos(){
            //validation
            if($("#selAgrupProyectos").val()=='0'){
                alert("Debe seleccionar un agrupamiento");
                return;
            }else{
                var agrupamiento = $("#selAgrupProyectos").val();
                $.post("project.php","idAgrupamiento="+agrupamiento+"",function(html){$("#center").html(html);});
            }
        }

        function listaDetallesProyecto(idAgrupamiento){
            //validation
            if($("#selProyecto").val()=='0'){
                alert("Debe seleccionar un proyecto");
                return;
            }else{
                var nombreProyecto = $("#selProyecto").val();
                $.post("project.php","idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});
            }
        }

        function eliminarProyecto(nombreProyecto,idAgrupamiento){
            if( ! confirm("Advertencia: Se eliminará este proyecto y sus calificaciones registradas ¿REALMENTE DESEA CONTINUAR?") ) {
				    return false;
                }
                $.post("project.php","deleteProyecto=si&idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});

        }

        function replicarProyecto(nombreProyecto,idAgrupamiento){
            var numReplicas = $("#txtNumReplicas").val();
            if(numReplicas==''){
                alert("Debe especificar el número de réplicas a realizar");
                return;
            }
            if( ! confirm("Va a replicar "+numReplicas+" veces este proyecto. Posteriormente deberá renombrar cada réplica. ¿Desea continuar?") ) {
				    return false;
                }
                $.post("project.php","numReplicas="+numReplicas+"&idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});

        }

        function copiaProyecto(nombreProyecto,idAgrupamiento){
            var idAgrupamientoDestino = $("#selAgrupCopiaProyecto").val();
            if(idAgrupamientoDestino=='0'){
                alert("Indique el agrupamiento al que desea copiar el proyecto");
                return;
            }
            if( ! confirm("Va a copiar este proyecto al agrupamiento seleccionado. ¿Desea continuar?") ) {
				    return false;
                }
                $.post("project.php","idAgrupamientoDestino="+idAgrupamientoDestino+"&idAgrupamiento="+idAgrupamiento+"&nombreProyecto="+nombreProyecto+"",function(html){$("#center").html(html);});

        }

        function generaDocumentoProyecto(nombreProyecto,idAgrupamiento){
            window.open("reportProject.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"");
        }

        function califica(idAgrupamiento,stringArrayIdProyecto,nombreProyecto){
            //getting variables
            var stringForm = $("#formCalifica").serialize();
            var stringDef = ""+stringForm+"&idAgrupamiento="+idAgrupamiento+"&stringArrayIdProyecto="+stringArrayIdProyecto+"&nombreProyecto="+nombreProyecto+"";
            $.post("califProject.php?nombreProyecto="+nombreProyecto+"&idAgrupamiento="+idAgrupamiento+"",stringDef,function(html){$("#center").html(html);});

            //alert(stringDef);
        }

        function presentaCalifEstandar(){
            //validation
            if($("#selAgrup").val()=='0'){
                alert('Debe seleccionar un agrupamiento');
                return;
            }else if($("#selEstandarCreaProyecto").val()=='0'){
                alert('Debe seleccionar un estándar de aprendizaje');
                return;
            }else{
            //getting variables
            var idAgrupamiento = $("#selAgrup").val();
            var idEstandar = $("#selEstandarCreaProyecto").val();
            var idProyecto = $("#selProyecto").val();
            $.post("califEstandar.php","idAgrupamiento="+idAgrupamiento+"&idEstandar="+idEstandar+"&idProyecto="+idProyecto+"",function(html){$("#center").html(html);});
            }//fin de else

            //alert(stringForm);
        }

        function calificaEstandar(){
            //validation
            if($("#selAgrup").val()=='0'){
                alert('Debe seleccionar un agrupamiento');
                return;
            }else if($("#selEstandarCreaProyecto").val()=='0'){
                alert('Debe seleccionar un estándar de aprendizaje');
                return;
            }else if($("#formCalificaEstandar input:text").val().length == 0){
                alert('Indique al menos una calificación para algún alumno');
                return;
            }else{
            //getting variables
            var nombreAgrupmiento = $("#selAgrup option:selected").text();
            var nombreProyecto = $("#selProyecto option:selected").text();
            var stringForm = $("#formCalificaEstandar").serialize();
            $.post("califEstandar.php?nombreAgrupamiento="+nombreAgrupmiento+"&nombreProyecto="+nombreProyecto+"",stringForm,function(html){$("#center").html(html);});
            }//fin de else

            //alert(stringForm);
        }

        function calificaEstandarNuevo(){
            //validation
            if($("#selAgrup").val()=='0'){
                alert('Debe seleccionar un agrupamiento');
                return;
            }else if($("#selEstandarCreaProyecto").val()=='0'){
                alert('Debe seleccionar un estándar de aprendizaje');
                return;
            }else if($("#formCalificaEstandar input[type='checkbox']:checked").length == 0){
                alert('Seleccione al menos una competencia básica');
                return;
            }else if($("#formCalificaEstandar input:text").val().length == 0){
                alert('Indique al menos una calificación para algún alumno');
                return;
            }else{
            //getting variables
            var nombreAgrupmiento = $("#selAgrup option:selected").text();
            var stringForm = $("#formCalificaEstandar").serialize();
            $.post("califEstandar.php?nombreAgrupamiento="+nombreAgrupmiento+"",stringForm,function(html){$("#center").html(html);});
            }//fin de else

            //alert(stringForm);
        }

        function calificaEstandarNuevo2(){
            //validation
            if($("#selAgrup").val()=='0'){
                alert('Debe seleccionar un agrupamiento');
                return;
            }else if($("#selEstandarCreaProyecto").val()=='0'){
                alert('Debe seleccionar un estándar de aprendizaje');
                return;
            }else if($("#formCalificaEstandar input:text").val().length == 0){
                alert('Indique al menos una calificación para algún alumno');
                return;
            }else{
            //getting variables
            var nombreAgrupmiento = $("#selAgrup option:selected").text();
            var stringForm = $("#formCalificaEstandar").serialize();
            $.post("califEstandar.php?nombreAgrupamiento="+nombreAgrupmiento+"&nuevo=nuevo",stringForm,function(html){$("#center").html(html);});
            }//fin de else

            //alert(stringForm);
        }

        function listaProyectosEvaluacion(){
            //validación
            var idAgrupamiento = $("#selAgrupInformeEvaluacion").val();
            var fechaIni = $("#fechaIni").val();
            var fechaFin = $("#fechaFin").val();
            if(idAgrupamiento=='0'){
                alert("Debe seleccionar un agrupamiento");
                $("#fechaFin").val('');
                return;
            }else if(fechaIni==''){
                alert("Debe seleccionar una fecha inicial");
                $("#fechaFin").val('');
                return;
            }else if(fechaFin==''){
                alert("Debe seleccionar una fecha final");
                $("#fechaFin").val('');
                return;
            }
            //fin validación
            $.post("informeEvaluacion.php","idAgrupamiento="+idAgrupamiento+"&fechaIni="+fechaIni+"&fechaFin="+fechaFin+"",function(html){$("#center").html(html);});
        }

        function listaEstandaresEvaluacion(){
            //validación
            var idAgrupamiento = $("#selAgrupInformeEvaluacion").val();
            var fechaIni = $("#fechaIni").val();
            var fechaFin = $("#fechaFin").val();
            if(idAgrupamiento=='0'){
                alert("Debe seleccionar un agrupamiento");
                $("#fechaFin").val('');
                return;
            }else if(fechaIni==''){
                alert("Debe seleccionar una fecha inicial");
                $("#fechaFin").val('');
                return;
            }else if(fechaFin==''){
                alert("Debe seleccionar una fecha final");
                $("#fechaFin").val('');
                return;
            }
            //fin validación
            $.post("informeEvaluacionEstandares.php","idAgrupamiento="+idAgrupamiento+"&fechaIni="+fechaIni+"&fechaFin="+fechaFin+"",function(html){$("#center").html(html);});
        }

        function asignaPeso(num){
            //número de cb clicados en el formulario
            var numberOfChecked = $('input:checkbox:checked').length;
            var peso = $("#txtAsignaPeso").val();
            var pesoAsignado = (peso/numberOfChecked);
            for (var i = 0; i < num; i++) {
                if( $("#cb_"+i+"").prop('checked') ) {
                    $("#txt_Peso_"+i+"").val(pesoAsignado);
                    $("#hid_Peso_"+i+"").val(pesoAsignado);
                    $("#cb_"+i+"").prop( "checked", false );
                }
            }
            var arrayPesos = new Array();
            for (var p = 0; p < num; p++) {
                arrayPesos[p] = parseFloat($("#txt_Peso_"+p+"").val());
            }
            var total=0;
            for(var q in arrayPesos) {
                total += arrayPesos[q];
            }
            $("#txtTotalPeso").val(total);
        }

        function generaInformeEval(){
            //validación
            if($("#txtTotalPeso").val()<100){
                if( ! confirm("Advertencia: El informe se generará con un peso de los proyectos inferior al 100% ¿Desea continuar?") ) {
				    return false;
                }
            }
            if($("#txtTotalPeso").val()>100){
                alert("La suma del peso de los proyectos excede del 100 por ciento. No se generará el informe");
                return;
            }
            //si escoge la opción Continuar, continuamos
            var stringForm = $("#formInformeEvaluacion").serialize();
            window.open("boletinEvaluacion.php?"+stringForm+"");
        }

        function generaInformeEstandaresEval(){

            //si escoge la opción Continuar, continuamos
            var stringForm = $("#formInformeEvaluacionEstandares").serialize();
            var nombreAgrupamiento = $("#selAgrupInformeEvaluacion option:selected").text();
            //alert(stringForm);
            window.open("boletinEvaluacionEstandares.php?"+stringForm+"&nombreAgrupamiento="+nombreAgrupamiento+"");
        }

        function generaInformeEstandaresEvalPond(){
            //validación
            if($("#txtTotalPeso").val()<100){
                alert("La suma del peso de los estándares es inferior al 100 por ciento. No se generará el informe");
				    return;
            }
            if($("#txtTotalPeso").val()>100){
                alert("La suma del peso de los estándares excede del 100 por ciento. No se generará el informe");
                return;
            }
            //si escoge la opción Continuar, continuamos
            var stringForm = $("#formInformeEvaluacionEstandares").serialize();
            var nombreAgrupamiento = $("#selAgrupInformeEvaluacion option:selected").text();
            //alert(stringForm);
            window.open("boletinEvaluacionEstandaresPond.php?"+stringForm+"&nombreAgrupamiento="+nombreAgrupamiento+"");
        }

        function copiaEstandares(){
            //validación
            var idAgrupamientoOrigen = $("#selAgrupOrigen").val();
            var idAgrupamientoDestino = $("#selAgrupDestino").val();

            if(idAgrupamientoOrigen=='0'){
                alert("Debe seleccionar un agrupamiento de origen");
                return;
            }else if(idAgrupamientoDestino=='0'){
                alert("Debe seleccionar un agrupamiento de destino");
                return;
            }else if(idAgrupamientoOrigen==idAgrupamientoDestino){
                alert("Los agrupamientos deben ser distintos");
                return;
            }
            //fin validación
            if( ! confirm("Advertencia: Van a copiarse los estándares del Agrupamiento Origen al Agrupamiento Destino ¿Desea continuar?") ) {
				    return false;
                }
            $.post("settings.php","idAgrupamientoOrigen="+idAgrupamientoOrigen+"&idAgrupamientoDestino="+idAgrupamientoDestino+"",function(html){$("#center").html(html);});
        }

        function exportarEstandaresProgramacion(){
            window.open("exportaEstandaresProgramacion.php");
        }

        function backUp(){
            window.open("backup.php");
        }

        function eliminaAlum(idAlum,id,agrup){
            if( ! confirm("Advertencia: Se eliminará este alumno y todas sus calificaciones ¿Desea continuar?") ) {
				    return false;
                }
            $.post("agrup.php","idAlumnoElimina="+idAlum+"&id="+id+"&agrup="+agrup+"",function(html){$("#center").html(html);});
        }

        function cambiaAlumAgrup(idAlum,id,agrup){
            if( ! confirm("Advertencia: Se cambiará este alumno al agrupamiento seleccionado ¿Desea continuar?") ) {
				    return false;
                }
            //validación
            var idAgrupamientoCambio = $("#selCambiaAgrup").val();
            if(idAgrupamientoCambio=='0'){
                alert("Debe seleccionar un agrupamiento");
                return;
            }
            $.post("agrup.php","idAlumnoCambia="+idAlum+"&id="+id+"&agrup="+agrup+"&idAgrupCambio="+idAgrupamientoCambio+"",function(html){$("#center").html(html);});
        }

        function eliminaAgrup(id){
            if( ! confirm("ATENCIÓN: Se eliminará este agrupamiento y todas sus calificaciones, proyectos y estándares de aprendizaje ¿REALMENTE DESEA CONTINUAR?") ) {
				    return false;
                }
            $.post("index.php","idAgrupamientoElimina="+id+"",function(html){});
        }

        function eliminaEstandar(idEstandar,id){
            if( ! confirm("Advertencia: Se eliminará este estándar de aprendizaje ¿Desea continuar?") ) {
				    return false;
                }
            $.post("estandar.php","idEstandarEliminar="+idEstandar+"&id="+id+"",function(html){$("#center").html(html);});
        }

        function grabaSesion(nombreAgrupamiento){
            //validación
            var idAgrupamiento = $("#hidIdAgrupamiento").val();
            var textoDiario = $("#taDiario").val();
            var fecha = $("#txtSesionDestino").val();
            if(textoDiario==''){
                alert("El contenido de la sesión no puede estar vacío");
                return;
            }
            $.post("main.php","nombreAgrupamiento="+nombreAgrupamiento+"&idAgrupamiento="+idAgrupamiento+"&textoDiario="+textoDiario+"&fecha="+fecha+"",function(html){$("#center").html(html);});
        }
        function asistencia(idAlumno,idAgrup,agrup){
            //no hay validación, seleccionar el valor cero (opción A) es anular la asistencia
            var asistencia = $("#selAsis_"+idAlumno+"").val();
            var fecha = $("#fecha").val();
            if(asistencia=='0'){
                alert("Va a eliminar la incidencia");
            }
            $.post("agrup.php","idAlumno="+idAlumno+"&asistencia="+asistencia+"&id="+idAgrup+"&agrup="+agrup+"&fecha="+fecha+"",function(html){$("#center").html(html);});
        }

        function goFicha(idAlumno){
            $.post("ficha.php","idAlumno="+idAlumno+"",function(html){$("#center").html(html);});
        }

        function goFichaSelect(){
            var idAlumno = $("#selAlumAgrup").val();
            $.post("ficha.php","idAlumno="+idAlumno+"",function(html){$("#center").html(html);});
        }

        function grabaFranja(){
            //validación
            var selAgrup_0 = $("#selAgrup_0").val();
            var selAgrup_1 = $("#selAgrup_1").val();
            var selAgrup_2 = $("#selAgrup_2").val();
            var selAgrup_3 = $("#selAgrup_3").val();
            var selAgrup_4 = $("#selAgrup_4").val();
            var nombreAgrup_0 = $("#selAgrup_0 option:selected").text();
            var nombreAgrup_1 = $("#selAgrup_1 option:selected").text();
            var nombreAgrup_2 = $("#selAgrup_2 option:selected").text();
            var nombreAgrup_3 = $("#selAgrup_3 option:selected").text();
            var nombreAgrup_4 = $("#selAgrup_4 option:selected").text();
            var txtEspacio_0 = $("#txtEspacio_0").val();
            var txtEspacio_1 = $("#txtEspacio_1").val();
            var txtEspacio_2 = $("#txtEspacio_2").val();
            var txtEspacio_3 = $("#txtEspacio_3").val();
            var txtEspacio_4 = $("#txtEspacio_4").val();
            var txtNuevaFranja = $("#txtNuevaFranja").val();

            if(txtNuevaFranja==''){
                alert("Debe proporcionar una franja horaria. Por ejemplo: 08:30-09:25");
                return;
            }else if(selAgrup_0=='0'||selAgrup_1=='0'||selAgrup_2=='0'||selAgrup_3=='0'||selAgrup_4=='0'){
                alert("Debe elegir algo cada día");
                return;
            }else if(txtEspacio_0==''||txtEspacio_1==''||txtEspacio_2==''||txtEspacio_3==''||txtEspacio_4==''){
                alert("Recuerde que siempre puede especificar el aula donde imparte clase o incluir cualquier comentario");
            }
            var string="selAgrup_0="+selAgrup_0+"&selAgrup_1="+selAgrup_1+"&selAgrup_2="+selAgrup_2+"&selAgrup_3="+selAgrup_3+"&selAgrup_4="+selAgrup_4+"&nombreAgrup_0="+nombreAgrup_0+"&nombreAgrup_1="+nombreAgrup_1+"&nombreAgrup_2="+nombreAgrup_2+"&nombreAgrup_3="+nombreAgrup_3+"&nombreAgrup_4="+nombreAgrup_4+"&txtEspacio_0="+txtEspacio_0+"&txtEspacio_1="+txtEspacio_1+"&txtEspacio_2="+txtEspacio_2+"&txtEspacio_3="+txtEspacio_3+"&txtEspacio_4="+txtEspacio_4+"&txtNuevaFranja="+txtNuevaFranja+"";
            //alert('Todo correcto');
            $.post("main.php",string,function(html){$("#center").html(html);});
        }

        function escondeFranja(){
            $("#tablaHorario tr.fila").toggle();
        }

        function muestraInputReplica(){
            $("#numReplicas").toggle();
        }

        function verSesion(idAgrupamiento,nombreAgrupamiento,fechaDestino){

            var string = "idAgrupamiento="+idAgrupamiento+"&fecha="+fechaDestino+"&nombreAgrupamiento="+nombreAgrupamiento+"";
            $.post("main.php",string,function(html){$("#center").html(html);});
        }

        function eliminaDiario(idAviso){
            if( ! confirm("Advertencia: Se eliminará esta entrada del diario de sesiones ¿Desea continuar?") ) {
				    return false;
                }
            $.post("main.php","idAviso="+idAviso+"",function(html){$("#center").html(html);});
        }

	</script>
</head>

<body>

<div id="header">
    <br/>
	<p>Approva</p>

	<span id="notify">
		<img id="loading" style="display:none" src="css/images/ajax-loader.gif" title="Cargando" />
	</span>

    <ul>
            <li><a href="index.php"><img src="css/images/home.png" title="Inicio" /></a></li>
            <li><a href="#" onclick="goEstandar()">Estándares de Aprendizaje</a></li>
            <li><a href="#" onclick="goProject()">Proyectos</a></li>
            <li><a href="#" onclick="goCalifEstandar()">Calificar Estándar</a></li>
            <li><a href="#" onclick="goInformeEvaluacion()">Informes de Evaluación por Proyectos</a></li>
            <li><a href="#" onclick="goInformeEvaluacion2()">Informes de Evaluación por Estándares</a></li>
            <li><a href="#" onclick="">Programación de Aula</a></li>
            <li><a href="#" onclick="goAjustes()">Ajustes</a></li>
            <li><a href="#" onclick="logout()"><img src="css/images/logout.png" title="Salir" /></a></li>
    </ul>

</div>

<div id="content">

	<div id="left">
        <h2>Agrupamientos</h2>
        <hr/>
        <?php
        //consulta de agrupamientos para listar
        $query="SELECT * FROM `$tabla_agrupamientos` order by `agrupamiento`";
        $result=mysqli_query($con_mysql,$query)or die('ERROR:'.mysqli_error($con_mysql));
        $num=mysqli_num_rows($result);
        if($num>0){
            for($a=0;$a<$num;$a++){
                $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
                echo '<p>';
                    echo '<big><b><a href="#" onclick="goAgrup(\''.$row['agrupamiento'].'\',\''.$row['id'].'\')">'.$row['agrupamiento'].'</a></b></big>';
                    echo '<br/>';
                    echo '<b>'.$row['materia'].'</b>';
                    echo ' - ';
                    echo ''.$row['curso'].' '.$row['nivel'].'';
                    echo '<br/>';
                echo '</p>';
            }
        }else{
            echo '<p>No hay agrupamientos registrados</p>';
        }
        //fin listado agrupamientos

// Free result set
mysqli_free_result($result);
mysqli_close($con_mysql);
?>

	</div><!--cierre de contenedor lateral izquierdo -->

	<div id="center">
		<?php
            include('main.php');
        ?>
	</div>

	<div id="footer">
        <ul>
            <li><a href="#">andClass 2016</a></li>
        </ul>
	</div>

</div>


</body>
</html>
