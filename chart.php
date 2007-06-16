<?php
include('./init.php');

$filter = "";
if (isset($_GET['filter'])) {
    $filter = stripslashes($_GET['filter']);
}

$d = iterator_to_array($def);
if (isset($_GET['d']) && isset($d[$_GET['d']])) {
    $q = $d[$_GET['d']];
} else {
    die('Unknown chart selected!');
}


try {
    $graph = new VersionGraph($pdo, $q[0], $q[1], $filter);
} catch (PDOException $exception) {
    echo "<p>Error while performing the database query. Possibly an invalid filter.</p>";
    echo "<pre>", $exception->getMessage(), '</pre>';
    echo "<pre>"; print_r($pdo->getQueries()); echo '</pre>';
    die();
}

if (isset($_GET['export'])) {
    switch ($_GET['export']) {
    case 'png':
        $graph->driver = new ezcGraphGdDriver();
        $graph->options->font = 'ezcomponents-2007.1beta1/Graph/docs/tutorial_font.ttf';
        $graph->driver->options->imageFormat = IMG_PNG;
        $graph->render(700, 600, 'tmp.png');

        header('Content-type: image/png');
        header('Content-Disposition: attachment; filename="'.$_GET['d'].'.png"');

        echo file_get_contents('tmp.png');
        unlink('tmp.png');
        break;
    case 'svg':
        header('Content-type: image/svg+xml');
        header('Content-Disposition: attachment; filename="'.$_GET['d'].'.svg"');
        $graph->render(700, 600, 'php://output');
    }
} else {
    header('Content-type: image/svg+xml');
    $graph->render(700, 600, 'php://output');
}
