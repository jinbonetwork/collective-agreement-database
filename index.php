<?php
define('__CADB__',true);
if(!defined('ROOT'))
	define('ROOT','.');

/**
 * @brief 필요한 설정 파일들 include
 **/
require_once(ROOT.'/config/config.php');
define('__CADB_LOADED_CLASS__',true);

$browser = new Browser();

global $context, $config;
$config = \CADB\Model\Config::instance();
$context = \CADB\Model\Context::instance();
$uri = \CADB\Model\URIHandler::instance();
$context->setProperty('uri',$uri);
$context->setProperty('service.base_uri',$uri->uri['root']);

try {
	if(!is_null($context->getProperty('database.DB'))) {
		$db = $context->getProperty('database.*');
		$dbm = \CADB\DBM::instance();
		$dbm->bind($db,1);
		register_shutdown_function( array($dbm,'release') );
		$uri->URIParser();
	}

	/**
	 * @brief URL을 기반으로 소스 경로와 controller class 를 지정한다.
	 * 또한 각각의 파일에는 Validate 정의와 권한정보가 포함되어야 한다.
	 * Validate는 $IV = array()
	 * Privilege는 $Acl = ''
	 **/
	include_once $uri->uri['appPath']."/".$uri->uri['appFile'].".php";
	$controller_class = $uri->uri['appClass'];

	/**
	* check Basic Post/GET variable validation.
	**/
	$valid = true;
	if (isset($IV)) $valid = $valid && \CADB\Validator::validate($IV);

	// Basic SERVER variable validation to prevent hijacking possibility.
	$basicIV = array(
		'SCRIPT_NAME' => array('string'),
		'REQUEST_URI' => array('string'),
		'REDIRECT_URL' => array('string', 'mandatory' => false)
	);
	$valid = $valid && \CADB\Validator::validateArray($_SERVER, $basicIV);

	// Basic URI information validation.
	if (!$valid) {
		header('HTTP/1.1 404 Not Found');
		exit;
	}

	/**
	 * @brief session include and start
	 **/
	if(!defined('NO_SESSION')) {
		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				\CADB\Lib\importLibrary('session.gnu5');
				break;
		}
		\CADB\Lib\session\init_session();
	}

	/*
	 * @brief Acl(Access Controll Logic
	**/
	$__Acl = \CADB\Acl::instance();
	$__Acl->getPrivilege();
	$__Acl->setAcl($Acl);
	/* login check */
	$__Acl->check();

	$controller = new $controller_class;
	$controller->handle($uri->params);

	$dbm->release();
} catch(Exception $e) {
	$logger = \CADB\Logger::instance();
	$logger->Error($e);
}
?>
