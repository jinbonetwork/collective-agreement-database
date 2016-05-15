<?php
namespace CADB\Member;
        
class DBM extends \CADB\Objects  {
	private static $fields;
	public static $errmsg;
				        
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getPrivilegeByID($members) {
		$dbm = \CADB\DBM::instance();

		$privileges = array();
		if(is_array($members)) {
			$que = "SELECT * FROM {privilege} AS p LEFT JOIN {organize} AS o ON p.oid = o.oid WHERE p.user_id IN('".implode("','",$members)."')";
			while($row = $dbm->getFetchArray($que)) {
				$privileges[$row['user_id']][$row['oid']] = $row;
			}
		}

		return $privileges;
	}

	public static function deletePrivilegeByID($user_id) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {privilege} WHERE user_id = ?";
		$dbm->execute($que,array("s",$user_id));
	}

	public static function addPrivileges($uid,$user_id,$oid,$role) {
		$dbm = \CADB\DBM::instance();

		$que = "INSERT INTO {privilege} (`uid`,`user_id`,`oid`,`role`) VALUES (?,?,?,?)";

		$dbm->execute($que,array("dsdd",$uid,$user_id,$oid,$role));
	}

	public static function removePrivileges($user_id,$oid) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {privilege} WHERE `user_id` = ? AND `oid` = ?";

		$dbm->execute($que,array("sd",$user_id,$oid));
	}

	public static function updatePrivileges($user_id,$oid,$role) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {privilege} SET role = ? AND user_id = ? AND oid = ?";
		$dbm->execute($que,array("dsd",$role,$user_id,$oid));
	}
}
?>
