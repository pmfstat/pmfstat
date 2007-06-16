<?php
include('./init.php');

if (!isset($_GET['action'])) {
    die(json_encode("Keine action"));
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
    $it->append(new ArrayIterator(array('settings' => array('PHP Settings by PHP Version', ''))));
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
}
