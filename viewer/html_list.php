<?php
include('./auth.php');
include('./init.php');

$it = new AppendIterator();
$it->append(new ArrayIterator(array('timeline' => array('Number of reports', 'SELECT report_date FROM stat WHERE {filter} ORDER BY report_date'))));
$it->append($def);
$it->append(new ArrayIterator(array(
    'settings' => array('PHP Settings by PHP Version', ''),
    'php_by_pmf' => array('PHP Version by PMF Version', ''),
)));

echo <<<HTML
<html>
<head>
<script>
function loadchart(chartname) {
    var url = 'http://phpmyfaq.de/stats/chart.php?d='+chartname+'&filter='+escape(document.getElementById('filter').value);
    chartframe.location.href = url;
}
</script>
</head>
<body><table><tr><td><ul>
HTML;
foreach ($it as $name => $caption) {
    echo "<li><a href='#' onclick='loadchart(\"".urlencode($name)."\"); return false;'>".htmlentities($caption[0])."</a></li>\n";
}
echo "</ul>Filter: <input id='filter'></td><td><iframe name='chartframe' style='width: 850;height: 650;'></iframe></td></tr></table></body></html>";

