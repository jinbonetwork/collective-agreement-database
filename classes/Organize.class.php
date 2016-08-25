<?php
namespace CADB;

class Organize extends \CADB\Objects  {
	private static $mode;
	private static $fields;
	private static $keyword;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function setMode($mode) {
		self::$mode = $mode;
	}

	public static function getFieldInfo($active=1) {
		if(!self::$fields)
			self::$fields = \CADB\Fields::getFields('organize',$active);
		return self::$fields;
	}

	public static function totalCnt($q,$args=null) {
		$dbm = \CADB\DBM::instance();

		if(!self::$fields) self::getFieldInfo();

		if(self::$mode == 'depth') {
			$que = self::makeDepthQuery($q,$args,'count(*) AS cnt');
		} else {
			$que = self::makeQuery($q,$args,'count(*) AS cnt');
		}
		if($que) {
			$row = $dbm->getFetchArray($que);
		}
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20,$args=null) {
		if(!$page) $page = 1;
		$dbm = \CADB\DBM::instance();

		if(!self::$fields) self::getFieldInfo();

		if($q) {
			self::$keyword = $q;
		}
		if(self::$mode == 'depth') {
			$que = self::makeDepthQuery($q,$args,'o.*');
		} else {
			$que = self::makeQuery($q,$args,'o.*');
		}
		if($que) {
			if(self::$mode == 'admin') {
				if($limit == 0) {
					$que .= " ORDER BY o.p1 ASC, o.p2 ASC, o.p3 ASC, o.p4 ASC";
				} else {
					$que .= " ORDER BY o.p1 ASC, o.p2 ASC, o.p3 ASC, o.p4 ASC LIMIT ".(($page-1)*$limit).",".$limit;
				}
			} else if(self::$mode == 'depth') {
				$que .= " ORDER BY o.depth ASC, o.oid ASC";
			} else {
				$que .= " ORDER BY o.depth ASC, o.oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			}

			$organize = array();
			while($row = $dbm->getFetchArray($que)) {
				$organize[] = self::fetchOrganize($row);
			}
		}

		return self::getAgreementList($organize);
	}

	private static function getAgreementList($organize) {
		$o = array();
		if(is_array($organize)) {
			foreach($organize as $og) {
				$o[] = $og['oid'];
			}
		}
		if(count($o) > 0) {
			$dbm = \CADB\DBM::instance();

			$que = "SELECT r.*, a.subject FROM {agreement_organize} AS r LEFT JOIN {agreement} AS a ON (r.nid = a.nid AND r.did = a.did) WHERE r.oid IN (".implode(",",$o).")";
			while($row = $dbm->getFetchArray($que)) {
				$ag[$row['oid']][$row['nid']] = array('did'=>$row['did'],'subject'=>stripslashes($row['subject']));
			}

			for($i=0; $i<@count($organize); $i++) {
				$_oid = $organize[$i]['oid'];
				if($ag[$_oid]) {
					$organize[$i]['nid'] = array();
					foreach($ag[$_oid] as $nid => $v) {
						$organize[$i]['articles'][] = array('nid'=>$nid, 'did'=>$v['did'], 'subject'=>$v['subject']);
					}
				}
			}
		}

		return $organize;
	}

	public static function makeQuery($q,$args=null,$result) {
		if($args) {
			$options = self::makeQueryOptions($args);
			if($options) {
				if($q) {
//					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options." AND match(o.fullname,o.f8,o.f9) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1'";
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options." AND match(o.fullname,o.f8,o.f9) against('".$q."' IN BOOLEAN MODE) AND o.current = '1' AND o.active = '1'";
				} else {
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$options." AND o.current = '1' AND o.active = '1'";
				}
			} else if($q) {
//				$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.`fullname`,o.`f8`,o.`f9`) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1'";
				$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.`fullname`,o.`f8`,o.`f9`) against('".$q."' IN BOOLEAN MODE) AND o.current = '1' AND o.active = '1'";
			}
		} else if($q) {
//			$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.`fullname`,o.`f8`,o.`f9`) against('".$q."' IN NATURAL LANGUAGE MODE) AND o.current = '1' AND o.active = '1'";
			$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.`fullname`,o.`f8`,o.`f9`) against('".$q."' IN BOOLEAN MODE) AND o.current = '1' AND o.active = '1'";
		} else if(self::$mode == 'admin') {
			$que = "SELECT ".$result." FROM {organize} AS o WHERE o.current = '1' AND o.active = '1'";
		}

		return $que;
	}

	public static function makeDepthQuery($q,$args=null,$result) {
		if($args) {
			$options = self::makeQueryDepthOptions($args);
			if($options) {
				$que = "SELECT ".$result." FROM {organize} AS o WHERE ".$options;
				if($q) {
					$que .= " AND match(o.fullname,o.f8,o.f9) against('".$q."' IN BOOLEAN MODE)";
				}
				$que .= " AND o.current = '1' AND o.active = '1'";
			} else if($q) {
				$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.fullname,o.f8,o.f9) against('".$q."' IN BOOLEAN MODE) AND o.current = '1' AND o.active = '1'";
			}
		} else if($q) {
			$que = "SELECT ".$result." FROM {organize} AS o WHERE match(o.fullname,o.f8,o.f9) against('".$q."' IN BOOLEAN MODE) AND o.current = '1' AND o.active = '1'";
		}

		return $que;
	}

	public static function getOrganizeByOid($oid,$current=1) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {organize} WHERE `oid` = ".$oid.($current ? " AND `current` = '1' AND `active` = '1'" : "")." ORDER BY vid DESC LIMIT 1";
		$row = $dbm->getFetchArray($que);
		$organize = self::fetchOrganize($row);

		return $organize;
	}

	public static function getOrganizeByVid($vid) {
		$dbm = \CADB\DBM::instance();

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

	private static function makeQueryDepthOptions($args) {
		if(!is_array($args)) {
			$args = @json_decode($args,true);
		}
		$c=0;
		$query = '';
		foreach($args as $k => $v) {
			$t = substr($k,0,1);
			if($t != 'p') continue;
			if(!is_numeric($v)) continue;
			if($k == 'pdepth') $depth = $v;
			else $_args[$k] = $v;
		}
		if($_args) {
			ksort($_args);
			foreach($_args as $k => $v) {
				$que .= ($c++ ? " AND " : "")."o.".$k."=".$v;
				$key = (int)substr($k,1);
			}
		}
		if($depth) {
			$que .= ($c++ ? " AND " : "")."o.depth=".$depth;
		}

		return $que;
	}

	private static function fetchOrganize($row) {
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		if(!is_array($row)) return null;
		foreach($row as $k => $v) {
			if(in_array($k,  array('current','active','from','to','created'))) continue;
			if(is_string($v)) {
				$v = stripslashes($v);
				if( self::$keyword &&
					($k == 'fullname' ||
						(substr($k,0,1) == 'f' && self::$fields[(int)substr($k,1)]['indextype'] == 'fulltext' )
					)
				) {
					$p = mb_stripos($v, self::$keyword, 0, 'utf-8');
					if($p) {
						$v = str_replace(self::$keyword, '<span class="cadb-keyword">'.self::$keyword.'</span>',$v);
					}
				}
				$organize[$k] = $v;
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
		$organize['organizes'] = self::makeMap($organize);
		return $organize;
	}

	private static function makeMap($organize) {
		$org_map = array();
		$org_map[] = array(
			'oid' => ($organize['depth'] > 1 ? $organize['p1'] : 0),
			'name' => $organize['nojo']
		);
		for($i=2; $i<($organize['depth']); $i++) {
			if($organize['p'.$i]) {
				$org_map[] = array(
					'oid' => $organize['p'.$i],
					'name' => $organize['sub'.($i-1)]
				);
			}
		}
		if($organize['depth'] > 1) {
			$org_map[] = array(
				'oid' => 0,
				'name' => $organize['sub'.($organize['depth']-1)]
			);
		}
		return $org_map;
	}
}
?>
