<?php
namespace CADB;

class Fields extends \CADB\Objects  {
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFields($table="all",$active=1) {
		$context = \CADB\Model\Context::instance();

		if(!is_array($table)) {
			if($table != 'all') $table = array($table);
		}

		$fields = array();

		self::$fields = $context->getProperty('fields');
		if(!self::$fields) {
			$dbm = \CADB\DBM::instance();
			$que = "SELECT * FROM {fields} ".($active ? "WHERE active = '".$active."' " : "")."ORDER BY `table` ASC, idx ASC";
			while($row = $dbm->getFetchArray($que)) {
				self::$fields[$row['fid']] = self::fetchFields($row);
			}
			$context->setProperty('fields',self::$fields);
		}

		if($table == 'all') {
			$fields = self::$fields;
		} else {
			foreach(self::$fields as $f => $v) {
				if( in_array($v['table'], $table) ) {
					$fields[$f] = $v;
				}
			}
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
