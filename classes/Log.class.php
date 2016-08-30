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

	public static function accessLog($action="login",$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "user:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsdss",$action,$_SESSION['user']['uid'],$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'], $memo));
	}

	public static function articleLog($action="modify",$did,$nid,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "article:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sdddsdss",$action,$did,$nid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function orgLog($action="modify",$oid,$vid,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "org:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sdddsdss",$action,$oid,$vid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function guideLog($action="modify",$nid,$vid=0,$id=0,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "guide:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`vid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddddsdss",$action,$nid,$vid,($id ? $id : 0),$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function memberLog($action="modify",$uid,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "member:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsdss",$action,$uid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function fieldLog($action="modify",$fid=0,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "field:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsdss",$action,$fid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function taxonomyLog($action="insert",$cid,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "taxonomy:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddsdss",$action,$cid,$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}

	public static function taxonomytermLog($action="modify",$cid,$tid=0,$vid=0,$memo="") {
		$dbm = \CADB\DBM::instance();

		$member = self::getMember();
		if(!$member) return;
		$action = "taxonomy_term:".$action;

		$que = "INSERT INTO {log} (`action`,`oid`,`fid`,`vid`,`editor`,`name`,`modified`,`ipaddress`,`memo`) VALUES (?,?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sddddsdss",$action,$cid,($fid ? $fid : 0),($vid ? $vid : 0),$_SESSION['user']['uid'],$member['name'],time(),$_SERVER['REMOTE_ADDR'],$memo));
	}
}
