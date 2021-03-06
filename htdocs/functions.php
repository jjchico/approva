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

////////////////////////////////////////////////////
//Convierte fecha de normal a mysql
////////////////////////////////////////////////////

function nombreDia($day){
    if($day=='Sunday'){return 'Domingo';}
    else if($day=='Monday'){return 'Lunes';}
    else if($day=='Tuesday'){return 'Martes';}
    else if($day=='Wednesday'){return 'Miércoles';}
    else if($day=='Thursday'){return 'Jueves';}
    else if($day=='Friday'){return 'Viernes';}
    else if($day=='Saturday'){return 'Sábado';}
}

// Comprueba si existe una base de datos

function dbExists($mysql_con, $db)
{
    $query = "SELECT *
              FROM INFORMATION_SCHEMA.SCHEMATA
              WHERE schema_name = '$db'
              LIMIT 1;";
    $result = mysqli_query($mysql_con, $query) or die(mysqli_error($mysql_con));
    $count = count(mysqli_fetch_row($result));
    if ($count === 0)
        return False;
    else
        return True;
}

// Comprueba si existe una tabla en una base de datos

function tableExists($mysql_con, $table)
{
    $result = mysqli_query($mysql_con, "SHOW TABLES LIKE '$table'") or
        die(mysqli_error($mysql_con));
    return mysqli_num_rows($result) > 0;
}

?>
