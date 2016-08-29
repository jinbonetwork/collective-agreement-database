<?php
namespace CADB\Log;

class DBM extends \CADB\Objects {
	private static $query;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function initQuery($args) {
		self::$query = $args;
	}

	public static function totalCnt() {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT count(*) AS cnt FROM {log}".self::makeQuery();
		$row = $dbm->getFetchArray($que);
		
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($page=1,$limit=50) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM {log}".self::makeQuery();
		$que .= " LIMIT ".( ($page-1)*$limit ).",".$limit;

		$log = array();
		while($row = $dbm->getFetchArray($que)) {
			$log[] = self::fetchLog($row);
		}
		return $log;
	}

	private static function makeQuery() {
		$q_cnt = 0;
		$que = "";
		if(@count(self::$query)) {
			foreach(self::$query as $k => $v) {
				switch($k) {
					case 'action':
						$que .= ($q_cnt++ ? " WHERE" : "")." action LIKE '".$v."%'"; 
						break;
					case 'name':
						$que .= ($q_cnt++ ? " WHERE" : "")." name = '".$v."'"; 
						break;
					case "start":
						$que .= ($q_cnt++ ? " WHERE" : "")." modified >= '".strtotime($v." 00:00:00"); 
						break;
					case "end":
						$que .= ($q_cnt++ ? " WHERE" : "")." modified <= '".strtotime($v." 00:00:00"); 
						break;
				}
			}
		}

		return $que;
	}

	private static function fetchLog($row) {
		if(!$row) return null;

		foreach($row as $k => $v) {
			if(is_string($v)) {
				$row[$k] = stripslashes($v);
			} else{
				$row[$k] = $v;
			}
			if($k == 'action') {
				$action = explode(":",$v);
				$row['table'] = $action[0];
				$row['type'] = $action[1];
			}
		}
		return $row;
	}
}
