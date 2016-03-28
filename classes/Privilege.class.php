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
}
