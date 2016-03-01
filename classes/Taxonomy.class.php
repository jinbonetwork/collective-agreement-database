<?php
namespace CADB;

class Taxonomy extends \CADB\Objects  {
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getTaxonomy($cids) {
		$dbm = DBM::instance();

		if(!is_array($cids)) $cids = array($cids);
		$que = "SELECT * FROM {taxonomy} WHERE cid IN (".implode(",",$cids).")";
		while($row = $dbm->getFetchArray($que)) {
			$taxonomy[$row['cid']] = self::fetchTaxonomy($row);
		}
		return $taxonomy;
	}

	private static function fetchTaxonomy($row) {
		$taxonomy = array();
		foreach($row as $k => $v) {
			if(is_numeric($v)) {
				$taxonomy[$k] = $v;
			} else {
				$taxonomy[$k] = stripslashes($v);
			}
		}
		return $taxonomy;
	}
}
?>
