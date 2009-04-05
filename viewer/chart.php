<?php
include('./init.php');

/**
* Chart generator
*
* @param d      Name of ther chart
* @param filter SQL WHERE clause to use
* @param width  Width of the picture
* @param height Height of the picture
* @param pie_threshold Threshold used for grouping with pie charts
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
} elseif (!isset($_GET['d']) || ($_GET['d'] != 'settings' && $_GET['d'] != 'timeline' && $_GET['d'] != 'php_by_pmf')) {
    die('Unknown chart selected!');
}

$width  = isset($_GET['width'])  && is_numeric($_GET['width'])  ? (int)$_GET['width'] :  800;
$height = isset($_GET['height']) && is_numeric($_GET['height']) ? (int)$_GET['height'] : 600;
$type   = (isset($_GET['format']) && in_array($_GET['format'], array('svg', 'png'))) ? $_GET['format'] : 'svg';

class BlubIterator extends IteratorIterator {
    public function key() {
        $tmp = parent::current();
        return $tmp[0];
    }

    public function current() {
        $tmp = parent::current();
        return $tmp[1];
    }
}

class TimelineIterator implements Iterator {
    /**
     * @var PDOStatement
     */
    private $stmt;
    private $start;
    private $end;
    private $duration;
    private $step;
    private $position;

    public function __construct(PDO $pdo, $stepcount) {
        $this->pdo = $pdo;

        $sql = 'CREATE TEMPORARY TABLE IF NOT EXISTS report_dates (report_date int not null) SELECT UNIX_TIMESTAMP(report_date) AS report_date FROM stat';
        $row = $pdo->query($sql);
        $sql = "SELECT report_date FROM report_dates ORDER BY report_date ASC LIMIT 1";
        $row = $pdo->query($sql)->fetch();
        $this->start = $row[0];
        $sql = "SELECT report_date FROM report_dates ORDER BY report_date DESC LIMIT 1";
        $row = $pdo->query($sql)->fetch();
        $this->end = $row[0];
        $this->duration = $this->end - $this->start;
        $this->step = $this->duration / ($stepcount-1);
        $this->stmt = $pdo->prepare("SELECT COUNT(*) FROM report_dates WHERE report_date < ?");
    }

    public function rewind() {
        $this->position = $this->start;
    }

    public function next() {
        $this->position += $this->step;
    }

    public function valid() {
        return $this->position < $this->end;
    }

    public function key() {
        return date('Y-m-d', $this->position);
    }

    public function current() {
        $this->stmt->bindParam(1, $this->position);
        $this->stmt->execute();
        $row = $this->stmt->fetch();
        return $row[0];
    }
}

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

    case 'php_by_pmf':
        $graph = new ezcGraphRadarChart();

        $graph->title = "PHP Version by phpMyFAQ Version";

        $v = iterator_to_array(new BlubIterator($pdo->query("SELECT LEFT(PHP_version, 3) AS v FROM stat GROUP BY v")));

        foreach ($v as $ver => $null) {
            $sql = "SELECT `phpMyFAQ_main.currentVersion` as pmfver, 100*COUNT(*)/(SELECT COUNT(*) FROM  stat WHERE `phpMyFAQ_main.currentVersion` = pmfver AND ($filter)) FROM stat WHERE PHP_version LIKE '$ver%' AND LENGTH(`phpMyFAQ_main.currentVersion`) = 5 AND ($filter) GROUP BY `phpMyFAQ_main.currentVersion`";
            $graph->data['PHP '.$ver] = new ezcGraphArrayDataSet(new BlubIterator($pdo->query($sql)));
        }
        break;
    case 'timeline':
        $graph = new ezcGraphLineChart();
        $graph->title = "Number of reports";
        $graph->legend = false;
        
        $graph->xAxis = new ezcGraphChartElementDateAxis();
        $graph->xAxis->dateFormat = "Y/m/d";

        $data = new TimelineIterator($pdo, $_REQUEST['timeline_steps']);
        $graph->data['Machine 1'] = new ezcGraphArrayDataSet($data);
        break;

    default;
        $graph = new VersionGraph($pdo, $q[0], $q[1], $filter);
        $graph->renderer = new ezcGraphRenderer3d();
        if (isset($_REQUEST['pie_threshold'])) {
            $graph->options->percentThreshold = $_REQUEST['pie_threshold']/100;
            $graph->options->summarizeCaption = 'Others';
        }
        break;
    }

} catch (PDOException $exception) {
    echo "<p>Error while performing the database query. Possibly an invalid filter.</p>";
    echo "<pre>", $exception /*->getMessage()*/, '</pre>';

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
    $graph->options->font = './ezc/Graph/docs/tutorial/tutorial_font.ttf';
    $graph->driver->options->imageFormat = IMG_PNG;

    if (!isset($_GET['export']) || $_GET['export'] != 'server') {
        $graph->render($width, $height, 'svg/tmp.png');
        echo file_get_contents('svg/tmp.png');
        unlink('svg/tmp.png');
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
default:
    die("Invalid file type");
}
