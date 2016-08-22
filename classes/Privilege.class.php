<?
namespace CADB;

class Privilege extends \CADB\Objects  {
	public static function checkAgreement($articles) {
		$context = \CADB\Model\Context::instance();
		$domain = $context->getProperty('service.domain');

		$__Acl = \CADB\Acl::instance();
		if( $__Acl->imMaster() ) return true;

		$role = $__Acl->getAcl();

		$dbm = \CADB\DBM::instance();

		$nids = array();
		$nids[] = $articles['nid'];
		for($i=4; $i>=1; $i--) {
			if( $articles['p'.$i] )
				$nids[] = $articles['p'.$i];
		}
		$que = "SELECT * FROM {agreement_organize} AS r LEFT JOIN {organize} AS o ON ( r.oid = o.oid AND r.vid = o.vid ) WHERE r.nid IN (" . implode(",",$nids) . ") ORDER BY depth ASC";

		while( $row = $dbm->getFetchArray($que) ) {
			if($_SESSION['acl'][$domain][$row['oid']]) {
				$permit = $__Acl->checkAcl($_SESSION['acl'][$domain][$row['oid']]);
				if($permit && $role >= BITWISE_OWNER && !$row['owner'])
					$permit = false;
				if($permit == true) {
					return true;
				}
			} else {
				for($d = min($row['depth'],4); $d>=1; $d--) {
					if($row['p'.$d]) {
						$ret = self::checkOrganize($row['p'.$d]);
						if($ret == true) return true;
					}
				}
			}
		}

		return false;
	}

	public static function checkOrganize($oid) {
		$context = \CADB\Model\Context::instance();
		$domain = $context->getProperty('service.domain');

		$__Acl = \CADB\Acl::instance();
		if( $__Acl->imMaster() ) return true;

		$role = $__Acl->getAcl();

		if( $_SESSION['acl'][$domain][$oid] ) {
			return $__Acl->checkAcl($_SESSION['acl'][$domain][$oid]);
		}
		return false;
	}

	public static function checkOrganizes($orgs) {
		$__Acl = \CADB\Acl::instance();
		if( $__Acl->imMaster() ) return true;

		for($d = min($orgs['depth'],4); $d>=1; $d--) {
			if($orgs['p'.$d]) {
				$ret = self::checkOrganize($orgs['p'.$d]);
				if($ret == true) return true;
			}
		}
		return self::checkOrganize($orgs['oid']);
	}
}
