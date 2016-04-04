<?php
define('__CADB__',true);
if(!defined('ROOT')) {
	$__root_fullpath = rtrim(dirname(__FILE__),"include");
	$__root_pathcount = explode("/",substr(dirname($_SERVER['SCRIPT_FILENAME']),strlen($__root_fullpath)));
	$__root_path = "";
	for($i=0; $i<@count($__root_pathcount); $i++) {
		$__root_path .= ($i > 0 ? "/" : "")."..";
	}
	define('ROOT',$__root_path);
}

require_once(ROOT.'/config/config.php');
define('__CADB_LOADED_CLASS__',true);

$browser = new Browser();

$cadb_config = \CADB\Model\Config::instance();
$context = \CADB\Model\Context::instance();
$context->setProperty('service.base_uri',CADB_URI);

try {
    if(!is_null($context->getProperty('database.DB'))) {
		$cadb_db = $context->getProperty('database.*');
		$dbm = \CADB\DBM::instance();
		$dbm->bind($cadb_db,1);
		register_shutdown_function( array($dbm,'release') );
	}

	$__Acl = \CADB\Acl::instance();
	$__Acl->getPrivilege();

	$themes = new \CADB\Themes();
	$themes->themeHeader();
//    $dbm->release();
} catch(Exception $e) {
	$logger = \CADB\Logger::instance();
	$logger->Error($e);
}
