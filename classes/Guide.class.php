<?php
namespace CADB;

class Guide extends \CADB\Objects  {
	private static $guide;
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFieldInfo($active=1) {
		if(!self::$fields)
			self::$fields = \CADB\Fields::getFields('guide_clause',$active);
		return self::$fields;
	}

	public static function setFieldInfo($fields) {
		self::$fields = $fields;
	}

	public static function getCurrent($oid=1) {
		$dbm = DBM::instance();
		$que = "SElECT  * FROM {guide} WHERE oid = ".$oid." AND current = '1' ORDER BY nid DESC LIMIT 1";
		self::$guide = $dbm->getFetchArray($que);
		if(self::$guide) {
			return self::$guide['nid'];
		} else {
			return 0;
		}
	}

	public static function getTaxonomy($oid=1) {
		if(!self::$guide)
			$nid = self::getCurrent($oid);

		$cids = preg_split("/[, ]/i",self::$guide['cid']);
		return $cids;
	}

	public static function getRelativeTerm($q) {
		$cids = self::getTaxonomy(self::$guide['oid']);
		if(count($cids)) {
			$dbm = DBM::instance();
			$que = "SELECT * FROM {taxonomy_terms} WHERE cid IN (".implode(",",$cids).") AND name LIKE '%".$q."%'";
			while($row = $dbm->getFetchArray($que)) {
				if(!$args['a'.$row['cid']])
					$args['a'.$row['cid']] = array();
				$args['a'.$row['cid']][] = $row['tid'];
			}
		}
		return $args;
	}

	public static function getList($q,$args=null) {
		$dbm = DBM::instance();
		if($q) {
			$args2 = self::getRelativeTerm($q);
		}
		if(@count($args) > 0) {
			if(@count($args2)) $args = array_merge($args,$args2);
		} else {
			if(@count($args2)) $args = $args2;
		}
		if($args) {
			$options = self::makeQuery($args);
			$que = "SELECT c.id,c.nid,c.parent,c.idx,c.subject,c.content FROM {taxonomy_term_relative} AS t LEFT JOIN {guide_clause} AS c ON t.`table` = 'guide_clause' AND t.rid = c.id WHERE ".$options." AND c.id IS NOT NULL GROUP BY t.rid ORDER BY c.idx ASC";
			$standard = array();
			while($row = $dbm->getFetchArray($que)) {
				$standard[] = self::fetchGuideClause($row);
			}
		}

		return $standard;
	}

	public static function getClause($id) {
		$dbm = DBM::instance();

		$que = "SELECT * FROM {guide_clause} WHERE id = ".$id;
		$row = $dbm->getFetchArray($que);
		$standard = self::fetchGuideClause($row);

		return $standard;
	}

	public static function makeQuery($args) {
		if(!is_array($args)) {
			$args = json_decode($args,true);
		}
		$cids = self::getTaxonomy(self::$guide['oid']);
		$c=0;
		foreach($args as $k => $v) { 
			$key = substr($k,1);
			if(in_array($key,$cids)) {
				if(!is_array($v)) $v = array($v);
				$que .= ($c++ ? " AND " : "")."t.tid IN (";
				$cc=0;
				foreach($v as $value) {
					$que .= ($cc++ ? "," : "").$value;
				}
				$que .= ") AND `table` = 'guide_clause'";
			}
		}

		return $que;
	}

	public static function fetchGuideClause($row) {
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		foreach($row as $k => $v) {
			if(is_string($v)) {
				$standard[$k] = stripslashes($v);
			} else if(is_array($v)) {
				foreach($v as $k2 => $v2) {
					if(is_string($v2))
						$standard['f'.$k2] = stripslashes($v2);
					else if(is_array($v2)) {
						$standard['f'.$k2] = array();
						foreach($v2 as $k3 => $v3) {
							$obj = array('tid'=>$k3);
							if(is_array($v3)) {
								$obj = array_merge($obj,$v3);
							}
							$standard['f'.$k2][] = $obj;
						}
					} else
						$standard['f'.$k2] = $v2;
				}
			} else {
				$standard[$k] = $v;
			}
		}
		return $standard;
	}
}
?>
