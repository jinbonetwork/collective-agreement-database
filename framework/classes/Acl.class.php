<?php
namespace CADB;
class Acl extends \CADB\Objects {
	private $predefinedrole;
	private $acl;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	protected function __construct() {
		global $AclPreDefinedRole;

		$this->predefinedrole = $AclPreDefinedRole;
	}

	public function setAcl($Acl) {
		if( !isset( $this->acl ) ) {
			$this->getPrivilege();
		}

		if($Acl)
			$this->role = $this->predefinedrole[$Acl];
		else
			$this->role = BITWISE_ANONYMOUS;
	}

	public function getAcl() {
		return $this->role;
	}

	public function check() {
		if($this->role < BITWISE_ANONYMOUS && !$_SESSION['user']['uid']) {
			importLibrary('auth');
			requireMembership();
		}
/*		if($_SESSION['user']['uid'] && $this->role < $_SESSION['user']['glevel']) {
			Error('접근 권한이 없습니다');
			exit;
		} */
	}

	function getPrivilege() {
		$context = \CADB\Model\Context::instance();

		$domain = $context->getProperty('service.domain');
		$session_type = $context->getProperty('session.type');

		if(!isset( $this->acl ) ) {
			$classname = "CADB\\Model\\".strtoupper($session_type);

			$acl = new $classname;
			$this->acl = $acl->getAcl($domain);
		}
	}

	public static function imMaster() {
		if($_SESSION['user']['glevel'] == BITWISE_ADMINISTRATOR) return 1;
		else return 0;
	}

	public function checkAcl($role,$eq='ge') {
		$permission = false;
		switch($eq) {
			case 'ge':
				if($this->role >= $role)
					$permission = true;
				break;
			case 'le':
				if($this->role <= $role)
					$permission = true;
				break;
			case 'eq':
				default:
				if($this->role == $role)
					$permission = true;
				break;
		}
		return $permission;
	}

	public function getIdentity($domain) {
		return $_SESSION['user']['uid'];
	}
}
?>
