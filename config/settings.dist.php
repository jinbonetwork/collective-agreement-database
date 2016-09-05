<?php
define("DEBUG_MODE",false);

$database['MYSQL_ERRNO_DUPKEY'] = '1062';
$database['HOST'] = 'database host';
$database['USER'] = 'database id';
$database['PWD'] = 'database password';
$database['DB'] = 'database name';
$database['CHARSET'] = 'utf8';
$database['TIMEOUT'] = 5;
$database['RECONN_COUNT'] = 2;
$database['TB_PREFIX'] = 'cadb_';

$service['domain'] = 'site domain(example: example.com)';
$service['title'] = "site title";
$service['ssl'] = false;
$service['themes'] = 'defaults';
$service['timezone'] = 'Asia/Seoul';
$service['encoding'] = 'UTF-8';
$service['LOG_TYPE'] = 1;
$service['redis'] = 1;

$session['type'] = 'gnu5';
$session['server'] = '127.0.0.1';
$session['cookie_domain'] = '.';
$session['cookie_path'] = '/';
$session['timeout'] = 14400;
?>
