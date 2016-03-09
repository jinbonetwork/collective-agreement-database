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
		if(is_array($fields)) {
			foreach($fields as $k => $v) {
				$t = substr($k,0,1);
				if($t == 'a') continue;
				$key = (int)substr($k,1);
				self::$fields[$key] = $v;
			}
		}
	}

	public static function totalCnt($q,$args=null) {
		$dbm = DBM::instance();

		$que = self::makeQuery($q,$args,'count(*) AS cnt');
		if($que) {
			$row = $dbm->getFetchArray($que);
		}
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20,$args=null) {
		if(!$page) $page = 1;
		$dbm = DBM::instance();

		$que = self::makeQuery($q,$args,'o.*');
		if($que) {
			$que .= " ORDER BY o.depth ASC, o.oid ASC LIMIT ".(($page-1)*$limit).",".$limit;

			$organize = array();
			while($row = $dbm->getFetchArray($que)) {
				$organize[] = self::fetchOrganize($row);
			}
		}

		return $organize;
	}

	public static function makeQuery($q,$args=null,$result) {
		if($args) {
			$options = self::makeQueryOptions($args);
			if($options) {
				if($q) {
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options." AND match(o.fullname,o.f8,o.f9) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1'";
				} else {
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options." AND o.current = '1' AND o.active = '1'";
				}
			}
		} else if($q) {
			$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.`fullname`,o.`f8`,o.`f9`) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1'";
		}

		return $que;
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

	private static function makeQueryOptions($args) {
		if(!is_array($args)) {
			$args = @json_decode($args,true);
		}
		$c=0;
		$sub_query_cnt = 0;
		$sub_query = '';
		foreach($args as $k => $v) {
			$t = substr($k,0,1);
			if($t == 'a') continue;
			$key = (int)substr($k,1);
			if(self::$fields[$key]) {
				switch(self::$fields[$key]['type']) {
					case 'taxonomy':
						if(!is_array($v)) $v = array($v);
						if($sub_query_cnt)
							$sub_query .= " AND t.rid IN ( SELECT t.rid FROM {taxonomy_term_relative} AS t WHERE t.tid IN (".implode(",",$v).") AND t.`table` = 'organize'";
						else
							$sub_query .= " t.tid IN (".implode(",",$v).") AND t.`table` = 'organize'";
						$sub_query_cnt++;
						$c++;
						break;
					case 'int':
						if(self::$fields[$key]['iscolumn']) {
							if(is_array($v)) {
								$extra_que .= ($c++ ? " AND " : "")."o.f".$key." >= ".$v[0];
								if($v[1]) $extra_que .=" AND o.f".$key." <= ".$v[1];
							} else {
								$extra_que .= ($c++ ? " AND " : "")."o.f".$key." >= ".$v;
							}
						}
						break;
					default:
						if(self::$fields[$key]['iscolumn']) {
							$extra_que .= ($c++ ? " AND " : "")."o.f".$key." LIKE '%".$v."%'";
						}
						break;
				}
			}
		}

		if($sub_query_cnt) {
			$sub_query .= str_repeat(")",($sub_query_cnt-1));
			$que = $sub_query;
		}
		if($extra_que) {
			$que .= $extra_que;
		}

		return $que;
	}

	private static function fetchOrganize($row) {
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		if(!is_array($row)) return null;
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
