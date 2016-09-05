<?php
namespace CADB\Fields;

class DBM extends \CADB\Objects {
	public static $errmsg;
	private static $log;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function searchField($f,$v) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {fields} WHERE `".$f."` = '".$v."'";
		while( $row = $dbm->getFetchArray($que) ) {
			$fields[$row['fid']] = self::fetchField($row);
		}
		return $fields;
	}

	public static function getField($fid) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {fields} WHERE fid = ".$fid;
		return self::fetchField( $dbm->getFetchArray($que) );
	}

	public static function insert($args) {
		$dbm = \CADB\DBM::instance();

		if(!$args['idx']) {
			$que = "SELECT max(idx) AS max_idx FROM {fields} WHERE `table` = '".$args['table']."'"; 
			$row = $dbm->getFetchArray($que);
			$args['idx'] = ( $row['max_idx'] ? $row['max_idx'] : 0 ) + 1;
		} else {
			$row = self::getFieldByIndex($args['table'],$args['idx']);
			if($row) {
				$que = "UPDATE {fields} SET idx = idx + 1 WHERE `table` = '".$args['table']."' AND idx >= ".$args['idx']." ORDER BY idx DESC";
				$dbm->query($que);
			}
		}

		$que = "INSERT INTO {fields} (`table`,`idx`,`subject`,`iscolumn`,`type`,`multiple`,`required`,`cid`,`active`,`system`,`autocomplete`, `indextype`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
		$dbm->execute($que,array("sdsdsdddddds",
			$args['table'],
			$args['idx'],
			$args['subject'],
			($args['iscolumn'] ? 1 : 0),
			$args['type'],
			($args['multiple'] ? 1 : 0),
			($args['required'] ? 1 : 0),
			($args['cid'] ? $args['cid'] : 0),
			($args['active'] ? 1 : 0),
			($args['system'] ? 1 : 0),
			($args['autocomplete'] ? 1 : 0),
			($args['indextype'] ? $args['indextype'] : 'none')
		));

		$insert_fid = $dbm->getLastInsertId();

		self::$log = "필드: ".$args['subject']." 를 추가했습니다.\n";
		\CADB\Log::fieldLog('insert',$insert_fid,self::$log);

		return $insert_fid;
	}

	public static function modify($field, $args) {
		$dbm = \CADB\DBM::instance();

		$que = "UPDATE {fields} SET subject = ?, multiple = ?, required = ?, active = ?, system = ?, autocomplete = ?, indextype = ? WHERE fid = ?";

		$dbm->execute($que,array("sdddddsd",
			$args['subject'],
			($args['multiple'] ? 1 : 0),
			($args['required'] ? 1 : 0),
			($args['active'] ? 1 : 0),
			($args['system'] ? 1 : 0),
			($args['autocomplete'] ? 1 : 0),
			($args['indextype'] ? $args['indextype'] : 'none'),
			$args['fid']
		));

		self::$log = "필드: ".$args['subject']." 를 수정했습니다.\n";
		\CADB\Log::fieldLog('modify',$args['fid'],self::$log);

		return $args['fid'];
	}

	public static function delete($field) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {fields} WHERE fid = ?";

		$dbm->execute($que,array("d",$field['fid']));

		$que = "UPDATE {fields} SET idx = idx - 1 WHERE `table` = '".$field['table']."' AND idx >= ".$field['idx']." ORDER BY idx ASC";

		self::$log = "필드: ".$field['subject']." 를 삭제했습니다.\n";
		\CADB\Log::fieldLog('delete',$field['fid'],self::$log);

		$dbm->query($que);
	}

	public static function getFieldByIndex($table,$idx) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {fields} WHERE `table` = '".$table."' AND idx = ".$idx;
		$row = $dbm->getFetchArray($que);
		return $row;
	}

	public static function addColumn($fid,$args) {
		$dbm = \CADB\DBM::instance();

		switch($args['type']) {
			case 'char':
				$type = 'char(255)';
				break;
			case 'int':
				$type = 'int(10)';
				break;
			case 'taxonomy':
				$type = 'text';
				break;
			default:
				$type = $args['type'];
				break;
		}
		$que = "ALTER TABLE {".$args['table']."} ADD COLUMN `f".$fid."` ".$type;
		$dbm->query($que);

		self::$log .= "필드: ".$fid." 를 ".$table." 에 칼럼으로 추가했습니다.\n";

		if($args['indextype'] == 'fulltext') {
			self::updateIndex($args);
		}
		\CADB\Log::fieldLog('delete',$fid,self::$log);
	}

	public static function dropColumn($fid,$args) {
		$dbm = \CADB\DBM::instance();

		$que = "ALTER TABLE {".$args['table']."} DROP COLUMN `f".$fid."`";
		$dbm->query($que);

		self::$log .= "필드: ".$fid." 를 ".$table." 에서 삭제했습니다.\n";

		if($args['indextype'] == 'fulltext') {
			self::updateIndex($args);
		}
		\CADB\Log::fieldLog('delete',$fid,self::$log);
	}

	public static function updateIndex($args) {
		$dbm = \CADB\DBM::instance();
		$context = \CADB\Model\Context::instance();

		$fulltext = array();
		switch($args['table']) {
			case 'organize':
				$fulltext[] = 'fullname';
				break;
			case 'agreement':
				$fulltext[] = 'subject';
				$fulltext[] = 'content';
				break;
			default:
				break;
		}
		$que = "SELECT * FROM {fields} WHERE `table` = '".$args['table']."' WHERE indextype = 'fulltext'";
		while( $row = $dbm->getFetchArray($que) ) {
			$fulltext[] = "f".$row['fid'];
		}

		if( count($fulltext) > 0 ) {
			$que = "ALTER TABLE {".$args['table']."} DROP INDEX `skey`";
			$dbm->query($que);

			$que = "ALTER TABLE {".$args['table']."} ADD FULLTEXT 'skey` (";
			$c = 0;
			foreach($fulltext as $f) {
				$que .= ($c++ ? "," : "")."`".$f."`";
			}
			$que .= ") WITH PARSER ".$context->getProperty('database.fulltext');
			$dbm->query($que);
			self::$log .= "테이블: ".$args['table']."의 fulltext index를 수정했습니다.\n";
		}
	}

	public static function removeTaxonomyField($table,$fid) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM {taxonomy_term_relatve} WHERE `table` = ? AND `fid` = ?";
		$dbm->execute($que,array("sd",$table,$fid));

		self::$log .= "테이블: ".$table."의 필드: ".$fid."와 연관된 관계테이블을 삭제했습니다.\n";
	}

	public static function resort($table,$index) {
		$dbm = \CADB\DBM::instance();

		foreach($index as $fid => $idx) {
			$que = "UPDATE {fields} SET `idx` = ? WHERE `table` = ? AND `fid` = ?";
			$dbm->execute($que,array("dsd",($idx+1),$table,$fid));
		}
		self::$log .= "테이블: ".$table."의 필드순서를 재조정했습니다.\n";
		\CADB\Log::fieldLog('modify',0,self::$log);
	}

	private static function fetchField($row) {
		if(!$row) return null;
		$row['subject'] = stripslashes($row['subject']);
		return $row;
	}
}
?>
