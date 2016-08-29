<?php
namespace CADB;

class Log extends \CADB\Objects {
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static accessLog() {
		$dbm = \CADB\DBM::instance();

		$que = "INSERT INTO {log} (`action`,`editor`,`name`,`modified`,`ipaddress`) VALUES (?,?,?,?,?)";
		$dbm->execute($que,array("sdsds",'login',$_SESSION['user']['uid'],'',time(),$_SERVER['REMOTE_ADDR']));
	}
}
