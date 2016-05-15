<?php
namespace CADB\Organize;

class DBM extends \CADB\Objects  {
	private static $fields;
	public static $errmsg;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function insert($fields,$args,$revision = false) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		if($args['nojo']) {
			$fullname = $args['nojo'];
			$depth = 1;
		}
		if(empty($args['p1'])) $args['p1'] = 0;
		if($args['sub1']) {
			$fullname .= ($fullname ? " " : "").$args['sub1'];
			$depth = 2;
		}
		if(empty($args['p2'])) $args['p2'] = 0;
		if($args['sub2']) {
			$fullname .= ($fullname ? " " : "").$args['sub2'];
			$depth = 3;
		}
		if(empty($args['p3'])) $args['p3'] = 0;
		if($args['sub3']) {
			$fullname .= ($fullname ? " " : "").$args['sub3'];
			$depth = 4;
		}
		if(empty($args['p4'])) $args['p4'] = 0;
		if($args['sub4']) {
			$fullname .= ($fullname ? " " : "").$args['sub4'];
			$depth = 5;
		}

		if(self::checkParent($depth,$args) < 0) {
			return -1;
		}

		$que = "INSERT INTO {organize} (".($revision == true ? "`vid`, " : "")."`p1`, `p2`, `p3`, `p4`, `depth`, `nojo`, `sub1`, `sub2`, `sub3`, `sub4`, `fullname`";
		$que2 = ") VALUES (".($revision == true ? "?, " : "")."?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
		$array1 = 'array("'.($revision ? 'd' : '').'dddddssssss';
		$array2 = ($revision ? '$'.$args['vid'].',' : '').'$'."args['p1'], ".'$'."args['p2'], ".'$'."args['p3'], ".'$'."args['p4'], ".'$'."depth, ".'$'."args['nojo'], ".'$'."args['sub1'], ".'$'."args['sub2'], ".'$'."args['sub3'], ".'$'."args['sub4'], ".'$'."fullname";
		foreach($args as $k => $v) {
			if(substr($k,0,1) == 'f') {
				$key = (int)substr($k,1);
				switch(self::$fields[$key]['iscolumn']) {
					case 1:
						$que .= ", `f".$key."`";
						$que2 .= ", ?";
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
					case 0:
						switch(self::$fields[$key]['type']) {
							case 'taxonomy':
								$cid = self::$fields[$key]['cid'];
								foreach($v as $t) {
									$custom[$key][$t['tid']] = array(
										'cid' => $t['cid'],
										'vid' => ($t['vid'] ? $t['vid'] : $t['tid']),
										'name' => $t['name']
									);
									$taxonomy_map[$cid]['add'][$t['tid']] = array(
										'oid'=>0,
										'ovid'=>($revision ? $args['vid'] : 0),
										'vid'=>($t['vid'] ? $t['vid'] : $t['tid']),
										'fid'=>$key
									);
								}
								break;
							default:
								$custom[$key] = $v;
								break;
						}
						break;
					default:
						break;
				} /* end of switch */
			}
		}
		
		$que .= ", `custom`, `created`, `current`, `active`";
		$que2 .= ", ?, ?, ?, ?)";
		$que = $que.$que2;

		$array1 .= 'sddd",';
		$array2 .= ", serialize(".'$'."custom), time(), 1, 1)";
		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_oid = $dbm->getLastInsertId();

		if($revision != true) {
			$que = "UPDATE {organize} SET vid = ? WHERE oid = ?";
			if( $dbm->execute($que,array("dd",$insert_oid,$insert_oid)) < 1) {
				self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
				return -1;
			}
			switch($depth) {
				case 1:
					$p_que = "UPDATE {organize} SET p1 = ? WHERE oid = ?";
					break;
				case 2:
					$p_que = "UPDATE {organize} SET p2 = ? WHERE oid = ?";
					break;
				case 3:
					$p_que = "UPDATE {organize} SET p3 = ? WHERE oid = ?";
						break;
				case 4:
					$p_que = "UPDATE {organize} SET p4 = ? WHERE oid = ?";
					break;
			}
			if($p_que) {
				$dbm->execute($p_que,array("dd",$insert_oid,$insert_oid));
			}
		}

		if( self::reBuildTaxonomy($insert_oid, ( $revision ? $args['vid'] : $insert_oid ), $taxonomy_map) < 0 ) {
			return -1;
		}

		if($revision) {
			$que = "UPDATE {organize} SET `current` = ? WHERE vid = ? AND oid != ?";
			if( $dbm->execute( $que, array("ddd",0,$args['vid'],$insert_oid) ) < 1) {
				self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
				return -1;
			}
		}

		return $insert_oid;
	}

	public static function modify($fields,$organize,$args) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		if($args['nojo']) {
			$fullname = $args['nojo'];
			$depth = 1;
		}
		if(empty($args['p1'])) $args['p1'] = 0;
		if($args['sub1']) {
			$fullname .= ($fullname ? " " : "").$args['sub1'];
			$depth = 2;
		}
		if(empty($args['p2'])) $args['p2'] = 0;
		if($args['sub2']) {
			$fullname .= ($fullname ? " " : "").$args['sub2'];
			$depth = 3;
		}
		if(empty($args['p3'])) $args['p3'] = 0;
		if($args['sub3']) {
			$fullname .= ($fullname ? " " : "").$args['sub3'];
			$depth = 4;
		}
		if(empty($args['p4'])) $args['p4'] = 0;
		if($args['sub4']) {
			$fullname .= ($fullname ? " " : "").$args['sub4'];
			$depth = 5;
		}

		if(self::checkParent($depth,$args) < 0) {
			return -1;
		}

		$que = "UPDATE {organize} SET `p1` = ?, `p2` = ?, `p3` = ?, `p4` = ?, `depth` = ?, `nojo` = ?, `sub1` = ?, `sub2` = ?, `sub3` = ?, `sub4` = ?, `fullname` = ?";

		$array1 = 'array("dddddssssss';
		$array2 = '$'."args['p1'], ".'$'."args['p2'], ".'$'."args['p3'], ".'$'."args['p4'], ".'$'."depth, ".'$'."args['nojo'], ".'$'."args['sub1'], ".'$'."args['sub2'], ".'$'."args['sub3'], ".'$'."args['sub4'], ".'$'."fullname";
		foreach($args as $k => $v) {
			if(substr($k,0,1) == 'f') {
				$key = (int)substr($k,1);
				switch(self::$fields[$key]['iscolumn']) {
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
					case 0:
						switch(self::$fields[$key]['type']) {
							case 'taxonomy':
								$cid = self::$fields[$key]['cid'];
								if( is_array($organize['f'.$key]) && count($organize['f'.$key]) ) {
									foreach( $organize['f'.$key] as $terms ) {
										$terms['fid'] = $key;
										$old_terms[$cid][$terms['tid']] = $terms;
									}
								}
								if( is_array($v) ) {
									foreach($v as $t) {
										$cid = $t['cid'];
										$custom[$key][$t['tid']] = array(
											'cid' => $t['cid'],
											'vid' => $t['vid'],
											'name' => $t['name']
										);
										$new_terms[$cid][$t['tid']] = $t;
										if( !$old_terms[$cid][$t['tid']] ) {
											$taxonomy_map[$cid]['add'][$t['tid']] = array(
												'oid'=>$args['oid'],
												'ovid'=>$args['vid'],
												'vid'=>$t['vid'],
												'fid'=>$key
											);
										}
									}
								}
								if(count($old_terms[$cid]) > 0) {
									foreach($old_terms[$cid] as $ot => $ov) {
										if(!$new_terms[$cid][$ot]) {
											$taxonomy_map[$cid]['delete'][$ot] = $ov;
										}
									}
								}
								break;
							default:
								$custom[$key] = $v;
								break;
						}
						break;
					default:
						break;
				} /* end of switch */
			}
		}
		$que .= ", `custom` = ? WHERE `oid` = ? AND `vid` = ?";
		$array1 .= 'sdd",';
		$array2 .= ", serialize(".'$'."custom), ".'$'."args['oid'], ".'$'."args['vid'])";

		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		$dbm->execute($que,$q_args);

		if($depth != $organize['organize']) {
			switch($depth) {
				case 1:
					$p_que = "UPDATE {organize} SET p1 = ? WHERE oid = ?";
					break;
				case 2:
					$p_que = "UPDATE {organize} SET p2 = ? WHERE oid = ?";
					break;
				case 3:
					$p_que = "UPDATE {organize} SET p3 = ? WHERE oid = ?";
					break;
				case 4:
					$p_que = "UPDATE {organize} SET p4 = ? WHERE oid = ?";
					break;
			}
			if($p_que) {
				if( $dbm->execute($que,array("dd",$insert_oid,$insert_oid)) < 1) {
					self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
					return -1;
				}
			}
		}

		self::reBuildTaxonomy($organize['oid'], $organize['vid'], $taxonomy_map);

		return $args['oid'];
	}

	public static function delete($fields,$oid) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		$que = "DELETE FROM {organize} WHERE oid = ?";
		$dbm->execute($que,array("d",$oid));

		$que = "DELETE FROM {agreement_organize} WHERE oid = ?";
		$dbm->execute($que,array("d",$oid));

		$que = "DELETE FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ?";
		$dbm->execute($que,array("sd",'organize',$oid));

		return 0;
	}

	public static function checkParent($depth,$args) {
		$dbm = \CADB\DBM::instance();

		for($d = 1; $d < min($depth,5); $d++) {
			$parent = $args['p'.$d];
			if($d == 1) {
				$name = $args['nojo'];
			} else {
				$name = $args['sub'.($d-1)];
			}
			if($name && !$parent) {
				self::setErrorMsg( "상급단체 ".$name."는 DB에 없는 단위이거나, 선택하기에서 선택하지 않으셨습니다." );
				return -1;
			}
			if($name && $parent) {
				$org = \CADB\Organize::getOrganizeByOid($parent);
				if(!$org) {
					self::setErrorMsg( "상급단체 ".$name."는 DB에 없는 단위입니다." );
					return -1;
				}
				if($d == 1) {
					$o_name = $org['nojo'];
				} else {
					$o_name = $org['sub'.($d-1)];
				}
				if($o_name != $name) {
					self::setErrorMsg( "상급단체 ".$name."이 DB에 있는 상급 단위 이름과 다릅니다." );
					return -1;
				}
			}
		}
		return 0;
	}

	public static function reBuildTaxonomy($oid, $vid, $taxonomy_map) {
		$dbm = \CADB\DBM::instance();

		if( is_array($taxonomy_map) ) {
			foreach($taxonomy_map as $cid => $option_taxonomies) {
				if( is_array($option_taxonomies) ) {
					foreach($option_taxonomies as $option => $taxonomies) {
						switch($option) {
							case "add":
								if( is_array($taxonomies) ) {
									foreach( $taxonomies as $tid => $term ) {
										$que = "INSERT INTO {taxonomy_term_relative} (`tid`, `table`, `rid`, `fid`) VALUES (?,?,?,?)";
										if( $dbm->execute( $que, array("dsdd",$tid,'organize',$oid,$term['fid']) ) < 1 ) {
											self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
											return -1;
										}
									}
								}
								break;
							case "delete":
								if( is_array($taxonomies) ) {
									foreach( $taxonomies as $tid => $term ) {
										$que = "DELETE FROM {taxonomy_term_relative} WHERE `tid` = ? AND `table` = ? AND `rid` = ?";
										if( $dbm->execute( $que, array("dsd",$tid,'organize',$oid) ) < 1 ) {
											self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
											return -1;
										}
									}
								}
								break;
							default:
								break;
						}       
					}
				}
			}
		}

		return 0;
	}

	public static function setErrorMsg($errmsg) {
		self::$errmsg = $errmsg;
	}

	public static function errorMsg() {
		return self::$errmsg;
	}
}
?>
