<?php
/*
This file is part of APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje).

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is developed by Ram&oacute;n Castro P&eacute;rez. You can get more information at http://www.siestta.org

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You cand find a copy of the GNU General Public License in the "license" directory.

You should have received a copy of the GNU General Public License along with APPROVA; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
*/

//forzamos codificación utf-8
header('Content-Type: text/html; charset=UTF-8');

echo '<h3>Instalación inicial de la Plataforma de Evaluación approva ...</h3>';
//config.php
require('config.php');
//functions.php
require('functions.php');
//connect
$mysqli = new mysqli(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD);


//si venimos de dar de alta

if(isset($_POST['username'])){
	//conexión dataBase
	$con_mysql=mysqli_connect(DB_SERVER,DB_MYSQL_USER,DB_MYSQL_PASSWORD,DB_DATABASE);
	if (!$con_mysql) {
		die("Connection error: " . mysqli_connect_error($con_mysql));
	}

    $username=$_POST['username'];
    $password=sha1($_POST['password']);
    $sql="insert into user values(NULL,'$username','$password')";
    $result=mysqli_query($con_mysql,$sql) or die(mysqli_error($con_mysql));
    if($result){
        header("Location:../login.php");
    }else{
        echo 'Hubo algún problema al proceder al registro inicial de usuario';
    }
}

//fin dar de alta

$database = DB_DATABASE;
$sql = "CREATE Database $database";
$retval = mysqli_query($mysqli, $sql) or
    die('No se pudo crear la base de datos: ' . mysqli_error($mysqli));

echo "La base de datos $database se ha creado";
echo '<br/>';

//conexión a la base de datos
mysqli_select_db($mysqli, $database);


   ///////////////////////////////////////////////////creación de tablas

       //agrupamientos
       $sql="CREATE TABLE `agrupamientos` (
      `id` int(11) NOT NULL auto_increment,
      `agrupamiento` varchar(225) NOT NULL,
      `curso` varchar(225) NOT NULL,
      `materia` varchar(225) NOT NULL,
      `nivel` varchar(225) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
       $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
       if($result){
            echo 'Tabla agrupamientos creada';
            echo '<br/>';
       }

        //alumnado
        $sql="CREATE TABLE `alumnado` (
  `id` int(11) NOT NULL auto_increment,
  `agrupamiento_id` int(11) NOT NULL,
  `alumno` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla alumnado creada';
            echo '<br/>';
       }

        //asistencia
        $sql="CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL auto_increment,
  `alumno_id` int(11) NOT NULL,
  `tipo` set('f','r','j') NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla asistencia creada';
            echo '<br/>';
       }

        //calificaciones
        $sql="CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL auto_increment,
  `alumno_id` int(11) NOT NULL,
  `proyecto_id` int(11) NOT NULL,
  `proyecto` varchar(255) NOT NULL,
  `calificacion` decimal(4,2) NOT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla calificaciones creada';
            echo '<br/>';
       }

        //diario
        $sql="CREATE TABLE `diario` (
  `id` int(11) NOT NULL auto_increment,
  `sesion` date NOT NULL,
  `agrupamiento_id` int(11) NOT NULL,
  `diario` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla diario creada';
            echo '<br/>';
       }

        //estándares
        $sql="CREATE TABLE `estandares` (
  `id` int(11) NOT NULL auto_increment,
  `agrupamiento_id` int(11) NOT NULL,
  `estandar` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla estándares creada';
            echo '<br/>';
       }

        //horario
        $sql="CREATE TABLE `horario` (
  `id` int(11) NOT NULL auto_increment,
  `franja` varchar(255) NOT NULL,
  `dia` int(11) NOT NULL,
  `agrupamiento_id` int(11) NOT NULL,
  `agrupamiento` varchar(255) NOT NULL,
  `espacio` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla horario creada';
            echo '<br/>';
       }

        //proyectos
        $sql="CREATE TABLE `proyectos` (
  `id` int(11) NOT NULL auto_increment,
  `agrupamiento_id` int(11) NOT NULL,
  `estandar_id` int(11) NOT NULL,
  `proyecto` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `num` int(11) NOT NULL,
  `peso` decimal(5,2) NOT NULL,
  `ccl` set('0','1') NOT NULL,
  `cmct` set('0','1') NOT NULL,
  `cd` set('0','1') NOT NULL,
  `caa` set('0','1') NOT NULL,
  `csyc` set('0','1') NOT NULL,
  `siep` set('0','1') NOT NULL,
  `cec` set('0','1') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla proyectos creada';
            echo '<br/>';
       }

        //usuario
        $sql="CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $result=mysqli_query($mysqli,$sql) or die(mysqli_error());
        if($result){
            echo 'Tabla usuario creada';
            echo '<br/>';
       }


echo '<hr>';


   mysqli_close($mysqli);

//fin creación de tablas

//presentamos formulario para insertar usuario y clave y ya, de paso, loguearnos

echo '<h1>Registro en la Plataforma de Evaluación</h1>';

echo '<h2>Introduzca el nombre de usuario y la contraseña con la que accederá a la plataforma</h2>';

echo '<p><big><b>Una vez introduzca sus datos, quedará registrado su nombre de usuario y contraseña y podrá utilizarlos para acceder a la plataforma</b></big></p>';

echo '<form method="post" action="index.php" id="login_form">';

echo '<ul>';
		echo '<li>Nombre de usuario que va a usar para acceder: <input name="username" type="text" id="username" value="" size="10"  maxlength="8" /></li>';
		echo '<li>Clave que va a usar para acceder: <input name="password" type="text" id="password" value="" size="10" maxlength="8" /></li>';
echo '</ul>';
		echo '<input class="button" name="Submit" type="submit" id="submit" value="Alta de usuario" />';

echo '</form>';

?>
