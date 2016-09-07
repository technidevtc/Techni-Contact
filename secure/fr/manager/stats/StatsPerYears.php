<?php

// New graph with a drop shadow
$graph = new Graph($GraphWidth,$GraphHeight,'auto');
$graph->SetShadow();
$graph->img->SetMargin(60,30,20,40);

// Use a "text" X-scale
$graph->SetScale("textlin");

// Specify X-labels
$graph->xaxis->SetTickLabels($YearLabels);

// Set title and subtitle
$graph->title->Set("Statistiques \"" . $sourcedesc . "\" " . $typedesc);

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->SetValuePos('center');

$b1->value->Show();
$b1->value->SetFont(FF_FONT1,FS_NORMAL,11);
$b1->value->SetColor("black","darkred");
$b1->value->HideZero(true);
$b1->value->SetAngle(90);
$b1->value->SetFormat($format);

//$b1->SetLegend("Temperature");

//$b1->SetAbsWidth(6);
//$b1->SetShadow();

// The order the plots are added determines who's ontop
$graph->Add($b1);

?>