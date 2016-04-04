<?php
namespace CADB\Lib;

function Login($loginid, $password) {
	$context = \CADB\Model\Context::instance();
	$result = \CADB\Auth::authenticate($loginid,$password);
	if(!$result) {
		$err = \CADB\Auth::error();
		if(preg_match("/비밀번호/i",$err)) {
			$ret = -2;
		} else if(preg_match("/아이디/i",$err)) {
			$ret = -1;
		} else {
			$ret = -3;
		}
	} else {
		$ret = 0;
	}

	return $ret;
}

function Logout() {
	unset($_SESSION);
	session_destroy();
}

function requireLogin() {
	$context = \CADB\Model\Context::instance();
	$service = $context->getProperty('service.*');
	$requestURI = ($_SERVER['HTTPS'] == 'on' ? "https://" : "http://").$service['domain'].$_SERVER['REQUEST_URI'];
	\CADB\Lib\RedirectURL('login',array('ssl'=>true,'query'=>array('requestURI'=>$requestURI)));
}

function doesHaveMembership() {
	$context = \CADB\Model\Context::instance();
	$domain = $context->getProperty('service.domain');
	$__Acl = \CADB\Acl::instance();
	return $__Acl->getIdentity($domain) !== null;
}

function requireMembership() {
	$context = \CADB\Model\Context::instance();
	$domain = $context->getProperty('service.domain');
	$__Acl = \CADB\Acl::instance();
	if($__Acl->getIdentity($domain) !== null) {
		return true;
	}
	\CADB\Lib\requireLogin();
}
?>
