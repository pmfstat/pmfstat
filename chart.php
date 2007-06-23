<?php
include('./init.php');

/**
* Chart generator
*
* @param d      Name of ther chart
* @param filter SQL WHERE clause to use
* @param width  Width of the picture
* @param height Height of the picture
* @param format Either svg or png
* @param export Send as attachment if set but != server, if set to server stores the chart on the server
*/


$filter = "1";
if (!empty($_GET['filter'])) {
    $filter = ini_get('magic_quotes_gpc') ? stripslashes($_GET['filter']) : $_GET['filter'];
}

$d = iterator_to_array($def);
if (isset($_GET['d']) && isset($d[$_GET['d']])) {
    $q = $d[$_GET['d']];
} elseif (!isset($_GET['d']) || ($_GET['d'] != 'settings' && $_GET['d'] != 'timeline')) {
    die('Unknown chart selected!');
}

$width  = isset($_GET['width'])  && is_numeric($_GET['width'])  ? (int)$_GET['width'] :  800;
$height = isset($_GET['height']) && is_numeric($_GET['height']) ? (int)$_GET['height'] : 600;
$type   = (isset($_GET['format']) && in_array($_GET['format'], array('svg', 'png'))) ? $_GET['format'] : 'svg';

try {
    switch ($_GET['d']) {
    case 'settings':
        $graph = new ezcGraphLineChart();
        $graph->title = "PHP settings by PHP Version";
        $graph->yAxis->max = 1;
        $graph->yAxis->labelCallback = create_function( '$label, $step', 'return sprintf( "%d%%  ", $label * 100 );' );
        foreach ($settings as $title => $sql) {
            $sql = str_replace('{filter}', $filter, $sql);
            $graph->data[$title] =  new ezcGraphArrayDataSet(new StatResulIterator($pdo->query($sql)));
        }
        break;

    case 'timeline':
        $graph = new ezcGraphLineChart();
        $graph->legend = false;
        $graph->title = "Number of reports";
        $graph->xAxis = new ezcGraphChartElementDateAxis();
        $graph->xAxis->dateFormat = "Y/m/d";

        $sql = "SELECT report_date FROM stat WHERE $filter ORDER BY report_date";
        $graph->data['Machine 1'] = new ezcGraphArrayDataSet(new ReportGrowthStepIterator($pdo->query($sql)));
        break;

    default;
        $graph = new VersionGraph($pdo, $q[0], $q[1], $filter);
        $graph->renderer = new ezcGraphRenderer3d(); 
        break;
    }

} catch (PDOException $exception) {
    echo "<p>Error while performing the database query. Possibly an invalid filter.</p>";
    echo "<pre>", $exception->getMessage(), '</pre>';
    die();
}


$filename = $_GET['d'].'_'.date('YmdHmi').'.'.$type;

if (isset($_GET['export']) && $_GET['export'] != 'server') {
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}

switch ($type) {
case 'png':
    header('Content-type: image/png');

    $graph->driver = new ezcGraphGdDriver();
    $graph->options->font = 'ezcomponents-2007.1beta1/Graph/docs/tutorial_font.ttf';
    $graph->driver->options->imageFormat = IMG_PNG;

    if (!isset($_GET['export']) || $_GET['export'] != 'server') {
        $graph->render(800, 700, 'tmp.png');
        echo file_get_contents('tmp.png');
        unlink('tmp.png');
    } else {
        $graph->render($width, $height, 'svg/'.$filename);
        echo 'svg/'.rawurlencode($filename);
    }

    break;
case 'svg':
    header('Content-type: image/svg+xml');

    if (!isset($_GET['export']) || $_GET['export'] != 'server') {
        $filename = 'php://output';
    } else {
        echo 'svg/'.rawurlencode($filename);
        $filename = 'svg/'.$filename;
    }
    $graph->render($width, $height, $filename);
    break;
}
