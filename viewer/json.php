<?php
include('./init.php');

function invalid_action_error() {
    die(json_encode("No valid action selected"));
}

if (!isset($_GET['action'])) {
    invalid_action_error();
}

$filter = "";
if (isset($_GET['filter'])) {
    $filter = stripslashes($_GET['filter']);
}

switch ($_GET['action']) {

case 'chartlist':
    $it = new AppendIterator();
    $it->append(new ArrayIterator(array('timeline' => array('Number of reports', 'SELECT report_date FROM stat WHERE {filter} ORDER BY report_date'))));
    $it->append($def);
    $it->append(new ArrayIterator(array(
        'settings' => array('PHP Settings by PHP Version', ''),
        'php_by_pmf' => array('PHP Version by PMF Version', ''),
    )));
    echo json_encode(array(
        'dat' => iterator_to_array($it),
    ));
    break;

case 'dump':
    $stmt = $pdo->query("SELECT * FROM stat" . ($filter ? ' WHERE '.$filter : ''));
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $first = true;

    $dat = array('header' => array(), 'data' => array());

    foreach ($stmt as $row) {
        if ($first) {
            foreach ($row as $name => $value) {
                $dat['header'][] = $name;
            }
            $first = false;
       }
       $dat['data'][] = $row;
    }

    echo json_encode($dat);
    break;

case 'querylist':
    echo json_encode(implode($pdo->getQueries(), "\n"));
    break;
default:
    invalid_action_error();
}
