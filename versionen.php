<?php
include('./init.php');

$it = new GraphTagGeneratingIteratorIterator($pdo, $def);

foreach ($it as $d)
{
    echo $d;
}


######################################


$graph = new ezcGraphLineChart();
$graph->title = "PHP settings by PHP Version";
$graph->yAxis->max = 1;
$graph->yAxis->labelCallback = create_function( '$label, $step', 'return sprintf( "%d%%  ", $label * 100 );' );
foreach ($settings as $title => $sql) {
    $sql = str_replace('{filter}', 1, $sql);
    $graph->data[$title] =  new ezcGraphArrayDataSet(new StatResulIterator($pdo->query($sql)));
}
$graph->render(600, 500, 'svg/register_globals_by_PHP.svg');
echo "<iframe src='svg/register_globals_by_PHP.svg' width='600' height='500'></iframe>\n";

