<?php
if(!defined('__CADB__')) {
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
}

if( !is_object($browser) )
	$browser = new Browser();

if( !is_object($context) ) {
	$cadb_config = \CADB\Model\Config::instance();
	$context = \CADB\Model\Context::instance();
}

try {
    if(!is_null($context->getProperty('database.DB'))) {
		$cadb_db = $context->getProperty('database.*');
		$dbm = \CADB\DBM::instance();
		$dbm->bind($cadb_db,1);
		register_shutdown_function( array($dbm,'release') );
	}

	if( !is_object($__Acl) ) {
		$__Acl = \CADB\Acl::instance();
		$__Acl->getPrivilege();
	}

	if( !is_object($themes) )
		$themes = new \CADB\Themes();
	$themes->themeFooter();
    $dbm->release();
} catch(Exception $e) {
	$logger = \CADB\Logger::instance();
	$logger->Error($e);
}
