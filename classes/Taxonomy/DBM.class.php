<?php
namespace CADB\Taxonomy;

class DBM extends \CADB\Objects {
	private static $fields;
	private static $log;
	public static $errmsg;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function getTaxonomyList($active=1) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy} ".($active ? "WHERE `active` = '1' " : "")."ORDER BY cid ASC";
		while( $row = $dbm->getFetchArray($que) ) {
			$taxonomy[$row['cid']] = self::fetchTaxonomy($row);
		}
		return $taxonomy;
	}

	public static function getTaxonomy($cid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy} WHERE cid = ".$cid;
		$row = $dbm->getFetchArray($que);
		return self::fetchTaxonomy($row);
	}

	public static function searchTaxonomy($f,$v) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy} WHERE `".$f."` = '".$v."'";
		while( $row = $dbm->getFetchArray($que) ) {
			$taxonomy[$row['cid']] = self::fetchTaxonomy($row);
		}
		return $taxonomy;
	}

	public static function getTaxonomyTerms($tid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy_terms} WHERE tid = ".$tid." AND current = '1'";
		$row = $dbm->getFetchArray($que);
		return self::fetchTaxonomyTerms($row);
	}

	public static function getAllTaxonomyTerms() {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy_terms} ORDER BY cid ASC";
		while( $row = $dbm->getFetchArray($que) ) {
			$terms[$row['tid']] = self::fetchTaxonomyTerms($row);
		}
		return $terms;
	}

	public static function searchTerms($cid, $f,$v) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {taxonomy_terms} WHERE `cid` = ".$cid." AND `".$f."` = '".$v."' AND current = '1'";
		while( $row = $dbm->getFetchArray($que) ) {
			$terms[$row['tid']] = self::fetchTaxonomyTerms($row);
		}
		return $terms;
	}

	public static function insert($args) {
		$dbm = \CADB\DBM::instance();

		$que = "INSERT INTO {taxonomy} (`subject`,`skey`,`active`) VALUES (?,?,?)";
		$dbm->execute($que,array("sdd", $args['subject'],1,1));

		$insert_cid = $dbm->getLastInsertId();

		self::$log = "분류 [".$args['subject']."] 을 추가했습니다.\n";
		\CADB\Log::taxonomyLog('insert',$insert_cid,self::$log);

		$t_args = array(
			'cid' => $insert_cid,
			'parent' => 0,
			'idx' => 1,
			'nsubs' => 0,
			'name' => $args['subject']
		);

		self::insertTerm($t_args);

		return $insert_cid;
	}

	public static function insertTerm($args) {
		$dbm = \CADB\DBM::instance();

		if(!isset($args['parent'])) $args['parent'] = 0;
		else if($args['parent'] > 0) {
			$que = "SELECT * FROM {taxonomy_terms} WHERE `cid` = ".$args['cid']." AND `tid` = ".$args['parent'];
			$row = $dbm->getFetchArray($que);
			if(!$row) $args['parent'] = 0;
		}

		if(!$args['idx']) {
			$que = "SELECT max(idx) AS max_idx FROM {taxonomy_terms} WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent'];
			$row = $dbm->getFetchArray($que);
			$args['idx'] = ( $row['max_idx'] ? $row['max_idx'] : 0 ) + 1;
		} else {
			$que = "UPDATE {taxonomy_terms} SET idx = idx + 1 WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent']." AND idx >= ".$args['idx']." ORDER BY idx DESC";
			$dbm->query($que);
		}

		$que = "INSERT INTO {taxonomy_terms} (`cid`,`parent`,`idx`,`nsubs`,`name`,`current`,`active`,`from`,`to`,`created`) VALUES (?,?,?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("ddddsddddd",
			$args['cid'],
			$args['parent'],
			$args['idx'],
			($args['nsubs'] ? $args['nsubs'] : 0),
			$args['name'],
			1,
			1,
			0,
			0,
			time()
		));

		$insert_vid = $dbm->getLastInsertId();

		$que = "UPDATE {taxonomy_terms} SET tid = ? WHERE vid = ?";
		$dbm->execute($que,array("dd",$insert_vid,$insert_vid));

		if($args['parent']) {
			$que = "UPDATE {taxonomy_terms} SET nsubs = nsubs + 1 WHERE cid = ? AND tid = ?";
			$dbm->execute($que,array("dd", $args['cid'], $args['parent']));
		}

		self::$log = "분류항목 [".$args['name']."] 을 추가했습니다.\n";
		\CADB\Log::taxonomytermLog('insert',$args['cid'],$insert_vid,$insert_vid,self::$log);

		return $insert_vid;
	}

	public static function modify($taxonomy,$args) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {taxonomy} SET subject = ?, skey = ?, active = ? WHERE cid = ?";
		$dbm->execute($que, array("sddd",$args['subject'], $args['skey'], $args['active'], $args['cid']));

		self::$log = "분류 [".$taxonomy['name']."] 을 수정했습니다.\n";
		\CADB\Log::taxonomyLog('modify',$taxonomy['cid'],self::$log);

		return $args['cid'];
	}

	public static function modifyTerm($terms,$args) {
		$dbm = \CADB\DBM::instance();

		if($terms['parent'] != $args['parent']) {
			if(!$args['idx']) {
				$que = "SELECT max(idx) AS max_idx FROM {taxonomy_terms} WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent'];
				$row = $dbm->getFetchArray($que);
				$args['idx'] = ( $row['max_idx'] ? $row['max_idx'] : 0 ) + 1;
			} else {
				$que = "UPDATE {taxonomy_terms} SET idx = idx + 1 WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent']." AND idx >= ".$args['idx']." ORDER BY idx DESC";
				$dbm->query($que);
			}
			$que = "UPDATE {taxonomy_terms} SET `name` = ?, `parent` = ?, idx = ? WHERE `vid` = ? AND `tid` = ?";
			$dbm->execute($que, array("sddd", $args['name'], $args['parent'], $args['idx'], $args['vid'], $args['tid']));
			if($args['parent']) {
				$que = "UPDATE {taxonomy_terms} SET nsubs = nsubs + 1 WHERE cid = ? AND tid = ?";
				$dbm->execute($que,array("dd", $args['cid'], $args['parent']));
			}
			$que = "UPDATE {taxonomy_terms} SET idx = idx - 1 WHERE `cid` = ? AND `parent` = ? AND idx >= ? ORDER BY idx ASC";
			$dbm->execute($que, array("ddd",$terms['cid'], $terms['parent'], $terms['idx']));
			if($terms['parent']) {
				$que = "UPDATE {taxonomy_terms} SET nsubs = nsubs - 1 WHERE `cid` = ? AND `tid` = ?";
				$dbm->execute($que, array("dd", $terms['cid'], $terms['parent']));
			}
		} else {
			if(!$args['idx']) $args['idx'] = $terms['idx'];
			if($terms['idx'] != $args['idx']) {
				if($terms['idx'] > $args['idx']) {
					$que = "UPDATE {taxonomy_terms} SET idx = idx + 1 WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent']." AND idx >= ".$args['idx']." AND idx < ".$terms['idx']." ORDER BY idx DESC";
				} else {
					$que = "UPDATE {taxonomy_terms} SET idx = idx - 1 WHERE `cid` = ".$args['cid']." AND `parent` = ".$args['parent']." AND idx > ".$terms['idx']." AND idx <= ".$args['idx']." ORDER BY idx ASC";
				}
				$dbm->query($que);
			}
			$que = "UPDATE {taxonomy_terms} SET `name` = ?, `idx` = ? WHERE `vid` = ? AND `tid` = ?";
			$dbm->execute($que, array("sddd", $args['name'], $args['idx'], $args['vid'], $args['tid']));
		}

		self::$log = "분류항목 [".$terms['name']."] 을 수정했습니다.\n";
		\CADB\Log::taxonomytermLog('modify',$terms['cid'],$terms['tid'],$terms['vid'],self::$log);

		return $args['vid'];
	}

	public static function delete($args) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {taxonomy} WHERE cid = ?";
		$dbm->execute($que,array("d",$args['cid']));

		self::$log = "분류 [".$argis['name']."] 을 삭제했습니다.\n";
		\CADB\Log::taxonomyLog('delete',$args['cid'],self::$log);
	}

	public static function deleteTerm($terms) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {taxonomy_terms} WHERE `cid` = ? AND `tid` = ?";
		$dbm->execute($que, array("dd",$terms['cid'],$terms['tid']));

		$que = "UPDATE {taxonomy_terms} SET idx = idx - 1 WHERE `cid` = ? AND `parent` = ? AND idx >= ? ORDER BY idx ASC";
		$dbm->execute($que, array("ddd",$terms['cid'], $terms['parent'], $terms['idx']));

		if($terms['parent']) {
			$que = "UPDATE {taxonomy_terms} SET nsubs = nsubs - 1 WHERE `cid` = ? AND `tid` = ?";
			$dbm->execute($que, array("dd", $terms['cid'], $terms['parent']));
		}
		self::$log = "분류항목 [".$terms['name']."] 을 삭제했습니다.\n";
		\CADB\Log::taxonomytermLog('delete',$terms['cid'],$terms['tid'],$terms['vid'],self::$log);
	}

	private static function fetchTaxonomy($row) {
		if(!$row) return null;
		$row['subject'] = stripslashes($row['subject']);
		return $row;
	}

	private static function fetchTaxonomyTerms($row) {
		if(!$row) return null;
		$row['name'] = stripslashes($row['name']);
		return $row;
	}
}
