<?php
namespace CADB\Guide;

class DBM extends \CADB\Objects {
	private static $fields;
	public static $errmsg;
	public static $oid;
	public static $parentIndex;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getFieldInfo($active=1) {
		if(!self::$fields)
			self::$fields = \CADB\Fields::getFields('guide',$active);
		return self::$fields;
	}

	public static function setOid($oid) {
		self::$oid = $oid;
	}

	public static function getTaxonomy($cid) {

		$cids = preg_split("/[, ]/i",$cid);
		return $cids;
	}

	public static function totalCnt($q) {
		$dbm = \CADB\DBM::instance();

		$que = self::makeQuery($q,"count(*) AS cnt");
		$row = $dbm->getFetchArray($que);

		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($q,$page=1,$limit=20) {
		if(!$page) $page = 1;
		$dbm = \CADB\DBM::instance();

		$que = self::makeQuery($q,"*");
		$que .= " ORDER BY nid DESC LIMIT ".(($page-1)*$limit).",".$limit;
		$guides = array();
		while($row = $dbm->getFetchArray($que)) {
			$guides[] = self::fetchGuide($row);
		}
		return $guides;
	}

	public static function getGuide($nid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {guide} WHERE nid = ".$nid;
		$row = $dbm->getFetchArray($que);
		return self::fetchGuide($row);
	}

	public static function getClauses($nid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT id,parent,idx,subject FROM {guide_clause} WHERE nid = ".$nid." ORDER BY parent ASC, idx ASC";
		$_clauses = array();
		while($row = $dbm->getFetchArray($que)) {
			$clauses[] = \CADB\Guide::fetchGuideClause($row);
		}

		return $clauses;
	}

	private static function makeQuery($q,$result) {
		$que = "SELECT ".$result." FROM {guide}";
		if(self::$oid) {
			$que .= " WHERE oid = ".self::$oid;
		}
		if($q) {
			$que .= (!self::$oid ? " WHERE " : " AND ")."subject LIKE '%".$q."%'";
		}
		return $que;
	}

	private static function fetchGuide($row) {
		if(!$row) return null;
		if($row['custom']) $row['custom'] = unserialize($row['custom']);
		foreach($row as $k => $v) {
			if($k == 'custom') continue;
			if(is_string($v)) {
				$row[$k] = stripslashes($v);
			} else {
				$row[$k] = $v;
			}
		}
		return $row;
	}

	public static function modifyGuide($guide,$args) {
		$dbm = \CADB\DBM::instance();

		if($args['cid']) {
			if(is_array($args['cid']))
				$cid = implode(",",$args['cid']);
			else
				$cid = trim($args['cid']);
		} else {
			$cid = '';
		}

		$que = "UPDATE {guide} SET subject = ?, content = ?, year = ?, cid = ?, current = ? WHERE nid = ?";

		$dbm->execute($que,array("ssdsdd",$args['subject'],$args['content'],$args['year'],$cid,($args['current'] ? 1 : 0), $args['nid']));

		if(!$guide['current'] && $args['current']) {
			$que = "UPDATE {guide} SET `current` = ? WHERE nid != ?";
			$dbm->execute($que,array("dd",0,$args['nid']));
		}

		return $args['nid'];
	}

	public static function deleteGuide($guide) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {guide} WHERE nid = ?";
		$dbm->execute($que,array("d",$guide['nid']));

		if($guide['nid'] == $guide['vid']) {
			$que = "DELETE FROM {guide_clause} WHERE nid = ?";
			$dbm->execute($que,array("d",$guide['vid']));

			$que = "DELETE FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ?";
			$dbm->execute($que,array("sd",'guide_clause',$guide['vid']));
		}
	}

	public static function getClauseTerms($nid,$id) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy_term_relative} WHERE `table` = 'guide_clause' AND `rid` = ".$nid." AND `fid` = ".$id;
		while($row = $dbm->getFetchArray($que)) {
			$taxo[$row['tid']] = $row;
		}
		return $taxo;
	}

	public static function getClauseTaxonomyTerms($nid,$id) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT r.*,t.cid,t.name FROM {taxonomy_term_relative} AS r LEFT JOIN {taxonomy_terms} AS t ON r.tid = t.tid WHERE r.`table` = 'guide_clause' AND r.`rid` = ".$nid." AND r.`fid` = ".$id;
		while($row = $dbm->getFetchArray($que)) {
			$taxo[$row['tid']] = $row;
		}
		return $taxo;
	}

	public static function addClause($args,$fields) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {guide_clause} SET idx = idx + 1 WHERE `parent` = ".$args['parent']." AND `idx` >= ".$args['idx']." ORDER BY idx DESC";
		$dbm->query($que);

		$que = "INSERT INTO {guide_clause} (`nid`,`parent`,`idx`,`subject`, `content`";
		$que2 = ") VALUES (?, ?, ?, ?, ?";
		$array1 = 'array("dddss';
		$array2 = '$'."args['nid'], ".'$'."args['parent'], ".'$'."args['idx'], ".'$'."args['subject'], ".'$'."args['content']";
		foreach($args as $k => $v) {
			if(substr($k,0,1) == 'f') {
				$key = (int)substr($k,1);
				switch($fields[$key]['iscolumn']) {
					case 1:
						$que .= ", `".$k."`";
						$que2 .= ", ?";
						$array2 .= ', $'."args['".$k."']";
						switch($fields[$key]['type']) {
							case 'int':
								$array1 .= 'd';
								break;
							default:
								$array1 .= 's';
								break;
						}
						break;
					default:
						$custom[$key] = $v;
						break;
				}
			}
		}

		$que .= ", `custom`";
		$que2 .= ", ?)";
		$que = $que.$que2;

		$array1 .= 's",';
		$array2 .= ", serialize(".'$'."custom))";
		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_id = $dbm->getLastInsertId();

		if($args['tid']) {
			if(!is_array($args['tid'])) {
				$tids = array($args['tid']);
			} else {
				$tids = $args['tid'];
			}
			for($i=0; $i<@count($args['tid']); $i++) {
				$que = "INSERT INTO {taxonomy_term_relative} (`tid`, `table`, `rid`, `fid`) VALUES (?,?,?,?)";
				$dbm->execute($que,array("dsdd",$tids[$i],'guide_clause',$args['nid'],$insert_id));
			}
		}

		return $insert_id;
	}

	public static function modifyClause($clause,$args,$fields) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {guide_clause} SET `subject` = ?, `content` = ?";
		$array1 = 'array("ss';
		$array2 = '$'."args['subject'], ".'$'."args['content']";
		foreach($args as $k => $v) {
			if(substr($k,0,1) == 'f') {
				$key = (int)substr($k,1);
				switch($fields[$key]['iscolumn']) {
					case 1:
						$que .= ", `".$k."` = ?";
						$array2 .= ', $'."args['".$k."']";
						switch($fields[$key]['type']) {
							case 'int':
								$array1 .= 'd';
								break;
							default:
								$array1 .= 's';
								break;
						}
						break;
					default:
						$custom[$key] = $v;
						break;
				}
			}
		}
		$que .= ", `custom` = ? WHERE `id` = ?";
		$array1 .= 'sd",';
		$array2 .= ", serialize(".'$'."custom), ".'$'."args['id'])";

		$eval_str = '$'."q_args = ".$array1.$array2.";";

		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$terms = self::getClauseTerms($args['nid'],$args['id']);
		if($args['tid']) {
			if(!is_array($args['tid'])) {
				$new_terms = array($args['tid']);
			} else {
				$new_terms = $args['tid'];
			}
		}

		self::rebuildTaxonomyTerms($args['nid'],$args['id'],$terms,$new_terms);

		return $args['id'];
	}

	public static function deleteClause($clause) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {guide_clause} WHERE id = ?";
		$dbm->execute($que,array("d",$clause['id']));

		$que = "UPDATE {guide_clause} SET idx = idx - 1 WHERE nid = ".$clause['nid']." AND parent = ".$clause['parent']." AND idx >= ".$clause['idx']." ORDER BY idx ASC";
		$dbm->query($que);

		$que = "DELETE FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ? AND `fid` = ?";
		$dbm->execute($que,array("sdd",'guide_clause',$clause['nid'],$clause['id']));
	}

	private static function rebuildTaxonomyTerms($nid,$id,$old_terms,$new_terms) {
		$dbm = \CADB\DBM::instance();
		$del_terms = array();
		$add_terms = array();
		if(is_array($old_terms)) {
			foreach($old_terms as $t => $terms) {
				if(@count($new_terms) > 0) {
					if(!in_array($t,$new_terms)) {
						$del_terms[] = $t;
					}
				} else {
					$del_terms[] = $t;
				}
			}
		}
		for($i=0; $i<@count($new_terms); $i++) {
			if(!$old_terms[$new_terms[$i]]) {
				$add_terms[] = $new_terms[$i];
			}
		}

		for($i=0; $i<@count($del_terms); $i++) {
			$que = "DELETE FROM {taxonomy_term_relative} WHERE `tid` = ? AND `table` = ? AND `rid` = ? AND `fid` = ?";
			$dbm->execute($que,array("dsdd",$del_terms[$i],'guide_clause',$nid,$id));
		}
		for($i=0; $i<@count($add_terms); $i++) {
			$que = "INSERT INTO {taxonomy_term_relative} (`tid`, `table`, `rid`, `fid`) VALUES (?,?,?,?)";
			$dbm->execute($que,array("dsdd",$add_terms[$i],'guide_clause',$nid,$id));
		}
	}

	public static function fork($nid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {guide} WHERE nid = ".$nid;
		$guide = $dbm->getFetchArray($que);

		if(!$guide) {
			self::setErrorMsg("존재하지 않는 모범단협입니다.");
			return -1;
		}

		$que = "INSERT INTO {guide} (";
		$que2 = ") VALUES (";
		$array1 = 'array("';
		$array2 = "";

		$c = 0;
		foreach($guide as $k => $v) {
			if($k == 'nid' || $k == 'vid') continue;
			if($k == 'created') continue;
			$que .= ($c ? ", " : "")."`".$k."`";
			$que2 .= ($c ? ", " : "")."?";
			if(is_numeric($v)) {
				$array1 .= 'd';
			} else {
				$guide[$k] = stripslashes($v);
				$array1 .= 's';
			}
			if($k == 'current') $guide[$k] = 0;
			$array2 .= ($c ? ", " : "").'$'.'guide['.$k.']';
			$c++;
		}

		$que .= ", `created`";
		$que2 .= ", ?)";
		$que = $que.$que2;

		$array1 .= 'd",';
		$array2 .= ", time())";
		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que."가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_nid = $dbm->getLastInsertId();

		$que = "UPDATE {guide} SET vid = ? WHERE nid = ?";
		if( $dbm->execute($que,array("dd",$insert_nid,$insert_nid)) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			self::rollback($insert_nid);
			return -1;
		}

		$clause = array();
		$que = "SELECT * FROM {guide_clause} WHERE nid = ".$nid." ORDER BY parent ASC, idx ASC";
		while($row = $dbm->getFetchArray($que)) {
			$clause[] = $row;
		}
		if(is_array($clause)) {
			foreach($clause as $cl) {
				if(self::forkClause($cl,$insert_nid) < 0) {
					self::rollback($insert_nid);
					return -1;
				}
			}
		}

		return $insert_nid;
	}

	public static function forkClause($row,$nid) {
		$dbm = \CADB\DBM::instance();

		$que = "INSERT INTO {guide_clause} (";
		$que2 = ") VALUES (";
		$array1 = 'array("';
		$array2 = "";

		$c = 0;
		$old_nid = $row['nid'];
		foreach($row as $k => $v) {
			if($k == 'id') continue;
			$que .= ($c ? ", " : "")."`".$k."`";
			$que2 .= ($c ? ", " : "")."?";
			if(is_numeric($v)) {
				$array1 .= 'd';
			} else {
				$row[$k] = stripslashes($v);
				$array1 .= 's';
			}
			if($k == 'nid') $row[$k] = $nid;
			if($k == 'parent' && (int)$v > 0) {
				if(!self::$parentIndex[$row['parent']]) {
					self::setErrorMsg($row['parent']."의 parentIndex가 연산되지 않았습니다.");
					return -1;
				}
				$row[$k] = self::$parentIndex[$row['parent']];
			}
			$array2 .= ($c ? ", " : "").'$'.'row['.$k.']';
			$c++;
		}

		$que2 .= ")";
		$que = $que.$que2;

		$array1 .= '",';
		$array2 .= ")";
		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que."가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_cid = $dbm->getLastInsertId();

		self::$parentIndex[$row['id']] = $insert_cid;

		$taxo_rel = self::getClauseTerms($old_nid,$row['id']);
		if(is_array($taxo_rel)) {
			foreach($taxo_rel as $t => $r) {
				$que = "INSERT INTO {taxonomy_term_relative} (`tid`,`table`,`rid`,`fid`) VALUES (?,?,?,?)";
				if($dbm->execute($que,array("dsdd",$t,'guide_clause',$nid,$insert_cid)) < 1) {
					self::setErrorMsg($que."가 DB에 반영되지 않았습니다.");
					return -1;
				}
			}
		}

		return $insert_cid;
	}

	private static function rollback($nid) {
		$que = "DELETE FROM {guide} WHERE nid = ?";
		$dbm->execute($que,array("d",$nid));

		$que = "DELETE FROM {guide_clause} WHERE nid = ?";
		$dbm->execute($que,array("d",$nid));

		$que = "DELETE FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ?";
		$dbm->execute($que,array("sd",'guide_clause',$nid));
	}

	private static function setErrorMsg($message) {
		self::$errmsg = $message;
	}

	public static function error() {
		return self::$errmsg;
	}
}
?>
