<?php
$diagramme = array(
  'php_versions_major'  => array('PHP Major Version', 'SELECT CONCAT("PHP ", SUBSTR(PHP_version, 1, 1), ".x.y")  AS version, count(*) FROM stat WHERE {filter} GROUP by version'),
  'php_versions_minor'  => array('PHP Minor Version', 'SELECT CONCAT("PHP ", SUBSTR(PHP_version, 1, 3), ".y")    AS version, count(*) FROM stat WHERE {filter} GROUP by version'),
  'php_versions_xyz'    => array('PHP Version',       'SELECT CONCAT("PHP ", IF (LOCATE("-", PHP_version), SUBSTR(PHP_version, 1, LOCATE("-", PHP_version) - 1), PHP_version)) AS version, count(*) FROM stat WHERE {filter}  GROUP by version'),
  'php_versions_4'      => array('PHP 4',             'SELECT PHP_version, count(*) FROM stat WHERE ({filter}) AND PHP_version LIKE "4%" GROUP by PHP_version'),
  'php_versions_5'      => array('PHP 5',             'SELECT PHP_version, count(*) FROM stat WHERE ({filter}) AND PHP_version LIKE "5%" GROUP by PHP_version'),

  'system_httpd'        => array('HTTPD name',        'SELECT IF (LOCATE(" ", System_httpd), SUBSTR(System_httpd, 1, LOCATE(" ", System_httpd)), System_httpd) AS title,   count(*) FROM stat WHERE {filter}  GROUP BY title'), 
  'system_httpd_minor'  => array('HTTPD minor version', 'SELECT IF (LOCATE("/", System_httpd), SUBSTR(System_httpd, 1, LOCATE("/", System_httpd) + 3), System_httpd) AS title, count(*) FROM stat WHERE {filter}  GROUP BY title'), 
);

$extensions = array('suhosin', 'openssl', 'apc', 'hash', 'bcmath', 'swf', 'ming', 'pdf', 'gd', 'json', 'soap', 'tokenizer', 'mbstring', 'iconv', 'imap', 'ldap', 'zlib', 'bz2', 'sysvshm', 'sysvsem', 'sysvmsg', 'Zend Optimizer');
natcasesort($extensions);

$settings = array(
  'register_globals' => 'SELECT CONCAT("PHP ", SUBSTR(PHP_version, 1, 3), ".y") AS version, SUM(PHP_register_globals)/COUNT(PHP_register_globals) AS value FROM stat WHERE {filter} GROUP by version ORDER BY version',
  'safe_mode'        => 'SELECT CONCAT("PHP ", SUBSTR(PHP_version, 1, 3), ".y") AS version, SUM(PHP_safe_mode)/COUNT(PHP_register_globals) AS value FROM stat WHERE {filter} GROUP by version ORDER BY version',
  'magic_quotes_gpc' => 'SELECT CONCAT("PHP ", SUBSTR(PHP_version, 1, 3), ".y") AS version, SUM(PHP_magic_quotes_gpc)/COUNT(PHP_magic_quotes_gpc) AS value FROM stat WHERE {filter} GROUP by version ORDER BY version',  
);

$badFields = array('id', 'report_date', 'PHP_extensions', 'q_other', 'PHP_disable_functions', 'System_httpd', 'System_ip');

$def = new AppendIterator();
$def->append(new ArrayIterator($diagramme));
$def->append(new PHPExtensionsIterator($extensions));
$def->append(new IteratorIterator(new RelevantTableToSessionIterator($pdo, 'stat', $badFields)));

