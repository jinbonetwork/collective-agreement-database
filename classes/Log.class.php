<?php
namespace CADB;

class Log extends \CADB\Objects {
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	private static function getMember() {
		$context = \CADB\Model\Context::instance();

		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				if($_SESSION['user']['uid']) {
					$member = \CADB\Member\Gnu5\User::getMember($_SESSION['user']['uid']);
					$member['name'] = $member['mb_name'];
				}
				break;
		}

		return $member;
	}

	public static function accessLog($action="login") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "user:".$action;

		$que = "INSERT INTO {log} (`action`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?)";
		$dbm->execute($que,array("sdsds",$action,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function articleLog($action="modify",$did) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "article:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$did,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function orgLog($action="modify",$oid) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "org:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$oid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function guideLog($action="modify",$nid,$id=0) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "guide:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$nid,($id ? $id : 0),$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function memberLog($action="modify",$uid) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "member:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$uid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function fieldLog($action="modify",$fid) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "field:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$fid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function taxonomyLog($action="add",$cid) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "taxonomy:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsds",$action,$cid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}

	public static function taxonomytermLog($action="modify",$cid,$tid=0,$vid=0) {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "taxonomy_term:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`vid`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?,?)";
		$dbm->execute($que,array("sddddsds",$action,$cid,($fid ? $fid : 0),($vid ? $vid : 0),$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR']));
	}
}
