<?php
require_once('../inc/sql.php');
require_once('../inc/init.php');

try {
    $pdo = new PDO('mysql:host='.$db_server.';dbname='.$db_name, $db_user, $db_pw);
} catch (Exception $e) {
    die("Internal server error while processing statistic data: Couldn't connect to database");
}

PMF_Init::cleanRequest();

$dat = unserialize(@$_POST['systemdata']);
if (!is_array($dat)) {
    die("Internal server error while processing statistic data: Bad input!");
}
$dat['q'] = @$_POST['q'];

// special tratment for extension list:
$dat['PHP']['extensions'] = @implode($dat['PHP']['extensions'], '|');
$dat['q']['other'] = (string)@implode($dat['q']['other'], '|');

$fields = array();
$values = array();
foreach ($pdo->query("SHOW COLUMNS FROM stat") as $row)
{
    if ($row['Field'] == 'id' || $row['Field'] == 'report_date') {
        continue;
    }

    list($prefix, $field) = explode('_', $row['Field'], 2);

    if (isset($dat[$prefix][$field]) && !is_null($dat[$prefix][$field])) {
        $fields[] = '`'.$row['Field'].'`';
        $values[] = @$dat[$prefix][$field];
    }
}

$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)",
    'stat',
    implode(', ', $fields),
    implode(', ', array_fill(0, sizeof($fields), '?')));

$stmt = $pdo->prepare($sql);
foreach ($values as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}
$stmt->execute();

echo '<p>Thank you for yur support! Have fun with your FAQ!</p>';
