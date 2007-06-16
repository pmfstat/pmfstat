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
    $it = $def;
    echo json_encode(array(
        'dat' => iterator_to_array($it),
        'queries' => implode($pdo->getQueries(), "\n"),
    ));
    break;

case 'dump':

    $stmt = $pdo->query("SELECT * FROM stat" . ($filter ? ' WHERE '.$filter : ''));
    $stmt->setFetchMode(PDO::FETCH_ASSOC);

    $first = true;

    $dat = array('header' => array(), 'data' => array());

    foreach ($stmt as $row)
    {
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
}
