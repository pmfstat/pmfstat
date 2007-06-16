<?php
require_once('./init.php');

try {
    $pdo = new PDO('mysql:host='.$db_server.';dbname='.$db_name, $db_user, $db_pw);
} catch (Exception $e) {
    die("Internal server error while processing statistic data: Couldn't connect to database");
}

$stmt = $pdo->query("SELECT * FROM stat");
$stmt->setFetchMode(PDO::FETCH_ASSOC);

$first = true;

echo "<table border='1'>\n";
foreach ($stmt as $row)
{
    if ($first) {
        echo '<tr>';
        foreach ($row as $name => $value) {
            echo '<th>'.htmlentities($name).'</th>';
        }
        echo "</tr>\n";
        $first = false;
    }

    echo '<tr>';
    foreach ($row as $row) {
        echo '<td>'.htmlentities($row).'</td>';
    }
    echo "</tr>\n";
}
echo "</table>\n";
