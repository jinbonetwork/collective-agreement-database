<?php
namespace CADB\Model;

class GNU5 extends \CADB\Objects {
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	protected function __contruct() {
	}

	public function getAcl($domain) {
		if( $_SESSION['ss_mb_id'] && !$_SESSION['user']['uid'] ) {
			$dbm = \CADB\DBM::instance();

			if($_SESSION['ss_mb_id']) {
				$que = "SELECT * FROM `g5_member` WHERE `mb_id` = '".$_SESSION['ss_mb_id']."'";
				$row = $dbm->getFetchArray($que);
				if($row['mb_no']) {
					$_SESSION['user'] = array(
						'uid' => $row['mb_no'],
						'glevel' => (11 - $row['mb_level'])
					);
					$que = "SELECT * FROM {privilege} WHERE user_id = '".addslashes($_SESSION['ss_mb_id'])."'";
					while($row = $dbm->getFetchArray($que)) {
						$_SESSION['acl'][$domain][$row['oid']] = $row['role'];
					}
					if(!count($_SESSION['acl'])) {
						$_SESSION['user']['glevel'] = BITWISE_ATHENTICATED;
					}
				}
			}
		}
		if( !isset($_SESSION['acl'][$domain]) ) {
			$_SESSION['acl'][$domain] = array();
		}
		return $_SESSION['acl'][$domain];
	}
}
?>
