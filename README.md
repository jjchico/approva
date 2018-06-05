# README #

**Repositorio trasladado a: https://gitlab.com/jjchico/approva**

Este repositorio es una copia no oficial de APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) desarrollado por Ramón Castro Pérez.

Para más información visite la [web oficial](http://siestta.org/).

## Descripción ##

APPROVA es una aplicación web para evaluación de asignatura mediente el método
tradicional (proyectos/actividades) y mediante metodologías basadas en
estándares de aprendizaje.

## Instalación ##

APPROVA necesita una base de datos MySQL y un servidor web con soporte PHP para
poder ejecutarse. Puede encontrar más instrucciones en la [web oficial](http://siestta.org/).

Las instrucciones siguientes son específicas de la versión distribuída en este
repositorio.

### Instrucciones generales ###

  * Copie el contenido de la carpeta 'htdocs' a un lugar accesible por el servidor web. Por ejemplo '/var/www/html/approva'.

  * Copie o renombre el archivo 'config.php.dist' a 'config.php'.

  * Edite el archivo 'config.php' y defina los parámetros del servidor MySQL. Su utiliza los parámetros de administrador de la base de datos (usurio 'root') Approva creará la base de datos por usted durante la instalación. Se aconseja no usar aquí la cuenta 'root' y crear el usuario y la base de datos previamente desde MySQL.

  * Vaya en su navegador a la ruta de instalación de Approva. Por ejemplo 'http://localhost/approva'. Debe iniciarse el proceso de instalación.

## Descargo ##

El software suministrado en este repositorio es experimental.
No se recomienda su uso en cualquier entorno donde no pueda tolerarse la
pérdida de datos. El distribuidor no se hace responsable de cualquier
inconveniente, pérdida de datos, daños directos o indirectos fruto del uso
de este software. Úsele bajo su exclusiva responsabilidad.

## Licencia ##

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is
free software; you can redistribute it and/or modify it under the terms of the
GNU General Public License as published by the Free Software Foundation; either
version 3 of the License, or (at your option) any later version.

APPROVA (Sistema de Evaluación por Proyectos y Estándares de Aprendizaje) is
distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You cand find a copy of the GNU General Public License in the "license"
directory.

You should have received a copy of the GNU General Public License along with
APPROVA; if not, write to the Free Software Foundation, Inc., 51 Franklin St,
Fifth Floor, Boston, MA  02110-1301  USA.
