<?php

//zona horaria por defecto
date_default_timezone_set('Europe/Madrid');

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_pie.php');

//the data
$stringData=$_GET['data'];
$data=explode('@',$stringData);

// A new pie graph
$graph = new PieGraph(300,300,'auto');

// Don't display the border
$graph->SetFrame(false);

// Uncomment this line to add a drop shadow to the border
// $graph->SetShadow();

// Setup title
// Set A title for the plot
$graph->title->Set("Trabajo de competencias clave");
$graph->title->SetFont(FF_DV_SANSSERIF,FS_BOLD,12);
$graph->title->SetMargin(8); // Add a little bit more margin from the top

// Create the pie plot
$p1 = new PiePlotC($data);

// Set size of pie
$p1->SetSize(0.35);

// Label font and color setup
$p1->value->SetFont(FF_DV_SANSSERIF,FS_BOLD,10);
$p1->value->SetColor('black');

$p1->value->Show();

// Setup the title on the center circle
//$p1->midtitle->Set("Test mid\nRow 1\nRow 2");
//$p1->midtitle->SetFont(FF_ARIAL,FS_NORMAL,14);

// Set color for mid circle
//$p1->SetMidColor('yellow');

// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);

// The label array values may have printf() formatting in them. The argument to the
// form,at string will be the value of the slice (either the percetage or absolute
// depending on what was specified in the SetLabelType() above.
$lbl = array("CCL\n%.1f%%","CMCT\n%.1f%%","CD\n%.1f%%",
         "CAA\n%.1f%%","CSYC\n%.1f%%","SIEP\n%.1f%%","CEC\n%.1f%%");
$p1->SetLabels($lbl);

// Uncomment this line to remove the borders around the slices
// $p1->ShowBorder(false);

// Add drop shadow to slices
$p1->SetShadow();

// Explode all slices 15 pixels
$p1->ExplodeAll(15);

// Add plot to pie graph
$graph->Add($p1);

// .. and send the image on it's marry way to the browser
$graph->Stroke();

?>
