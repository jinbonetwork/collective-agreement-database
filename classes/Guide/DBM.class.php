<?php
namespace CADB\Guide;

class DBM extends \CADB\Objects {
	private static $fields;
	public static $errmsg;
	public static $oid;

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

		$que = "UPDATE {guide} SET subject = ?, content = ?, year = ?, cid = ? WHERE nid = ?";

		$dbm->execute($que,array("ssdsd",$args['subject'],$args['content'],$args['year'],$cid,$args['nid']));

		return $args['nid'];
	}

	public static function deleteGuide($guide) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {guide} WHERE nid = ?";

		$dbm->execute($que,array("d",$guide['nid']));

		if($guide['nid'] == $guide['vid']) {
			$que = "DELETE * FROM {guide_clause} WHERE nid = ?";
			$dbm->execute($que,array("d",$guide['vid']));

			$que = "DELETE * FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ?";
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
						switch(self::$fields[$key]['type']) {
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

		$dbm->execute($que,$q_args);

		return $args['id'];
	}

	public static function deleteClause($clause) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {guide_clause} WHERE id = ?";
		$dbm->execute($que,array("d",$clause['id']));

		$que = "UPDATE {guide_clause} SET idx = idx - 1 WHERE nid = ".$clause['nid']." AND parent = ".$clause['parent']." AND idx >= ".$clause['idx']." ORDER BY idx ASC";
		$dbm->query($que);

		$que = "DELETE * FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ? AND `fid` = ?";
		$dbm->execute($que,array("sdd",'guide_clause',$clause['nid'],$clause['id']));
	}
}
?>
