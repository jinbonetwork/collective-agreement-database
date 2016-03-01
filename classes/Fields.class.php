<?php
namespace CADB;

class Fields extends \CADB\Objects  {
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFields($table="all",$active=1) {
		$dbm = DBM::instance();

		if(!is_array($table)) {
			if($table != 'all') $table = array($table);
		}
		$fields = array();
		$que = "SELECT * FROM {fields} WHERE ".(is_array($table) ? "`table` IN ('".implode("','",$table)."') AND " : "")."active = '".$active."' ORDER BY `table` ASC, idx ASC";
		while($row = $dbm->getFetchArray($que)) {
			$fields[$row['fid']] = self::fetchFields($row);
		}
		return $fields;
	}

	private static function fetchFields($row) {
		$fields = array();
		foreach($row as $k => $v) {
			if(is_numeric($v)) {
				$fields[$k] = $v;
			} else {
				$fields[$k] = stripslashes($v);
			}
		}
		return $fields;
	}
}
?>
