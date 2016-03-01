<?php
namespace CADB;

class Agreement extends \CADB\Objects  {
	private static $fields;
	private static $summary_method;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFieldInfo($active=1) {
		$dbm = DBM::instance();
		$que = "SELECT * FROM {fields} WHERE active = '".$active."' ORDER BY idx ASC";
		while($row = $dbm->getFetchArray($que)) {
			if($row['table'] == 'organize' || $row['table'] == 'agreement') {
				$row['subject'] = stripslashes($row['subject']);
				self::$fields['field'][$row['fid']] = $row;
			}
		}

		$cids = \CADB\Guide::getTaxonomy(1);
		if($cids) {
			$taxonomies = \CADB\Taxonomy::getTaxonomy($cids);
			foreach($taxonomies as $cid => $row) {
				self::$fields['taxonomy'][$cid] = $row;
			}
		}

		return self::$fields;
	}

	public static function totalCnt($q,$args=null) {
		$dbm = DBM::instance();

		$que = self::makeQuery($q,$args,"count(*) AS cnt");
		if($que) {
			$row = $dbm->getFetchArray($que);
		}
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20,$args=null) {
		if(!$page) $page = 1;
		$dbm = DBM::instance();

		$que = self::makeQuery($q,$args,"a.*,o.oid");
		if($que) {
			$que .= " ORDER BY o.depth ASC, o.oid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			$articles = array();
			while($row = $dbm->getFetchArray($que)) {
				$articles[] = self::fetchAgreement($row,true);
			}
		}

		return $articles;
	}

	public static function getAgreement($nid,$did=0,$current=1) {
		$dbm = DBM::instance();

		if($did) {
			$que = "SELECT * FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON (a.nid = r.nid AND a.did = r.did) WHERE `nid` = ".$nid." AND `did` = ".$did;
		} else {
			$que = "SELECT * FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON (a.nid = r.nid AND a.did = r.did) WHERE `nid` = ".$nid.($current ? " AND `current` = '1'" : "")." ORDER BY did DESC LIMIT 1";
		}
		$row = $dbm->getFetchArray($que);
		$article = self::fetchAgreement($row);

		return $article;
	}

	private static function makeQuery($q,$args=null,$result) {
		if($q) {
			self::$summary_method = array('type' => "string", 'value'=>$q);
		}
		if($args) {
			$type = self::checkQueryType($args);
			switch($type) {
				case 1:
					$options = self::makeArgsQuery($args,1);
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement_organize} AS r ON (t.rid = r.oid) LEFT JOIN {agreement} AS a ON (r.nid = a.nid) LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "")."a.current = '1'";
					break;
				case 2:
					$options = self::makeArgsQuery($args,2);
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement} AS a ON (t.rid = a.nid) LEFT JOIN {agreement_organize} AS r ON (t.rid = r.nid) LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "")."a.current = '1'";
					break;
				case 3:
					$sub_options = self::makeArgsQuery($args,1);
					$sub_que = "SELECT o.oid FROM {taxonomy_term_relative} AS t LEFT JOIN {organize} AS o ON t.rid = o.oid WHERE ".$sub_options.($sub_options ? " AND " : "")."o.current = '1' AND o.active = '1' GROUP BY o.oid";
					$options = self::makeArgsQuery($args,2);
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement_organize} AS r ON (t.rid = r.nid) LEFT JOIN {agreement} AS a ON t.rid = a.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "")."r.oid IN (".$sub_que.") AND a.current = '1'";
					break;
				default:
					break;
			}
		} else {
			if($q) {
				$que = "SELECT ".$result." FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON a.nid = r.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND a.current = '1'";
			} else {
				$que = "SELECT ".$result." FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON a.nid = r.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE a.current = '1'";
			}
		}

		return $que;
	}

	private static function checkQueryType($args) {
		$type = 0;
		$organize_type = $agreement_type = false;
		foreach($args as $k => $v) {
			$t = substr($k,0,1);
			$key = (int)substr($k,1);
			if($t == 'o' && self::$fields['field'][$key]) {
				switch(self::$fields[$key]['type']) {
					case 'organize':
						$organize_type = true;
						break;
					case 'agreement':
						$agreement_type = true;
						break;
					default:
						break;
				}
			} else if($t == 'a' && self::$fields['taxonomy'][$key]) {
				$agreement_type = true;
				if(!self::$summary_method) {
					self::$summary_method = array('type'=>'taxonomy','value'=>$v);
				}
			}
		}
		if($organize_type) $type += 1;
		if($agreement_type) $type += 2;

		return $type;
	}

	private static function makeArgsQuery($args,$type) {
		$key = (int)substr($k,1);
		$c=0;
		foreach($args as $k => $v) {
			$t = substr($k,0,1);
			$key = (int)substr($k,1);
			if($t == 'o' && self::$fields['field'][$key]) {
				switch(self::$fields[$key]['type']) {
					case 'taxonomy':
						if(!is_array($v)) $v = array($v);
						switch(self::$fields[$key]['table']) {
							case 'organize':
								if($type != 2) {
									$que .= ($c++ ? " AND " : "")."(t.`table` = 'organize' AND t.tid IN (".implode(",",$v)."))";
								}
							case 'agreement':
								if($type != 1) {
									$que .= ($c++ ? " AND " : "")."(t.`table` = 'agreement' AND t.tid IN (".implode(",",$v)."))";
								}
								break;
							default: break;
						}
						break;
					case 'int':
						switch(self::$fields[$key]['table']) {
							case 'organize':
								if($type != 2 && self::$fields[$key]['iscolumn']) {
									if(is_array($v)) {
										$que .= ($c++ ? " AND " : "")."o.f".$k." >= ".$v[0]." AND o.".$k." <= ".$v[1];
									} else {
										$que .= ($c++ ? " AND " : "")."o.f".$k." >= ".$v;
									}
								}
								break;
							default:
								break;
						}
						break;
					default:
						switch(self::$fields[$key]['table']) {
							case 'organize':
								if($type != 2 && self::$fields[$key]['iscolumn']) {
									$que .= ($c++ ? " AND " : "")."o.f".$k." LIKE '%".$v."%'";
								}
								break;
							default:
								break;
						}
						break;
				}
			} else if($t == 'a' && self::$fields['taxonomy'][$key]) {
				if($type != 1) {
					$que .= ($c++ ? " AND " : "")."(t.`table` = 'agreement' AND t.tid IN (".implode(",",$v)."))";
				}
			}
		}
		return $que;
	}

	public static function fetchAgreement($row,$summary=false) {
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		foreach($row as $k => $v) {
			if(in_array($k,  array('current','created'))) continue;
			if(is_string($v)) {
				$v = stripslashes($v);
				if($summary && $k == 'content') {
					$p = 0;
					switch(self::$summary_method['type']) {
						case 'string':
							$p = mb_stripos($v,self::$summary_method['value'],0,'utf-8');
							break;
						case 'taxonomy':
							if(!is_array(self::$summary_method['value'])) {
								$match = '<span id="cadb-taxo-term-'.self::$summary_method['value'].'">';
								$p = mb_stripos($v,$match,0,'utf-8');
							} else {
								foreach(self::$summary_method['value'] as $sv) {
									$match = '<span id="cadb-taxo-term-'.$sv.'">';
									$p = mb_stripos($v,$match,0,'utf-8');
									if($p) break;
								}
							}
							break;
						default:
							break;
					}
					$v = mb_substr(strip_tags(mb_substr($v,$p,500,'utf-8')),0,128,'utf-8');
				} 
				$article[$k] = $v;
			} else if(is_array($v)) {
				foreach($v as $k2 => $v2) {
					if(is_string($v2))
						$article['f'.$k2] = stripslashes($v2);
					else if(is_array($v2)) {
						$article['f'.$k2] = array();
						foreach($v2 as $k3 => $v3) {
							$obj = array('tid'=>$k3);
							if(is_array($v3)) {
								$obj = array_merge($obj,$v3);
							}
							$article['f'.$k2][] = $obj;
						}
					} else
						$article['f'.$k2] = $v2;
				}
			} else {
				$article[$k] = $v;
			}
		}
		return $article;
	}
}
?>