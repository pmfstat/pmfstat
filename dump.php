<?php
require_once('./init.php');

$mode   = isset($_GET['mode'])   ? $_GET['mode']   : 'html';
$filter = isset($_GET['filter']) ? stripslashes($_GET['filter']) : 1;

$stmt = $pdo->query("SELECT * FROM stat WHERE $filter");
$stmt->setFetchMode(PDO::FETCH_ASSOC);

$it = new IteratorIterator($stmt);

switch ($mode) { 
case 'html':
    $dump = new dumphtml($it);
    break;
case 'sql':
    header('Content-Type: text/plain');
    $dump = new dumpsql($it);
    break;
case 'csv':
    header('Content-Type: text/plain');
    $dump = new dumpcsv($it);
    break;
default:
    die('Invalid mode');
    break;
}

if (isset($_GET['export']) && $_GET['export'] != 'server') {
    $filename = 'dump_'.date('YmdHis').'.'.$mode;
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}


$dump->run();

abstract class dump {
    protected $it;

    public function __construct(Iterator $it)
    {
        $this->it = $it;
    }

    abstract public function init();
    abstract public function printcurrent(Iterator $i);
    abstract public function finalize();

    public function run()
    {
        $this->init();
        iterator_apply($this->it, array($this, 'printcurrent'), array($this->it));
        $this->finalize();
    }
}

abstract class dumpnoinitorfinalize extends dump {
    public function init() {}
    public function finalize() {}
}

class dumphtml extends dump
{
    public function init()
    {
        echo "<table border='1'>\n";
    }

    public function printcurrent(Iterator $it) {
        static $first = true;

        if ($first) {
            echo '<tr>';
            foreach ($it->current() as $name => $value) {
                echo '<th>'.htmlentities($name).'</th>';
            }
            echo "</tr>\n";
            $first = false;
        }

        echo '<tr>';
        foreach ($it->current() as $row) {
            echo '<td>'.htmlentities($row).'</td>';
        }
        echo "</tr>\n";

        return true;
    }

    public function finalize()
    {
        echo '</table>';
    }
}

class dumpsql extends dumpnoinitorfinalize
{
    protected $tablename = 'stat';

    public function printcurrent(Iterator $it)
    {
        global $pdo;
        $values = '';

        echo 'INSERT INTO ', $this->tablename, ' (';
        $cit = new CachingIterator(new ArrayIterator($it->current()));
        foreach ($cit as $name => $value) {
            echo '`', $name, '`';
            $values .= is_numeric($value) ? $value : $pdo->quote($value);
            if ($cit->hasNext()) {
                echo ', ';
                $values .= ', ';
            }
        }
        
        echo ') VALUES ('.$values.");\n";
        
        return true;
    }
}

class dumpcsv extends dumpnoinitorfinalize
{
    public function printcurrent(Iterator $it)
    {
        $cit = new CachingIterator(new ArrayIterator($it->current()));
        foreach ($cit as $name => $value) {
            echo is_numeric($value) ? $value : '"'.addslashes($value).'"';
            if ($cit->hasNext()) {
                echo ',';
            }
        }
        echo "\n";
        return true;
    }
}

