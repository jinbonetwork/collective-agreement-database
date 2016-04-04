<?php
namespace CADB;

class Options extends \CADB\Objects  {
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getOption($name) {
		$dbm = DBM::instance();

		if(is_array($name)) {
			$que = "SELECT * FROM {options} WHERE name IN ('".implode("','",$name)."')";
		} else {
			$que = "SELECT * FROM {options} WHERE name = '".$name."'";
		}
		if(is_array($name)) {
			while($row = $dbm->getFetchArray($que)) {
				$options[$row['name']] = self::fetchOption($row['vlaue']);
			}
			return $options;
		} else {
			$row = $dbm->getFetchArray($que);
			return self::fetchOption($row['value']);	
		}
	}

	private static function fetchOption($value) {
		if(\CADB\Lib\is_serialized($value)) {
			return unserialize($value);
		} else if(is_numeric($value)) {
			return $v;
		} else {
			return stripslashes($v);
		}
	}
}
?>
