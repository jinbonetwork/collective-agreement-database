<?php
/**
 * @file	config/config.php
 * @brief	기본적으로 사용하는 환경 설정 변수 값 설정 및 class 파일의 include
 **/

@error_reporting(E_ALL ^ E_NOTICE);
ini_set('display_errors','1');

if(!defined('__CADB__')) exit();

/**
 * @brief JinboNet PHP Framework System의 전체 버젼 표기
 **/
define('CADB_NAME', 'JINBONET');
define('CADB_VERSION', '0.5');

/**
 * @brief JinboNet PHP Framework System이 설치된 장소의 base path를 구함
 **/
define('CADB_PATH',str_replace('/config/config.php','',str_replace('\\', '/', __FILE__)));
if(!defined('CADB_URI')) {
	if(ROOT != '.')
		define('CADB_URI',"/".ROOT.rtrim(str_replace('index.php', '', $_SERVER["SCRIPT_NAME"])));
	else
		define('CADB_URI',rtrim(str_replace('index.php', '', $_SERVER["SCRIPT_NAME"])));
}

/**
 * Path Configuration
 **/

define('CADB_CLASS_PATH', CADB_PATH.'/framework/classes');
define('CADB_LIB_PATH', CADB_PATH.'/framework/library');
define('CADB_RESOURCE_URI', CADB_URI.'resources');
define('CADB_RESOURCE_PATH', CADB_PATH.'/resources');
define('CADB_CONTRIBUTE_URI', CADB_URI.'contribute');
define('CADB_CONTRIBUTE_PATH', CADB_PATH.'/contribute');
define('CADB_DATA_URI', CADB_URI.'files');
define('CADB_DATA_PATH', CADB_PATH.'/files');
define('CADB_CACHE_PATH', CADB_PATH.'/files/cache');

define('CADB_APP_PATH', CADB_PATH.'/app/');
define('CADB_API_PATH', CADB_PATH.'/app/api');

define('CADB_DEBUG',			1);

define("CADB_LOG_TYPE_PRINT",	1);
define("CADB_LOG_TYPE_FILE",	2);
define("CADB_LOG_TYPE_ALL",		3);

define("CADB_LOG_TYPE",			CADB_LOG_TYPE_PRINT);

define("CADB_LOG_ID", 'www');
define("CADB_LOG_DATE_FORMAT", 'Y-m-d H:i:s');
define("CADB_ERROR_LOG_PATH", CADB_PATH."/files/log/");

define("CADB_ERROR_ACTION_AJAX", 1);
define("CADB_ERROR_ACTION_URL", 2);
define("CADB_ERROR_AJAX_MSG", "FAIL");

define("CADB_COMMON_ERROR_PAGE", "");

define("CADB_REGHEIGHT_CONFIG_URL", "");

define('DIRECTORY_SEPARATOR','/');

require_once CADB_CLASS_PATH."/Autoload.class.php";
require_once CADB_CLASS_PATH."/Objects.class.php";
require_once CADB_CLASS_PATH."/Controller.class.php";

require_once CADB_LIB_PATH."/common.php";
require_once CADB_LIB_PATH."/import.php";

//spl_autoload_register(array('Autoload', 'load'));
$autoloader = new \CADB\Autoload;
$autoloader->register();

$autoloader->addNamespace('CADB',CADB_CLASS_PATH);
$autoloader->addNamespace('CADB\Model',CADB_CLASS_PATH."/Model");
$autoloader->addNamespace('CADB',CADB_PATH."/classes");
$autoloader->addNamespace('CADB\App',CADB_APP_PATH);
$autoloader->addNamespace('CADB\CONTRIBUTE',CADB_CONTRIBUTE_PATH);

define( 'BITWISE_ADMINISTRATOR', 1 );
define( 'BITWISE_OWNER', 3 );
define( 'BITWISE_USER', 5 );
define( 'BITWISE_ATHENTICATED', 16 );
define( 'BITWISE_ANONYMOUS', 17 );

global $AclPreDefinedRole;
$AclPreDefinedRole = array(
	'administrator'=>BITWISE_ADMINISTRATOR,
	'owner'=>BITWISE_OWNER,
	'user'=>BITWISE_USER,
	'authenticated'=>BITWISE_ATHENTICATED,
	'anonymous'=>BITWISE_ANONYMOUS
);

define( 'PW_ALGO', 'sha256' );

define( 'FB_ID', -1);
define( 'TWIT_ID', -2);
define( 'OPEN_ID', -10);

global $dev;
$dev = array(
	'timestamp' => time(),
);
?>
