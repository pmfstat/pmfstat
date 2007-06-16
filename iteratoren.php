<?php
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
        $sep = $this->filter ? '.'.crc32($this->filter) : '';
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
