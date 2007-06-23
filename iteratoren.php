<?php
class ReportGrowthIterator extends IteratorIterator
{
    private $icount;

    public function __construct(Traversable $it) {
        parent::__construct($it);
        $this->icount = 0;
    }

    public function key()
    {
        $tmp = parent::current();
        return $tmp[0];
    }

    public function current()
    {
        static $count = 0;
        return ++$count;
    }
}

class ReportGrowthStepIterator extends ReportGrowthIterator
{
    private $steps;

    public function __construct(Traversable $it, $steps = 15)
    {
        if (!($it instanceof Iterator)) {
            $it = new IteratorIterator($it);
        }
        parent::__construct(new CachingIterator($it));
        $this->steps = $steps;
    }

    public function next()
    {
        parent::next();

        for ($i = 0; $i < $this->steps - 1; $i++) {
            if ($this->/*getInnerIterator()->*/hasNext()) {
                parent::next();
            }
        }
    }
}

class StatResulIterator extends IteratorIterator
{
    public function key()
    {
        $tmp = parent::current();
        if (strlen($tmp[0]) > 20) {
            $tmp[0] = substr($tmp[0], 0, 18) . '...';
        }
        return $tmp[0];
    }

    public function current()
    {
        $tmp = parent::current();
        return $tmp[1];
    }
}

class TableColumnsIterator extends IteratorIterator
{
    public function __construct(PDO $pdo, $table) {
        parent::__construct($pdo->query("SHOW COLUMNS FROM `$table`"));
        $this->filter = $filter;
    }

    public function key()
    {
        $tmp = parent::current();
        return $tmp['Field'];
    }

    public function current()
    {
        $tmp = parent::current();
        return array($tmp['Field'], 'SELECT `'.$tmp['Field'].'` AS name, count(*) FROM stat WHERE {filter} GROUP BY name');
    }
}

class StoreInSessionDataCollectIterator extends IteratorIterator
{
    private $key;

    public function __construct(Traversable $it, $key)
    {
        parent::__construct($it);
        $this->key = $key;
    }

    public function current()
    {
        return $_SESSION[$this->key][parent::key()] = parent::current();
    }
}

abstract class StoreInSessionIteratorAggregate implements IteratorAggregate
{
    private $key;
    private $overwrite;

    public function __construct($key, $overwrite = false)
    {
        $this->key = $key;
        $this->overwrite = $overwrite;
    }

    abstract function getInnerIterator();

    public function getIterator()
    {
        if (!isset($_SESSION[$this->key]) || $this->overwrite) {
            $_SESSION[$this->key] = array();
            return new StoreInSessionDataCollectIterator($this->getInnerIterator(), $this->key);
        } else {
            return new ArrayIterator($_SESSION[$this->key]);
        }
    }
}


class RelevantTableColumnsIterator extends FilterIterator
{
    private $badFields = array();

    public function __construct(PDO $pdo, $table, array $badFields)
    {
        parent::__construct(new TableColumnsIterator($pdo, $table));
        $this->badFields = $badFields;
    }

    public function accept()
    {
        return !in_array($this->key(), $this->badFields);
    }
}

class RelevantTableToSessionIterator extends StoreInSessionIteratorAggregate
{
    private $pdo;
    private $table;
    private $badFields;

    public function __construct(PDO $pdo, $table, array $badFields, $overwrite = false)
    {
        parent::__construct('__cached_'.$table, $overwrite);
        $this->pdo = $pdo;
        $this->table = $table;
        $this->badFields = $badFields;
    }

    public function getInnerIterator()
    {
        return new RelevantTableColumnsIterator($this->pdo, $this->table, $this->badFields);
    }
}


class PHPExtensionsIterator extends ArrayIterator
{
    public function key()
    {
        return 'ext'.parent::current();
    }

    public function current()
    {
        $ext = parent::current();
        $query = sprintf('SELECT "ja", count(*) FROM stat WHERE ({filter}) AND PHP_extensions LIKE "%%%1$s%%" UNION SELECT "nein", count(*) FROM stat WHERE ({filter}) AND PHP_extensions NOT LIKE "%%%1$s%%"',
                         $ext);
        return array($ext.' Extension', $query);
    }
}

class GraphGeneratingIteratorIterator extends IteratorIterator
{
    protected $pdo;
    protected $filter;
    protected $width;
    protected $height;
    protected $dir;
    protected $extension;

    public function __construct(PDO $pdo, Traversable $it, $filter = "", $width = 600, $height = 500, $dir = 'svg', $extension = '.svg')
    {
        parent::__construct($it);
        $this->pdo = $pdo;
        $this->filter = $filter;
        $this->width = $width;
        $this->height = $height;
        $this->dir = $dir;
        $this->extension = $extension;
    }

    public function key()
    {
        $sep = ''; //$this->filter ? '.'.crc32($filter) : '';
        return parent::key().$sep.$this->extension;
    }

    public function current()
    {
        $d = parent::current();
        $graph = new VersionGraph($this->pdo, $d[0], $d[1], $this->filter);
        $graph->render($this->width, $this->height, $this->dir.'/'.$this->key());

        return $this->dir.'/'.$this->key();
    }
}

class GraphTagGeneratingIteratorIterator extends GraphGeneratingIteratorIterator
{
    public function current()
    {
        return sprintf("<iframe src='%s' width='%u' height='%u'></iframe>\n", parent::current(), $this->width, $this->height);
    }
}
