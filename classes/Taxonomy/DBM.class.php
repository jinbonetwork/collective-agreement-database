<?php
namespace CADB\Taxonomy;

class DBM extends \CADB\Objects {
	private static $fields;
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

		return $insert_vid;
	}

	public static function modify($taxonomy,$args) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {taxonomy} SET subject = ?, skey = ?, active = ? WHERE cid = ?";
		$dbm->execute($que, array("sddd",$args['subject'], $args['skey'], $args['active'], $args['cid']));

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

		return $args['vid'];
	}

	public static function delete($args) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {taxonomy} WHERE cid = ?";
		$dbm->execute($que,array("d",$args['cid']));
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
