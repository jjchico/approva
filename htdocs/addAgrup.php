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

echo '<h2>Registro de Nuevo Agrupamiento</h2>';
echo '<form id="formAddAgrup" name="formAddAgrup">';
    echo '<span>Nombre del agrupamiento:</span>';
    echo '<br/>';
    echo '<input type="text" id="txtNameAgrup" name="txtNameAgrup" />';
    echo '<br/><br/>';
    echo '<span>Nombre de la materia:</span>';
    echo '<br/>';
    echo '<input type="text" id="txtNameSubj" name="txtNameSubj" />';
    echo '<br/><br/>';
    echo '<span>Curso del agrupamiento:</span>';
    echo '<br/>';
    echo '<input type="text" id="txtCursoAgrup" name="txtCursoAgrup" />';
    echo '<br/><br/>';
    echo '<span>Nivel educativo:</span>';
    echo '<br/>';
    echo '<input type="text" id="txtLevel" name="txtLevel" />';
    echo '<br/><br/>';
    echo '<a href="#" onclick="saveAgrup()">';
	echo 'Registrar Agrupamiento';
	echo '</a>';
echo '</form>';
?>
