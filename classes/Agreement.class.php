<?php
namespace CADB;

class Agreement extends \CADB\Objects  {
	private static $mode;
	private static $fields;
	private static $summary_method;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function setMode($mode) {
		self::$mode = $mode;
	}

	public static function getFieldInfo($active=1) {
		if(!self::$fields['field']) {
			self::$fields['field'] = \CADB\Fields::getFields(array('organize','agreement'),$active);
		}

		if(!self::$fields['taxonomy']) {
			$cids = \CADB\Guide::getTaxonomy(1);
			self::setTaxonomy($cids);
		}

		return self::$fields;
	}

	public static function setFieldInfo($fields) {
		if(is_array($fields)) {
			foreach($fields as $k => $v) {
				if($v['table'] == 'organize' || $v['table'] == 'agreement') {
					$v['subject'] = stripslashes($v['subject']);
					self::$fields['field'][$k] = $v;
				}
			}
		}
	}

	public static function setTaxonomy($cids) {
		if(!self::$fields['taxonomy'] && $cids) {
			$taxonomies = \CADB\Taxonomy::getTaxonomy($cids);
			foreach($taxonomies as $cid => $row) {
				self::$fields['taxonomy'][$cid] = $row;
			}
		}
	}

	public static function totalCnt($q,$args=null) {
		$dbm = \CADB\DBM::instance();

		self::getFieldInfo();

		$que = self::makeQuery($q,$args,"count(*) AS cnt");
		if($que) {
			$row = $dbm->getFetchArray($que);
		}
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20,$args=null) {
		if(!$page) $page = 1;
		$dbm = \CADB\DBM::instance();

		self::getFieldInfo();

		$que = self::makeQuery($q,$args,"a.*");
		if($que) {
//			$que .= " GROUP BY a.nid ORDER BY r.nid ASC LIMIT ".(($page-1)*$limit).",".$limit;
			$que .= " GROUP BY a.nid LIMIT ".(($page-1)*$limit).",".$limit;
			$articles = array();
			while($row = $dbm->getFetchArray($que)) {
				$articles[] = self::fetchAgreement($row,true);
			}
		}

		return $articles;
	}

	public static function getAgreement($nid,$did=0,$current=1) {
		$dbm = \CADB\DBM::instance();

		if($did) {
			$que = "SELECT * FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON (a.nid = r.nid AND a.did = r.did) WHERE a.`nid` = ".$nid." AND a.`did` = ".$did;
		} else {
			$que = "SELECT * FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON (a.nid = r.nid AND a.did = r.did) WHERE a.`nid` = ".$nid.($current ? " AND a.`current` = '1'" : "")." ORDER BY a.did DESC LIMIT 1";
		}
		$row = $dbm->getFetchArray($que);
		$article = self::fetchAgreement($row);

		return $article;
	}

	public static function getAgreementsByOid($oid,$vid=0,$current=1) {
		$dbm = \CADB\DBM::instance();

		if($vid) {
			$que = "SELECT a.* FROM {agreement_organize} AS r LEFT JOIN {agreement} AS a ON ( r.nid = a.nid AND r.did = a.did ) WHERE r.oid = ".$oid." AND r.vid = ".$vid;
		} else {
			$que = "SELECT a.* FROM {agreement_organize} AS r LEFT JOIN {agreement} AS a ON ( r.nid = a.nid AND r.did = a.did ) WHERE r.oid = ".$oid.($current ? " AND a.`current` = '1'" : "")." ORDER BY r.did";
		}
		$articles = array();
		while($row = $dbm->getFetchArray($que)) {
			if($row) {
				$article[] = self::fetchAgreement($row);
			}
		}

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
//					$que = "SELECT ".$result." FROM {agreement_organize} AS r LEFT JOIN {agreement} AS a ON (r.nid = a.nid) WHERE ".($options ? "r.oid IN (".$options.") AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "")."a.current = '1'";
					$que = "SELECT ".$result." FROM {agreement_organize} AS r LEFT JOIN {agreement} AS a ON (r.nid = a.nid) WHERE ".($options ? "r.oid IN (".$options.") AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN BOOLEAN MODE) AND " : "")."a.current = '1'";
					break;
				case 2:
					$options = self::makeArgsQuery($args,2);
//					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement} AS a ON (t.`table` = 'agreement' AND t.rid = a.nid) WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "")."a.current = '1'";
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement} AS a ON (t.`table` = 'agreement' AND t.rid = a.nid) WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN BOOLEAN MODE) AND " : "")."a.current = '1'";
					break;
				case 3:
					$sub_options = self::makeArgsQuery($args,1);
					$options = self::makeArgsQuery($args,2);
//					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement_organize} AS r ON (t.`table` = 'agreement' AND t.rid = r.nid) LEFT JOIN {agreement} AS a ON t.rid = a.nid WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND " : "").($sub_options ? "r.oid IN (".$sub_options.") AND " : "")."a.current = '1'";
					$que = "SELECT ".$result." FROM {taxonomy_term_relative} AS t LEFT JOIN {agreement_organize} AS r ON (t.`table` = 'agreement' AND t.rid = r.nid) LEFT JOIN {agreement} AS a ON t.rid = a.nid WHERE ".$options.($options ? " AND " : "").($q ? "match(a.subject,a.content) against('".$q."' IN BOOLEAN MODE) AND " : "").($sub_options ? "r.oid IN (".$sub_options.") AND " : "")."a.current = '1'";
					break;
				default:
					break;
			}
		} else {
			if($q) {
//				$que = "SELECT ".$result." FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON a.nid = r.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE match(a.subject,a.content) against('".$q."' IN NATURAL LANGUAGE MODE) AND a.current = '1'";
				$que = "SELECT ".$result." FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON a.nid = r.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE match(a.subject,a.content) against('".$q."' IN BOOLEAN MODE) AND a.current = '1'";
			} else {
				if(self::$mode == 'admin') {
					$que = "SELECT ".$result." FROM {agreement} AS a LEFT JOIN {agreement_organize} AS r ON a.nid = r.nid LEFT JOIN {organize} AS o ON r.oid = o.oid WHERE a.current = '1'";
				}
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
				switch(self::$fields['field'][$key]['table']) {
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

		if($type == 1) {
			\CADB\Organize::getFieldInfo();
			$que = \CADB\Organize::makeQuery($q,$args,'t.rid');
		} else {
			foreach($args as $k => $v) {
				$t = substr($k,0,1);
				$key = (int)substr($k,1);
				if($t == 'o' && self::$fields['field'][$key]) {
					switch(self::$fields[$key]['type']) {
						case 'taxonomy':
							if(!is_array($v)) $v = array($v);
							switch(self::$fields[$key]['table']) {
								case 'agreement':
									$que .= ($c++ ? " AND " : "")."(t.`table` = 'agreement' AND t.tid IN (".implode(",",$v)."))";
									break;
								default: break;
							}
							break;
						default:
							break;
					}
				} else if($t == 'a' && self::$fields['taxonomy'][$key]) {
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
				if($summary && ($k == 'subject' || $k == 'content')) {
					$matched = self::$summary_method['value'];
					if(is_array($matched)) {
						foreach($matched as $m) {
							if($m) {
//								$v = str_replace($m,'<span class="keyword">'.$m.'</span>',$v);
							}
						}
					} else if($matched) {
//						$v = str_replace($matched,'<span class="keyword">'.$matched.'</span>',$v);
					}
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
