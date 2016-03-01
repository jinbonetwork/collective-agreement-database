<?php
namespace CADB;

class Organize extends \CADB\Objects  {
	private static $fields;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFieldInfo($active=1) {
		$dbm = DBM::instance();
		$que = "SELECT * FROM {fields} WHERE `table` = 'organize' AND active = '".$active."' ORDER BY idx ASC";
		while($row = $dbm->getFetchArray($que)) {
			$row['subject'] = stripslashes($row['subject']);
			self::$fields[$row['fid']] = $row;
		}
		return self::$fields;
	}

	public static function setFieldInfo($fields) {
		self::$fields = $fields;
	}

	public static function totalCnt($q,$args=null) {
		$dbm = DBM::instance();
		if($args) {
			$options = self::makeQuery($args);
			if($q) {
				$que = "SELECT count(*) as cnt FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options.($options ? " AND " : "")."match(o.fullname,o.f8,o.f9) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1' GROUP BY o.oid";
			} else {
				$que = "SELECT count(*) as cnt FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options.($options ? " AND " : "")."o.current = '1' AND o.active = '1' GROUP BY o.oid";
			}
		} else {
			if($q) {
				$que = "SELECT count(*) AS cnt FROM {organize} WHERE match(`fullname`,`f8`,`f9`) against('".$q."' IN NATURAL LANGUAGE MODE) AND current = '1' AND active = '1'";
			} else {
				$que = "SELECT count(*) AS cnt FROM {organize} WHERE current = '1' AND active = '1'";
			}
		}
		$row = $dbm->getFetchArray($que);
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20,$args=null) {
		if(!$page) $page = 1;
		$dbm = DBM::instance();
		if($args) {
			$options = self::makeQuery($args);
			if($q) {
				$que = "SELECT o.* FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options.($options ? " AND " : "")."match(o.fullname,o.f8,o.f9) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1' GROUP BY o.oid ORDER BY o.depth ASC, o.oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			} else {
				$que = "SELECT o.* FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options.($options ? " AND " : "")."o.current = '1' AND o.active = '1' GROUP BY o.oid ORDER BY o.depth ASC, o.oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			}
		} else {
			if($q) {
				$que = "SELECT * FROM {organize} WHERE match(`fullname`,`f8`,`f9`) against('".$q."' IN NATURAL LANGUAGE MODE) AND current = '1' AND active = '1' ORDER BY depth ASC, oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			} else {
				$que = "SELECT * FROM {organize} WHERE current = '1' AND active = '1' ORDER BY depth ASC, oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			}
		}
		$organize = array();
		while($row = $dbm->getFetchArray($que)) {
			$organize[] = self::fetchOrganize($row);
		}

		return $organize;
	}

	public static function getOrganizeByOid($oid,$current=1) {
		$dbm = DBM::instance();

		$que = "SELECT * FROM {organize} WHERE `oid` = ".$oid.($current ? " AND `current` = '1' AND `active` = '1'" : "")." ORDER BY vid DESC LIMIT 1";
		$row = $dbm->getFetchArray($que);
		$organize = self::fetchOrganize($row);

		return $organize;
	}

	public static function getOrganizeByVid($vid) {
		$dbm = DBM::instance();

		$que = "SELECT * FROM {organize} WHERE `vid` = ".$vid;
		$row = $dbm->getFetchArray($que);
		$organize = self::fetchOrganize($row);

		return $organize;
	}

	public static function makeQuery($args) {
		if(!is_array($args)) {
			$args = json_decode($args,true);
		}
		$c=0;
		foreach($args as $k => $v) {
			$key = (int)substr($k,1);
			if(self::$fields[$key]) {
				switch(self::$fields[$key]['type']) {
					case 'taxonomy':
						if(!is_array($v)) $v = array($v);
						$que .= ($c++ ? " AND " : "")."t.tid IN (";
						$cc=0;
						foreach($v as $value) {
							$que .= ($cc++ ? "," : "").$value;
						}
						$que .= ")";
						break;
					case 'int':
						if(self::$fields[$key]['iscolumn']) {
							if(is_array($v)) {
								$que .= ($c++ ? " AND " : "")."o.f".$k." >= ".$v[0]." AND o.".$k." <= ".$v[1];
							} else {
								$que .= ($c++ ? " AND " : "")."o.f".$k." >= ".$v;
							}
						}
						break;
					default:
						if(self::$fields[$key]['iscolumn']) {
							$que .= ($c++ ? " AND " : "")."o.f".$k." LIKE '%".$v."%'";
						}
						break;
				}
			}
		}

		return $que;
	}

	public static function fetchOrganize($row) {
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		foreach($row as $k => $v) {
			if(in_array($k,  array('current','active','from','to','created'))) continue;
			if(is_string($v)) {
				$organize[$k] = stripslashes($v);
			} else if(is_array($v)) {
				foreach($v as $k2 => $v2) {
					if(is_string($v2))
						$organize['f'.$k2] = stripslashes($v2);
					else if(is_array($v2)) {
						$organize['f'.$k2] = array();
						foreach($v2 as $k3 => $v3) {
							$obj = array('tid'=>$k3);
							if(is_array($v3)) {
								$obj = array_merge($obj,$v3);
							}
							$organize['f'.$k2][] = $obj;
						}
					} else
						$organize['f'.$k2] = $v2;
				}
			} else {
				$organize[$k] = $v;
			}
		}
		return $organize;
	}
}
?>
