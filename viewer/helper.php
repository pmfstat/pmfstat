<?php
//ini_set('include_path', './ezc/trunk');
//require_once "./ezc/trunk/Base/src/base.php";
ini_set('include_path', '.:./ezcomponents-2007.1beta1');
require_once "./ezcomponents-2007.1beta1/Base/src/base.php";
function __autoload( $className )
{
    ezcBase::autoload( $className );
}


class VersionGraph extends ezcGraphPieChart
{
    protected $pdo;
    protected $filter;

    public function __construct(PDO $pdo, $title, $query, $filter = "")
    {
        parent::__construct();

        $this->pdo = $pdo;

        $this->title = $title;
        $this->filter = $filter;

        $this->setDataQuery($query);

            $this->renderer = new ezcGraphRenderer3d(); 
    }

    public function setDataQuery($sql)
    {
        $sql = str_replace('{filter}', $this->filter ? $this->filter : 1, $sql);
        $this->data['data'] = new ezcGraphArrayDataSet(new StatResulIterator($this->pdo->query($sql)));
    }

    public function render($w, $h, $f = null)
    {
        try {
//            $this->renderer = new ezcGraphRenderer3d(); 
            $this->renderer->options->moveOut = .2;

            $this->renderer->options->pieChartOffset = 63;

            $this->renderer->options->pieChartGleam = .3;
            $this->renderer->options->pieChartGleamColor = '#FFFFFF';
            $this->renderer->options->pieChartGleamBorder = 2;

            $this->renderer->options->pieChartShadowSize = 3;
            $this->renderer->options->pieChartShadowColor = '#000000';

            $this->renderer->options->legendSymbolGleam = .5;
            $this->renderer->options->legendSymbolGleamSize = .9;
            $this->renderer->options->legendSymbolGleamColor = '#FFFFFF';

            $this->renderer->options->pieChartSymbolColor = '#000000';


            parent::render($w, $h, $f);
        } catch (Exception $e) {
            file_put_contents($f, $e->__toString());
        }
    }
}

######################################

class PDODebug extends PDO
{
    private $queries = array();

    public function __construct($dsn, $user, $pw)
    {
        parent::__construct($dsn, $user, $pw);
        $this->queries &= $_SESSION['queries'];
    }

    public function query($sql)
    {
        $_SESSION['queries'][] = $sql;
        return parent::query($sql);
    }

    public function getQueries() {
        return $_SESSION['queries'];
    }
}
