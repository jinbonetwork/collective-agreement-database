<?php
namespace CADB\Agreement;

class DBM extends \CADB\Objects  {
	private static $fields;
	public static $errmsg;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function insert($fields,$args,$revision = false) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		$que = "INSERT INTO {agreement} (".($revision == true ? "`did`, " : "")."`subject`, `content`";
		$que2 = ") VALUES (".($revision == true ? "?, " : "")."?, ?";
		$array1 = 'array("'.($revision ? 'd' : '').'ss';
		$array2 = ($revision ? '$'.$args['did'].', ' : '').'$'."args['subject'], ".'$'."args['content']";
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
					case 0;
						switch(self::$fields[$key]['type']) {
							case 'taxonomy':
								$cid = self::$fields[$key]['cid'];
								foreach($v as $t) {
									$custom[$key][$t['tid']] = array(
										'cid' => $t['cid'],
										'vid' => $t['vid'],
										'name' => $t['name']
									);
									$taxonomy_map[$cid]['add'][$t['tid']] = array(
										'nid'=>0,
										'did'=>($revision ? $args['did'] : 0),
										'vid'=>$t['vid'],
										'fid'=>$key
									);
								}
								break;
							case 'organize':
								$custom[$key] = array();
								foreach($v as $t) {
									$custom[$key][] = array(
										'oid' => $t['oid'],
										'vid' => $t['vid'],
										'owner' => $t['owner'],
										'name' => $t['name']
									);
									if($t['oid']) {
										$organize_map[$t['oid']] = array(
											'oid'=> $t['oid'],
											'vid'=>( $t['vid'] ? $t['vid'] : $t['oid'] ),
											'owner'=> ( $t['owner'] ? 1 : 0)
										);
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

		$que .= ", `custom`, `created`, `current`";
		$que2 .= ", ?, ?, ?)";
		$que = $que.$que2;

		$array1 .= 'sdd",';
		$array2 .= ", serialize(".'$'."custom), time(), 1)";
		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		if( $dbm->execute($que,$q_args) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_nid = $dbm->getLastInsertId();

		if($revision != true) {
			$que = "UPDATE {agreement} SET did = ? WHERE nid = ?";
			if( $dbm->execute($que,array("dd",$insert_nid,$insert_nid)) < 1) {
				self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
				return -1;
			}
		}

		if( self::reBuildTaxonomy($insert_nid, ( $revision ? $args['did'] : $insert_nid ), $taxonomy_map) < 0 ) {
			return -1;
		}
		if( self::reBuildOrganize($insert_nid, ( $revision ? $args['did'] : $insert_nid ), $organize_map,null) < 0 ) {
			return -1;
		}

		if($revision) {
			$que = "UPDATE {agreement} SET `current` = ? WHERE did = ? AND nid != ?";
			if( $dbm->execute( $que, array("ddd",0,$args['did'],$insert_nid) ) < 1) {
				self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
				return -1;
			}
		}

		$guide_cids = \CADB\Guide::getTaxonomy();
		foreach($guide_cids as $cid) {
			if( self::reBuildGuideTaxonomy($cid, $insert_nid, $args['guide']) < 0 ) {
				return -1;
			}
		}

		return $insert_nid;
	}

	public static function modify($fields,$articles,$args) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		$que = "SELECT * FROM {agreement_organize} WHERE nid = ".$articles['nid']." AND did = ".$articles['did'];
		$old_orgs = array();
		while( $row = $dbm->getFetchArray($que) ) {
			$old_orgs[$row['oid']] = $row;
		}

		$que = "UPDATE {agreement} SET `subject` = ?, `content` = ?";
		$array1 = 'array("ss';
		$array2 = '$'."args['subject'], ".'$'."args['content']";
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
					case 0;
						switch(self::$fields[$key]['type']) {
							case 'taxonomy':
								$cid = self::$fields[$key]['cid'];
								if( is_array($articles['f'.$key]) && count($articles['f'.$key]) ) {
									foreach( $articles['f'.$key] as $terms ) {
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
												'nid'=>$args['nid'],
												'did'=>$args['did'],
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
							case 'organize':
								if( is_array($v) ) {
									$custom[$key] = array();
									foreach($v as $t) {
										$custom[$key][] = array(
											'oid' => $t['oid'],
											'vid' => ( $t['vid'] ? $t['vid'] : $t['oid'] ),
											'owner' => ( $t['owner'] ? 1 : 0 ),
											'name' => $t['name']
										);
										if($t['oid']) {
											if( !$old_orgs[$t['oid']] ) {
												$organize_map[$t['oid']] = array(
													'oid'=> $t['oid'],
													'vid'=>( $t['vid'] ? $t['vid'] : $t['oid'] ),
													'owner'=> ( $t['owner'] ? 1 : 0)
												);
											} else {
												$old_orgs[$t['oid']]['matched'] = 1;
												if($old_orgs[$t['oid']]['owner'] != $t['owner']) {
													$old_orgs[$t['oid']]['change_owner'] = 1;
													$old_orgs[$t['oid']]['new_owner'] = $t['owner'];
												}
												if($old_orgs[$t['oid']]['vid'] != $t['vid']) {
													$old_orgs[$t['oid']]['change_vid'] = 1;
													$old_orgs[$t['oid']]['new_vid'] = $t['vid'];
												}
											}
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
		$que .= ", `custom` = ? WHERE `nid` = ? AND `did` = ?";
		$array1 .= 'sdd",';
		$array2 .= ", serialize(".'$'."custom), ".'$'."args['nid'], ".'$'."args['did'])";

		$eval_str = '$'."q_args = ".$array1.$array2.";";
		eval($eval_str);

		$dbm->execute($que,$q_args);
		self::reBuildTaxonomy($articles['nid'], $articles['did'], $taxonomy_map);
		self::reBuildOrganize($articles['nid'], $articles['did'], $organize_map, $old_orgs);

		$guide_cids = \CADB\Guide::getTaxonomy();
		foreach($guide_cids as $cid) {
			self::reBuildGuideTaxonomy($cid,$articles['nid'],$args['guide']);
		}

		return $args['nid'];
	}

	public static function delete($fields,$nid) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		$que = "DELETE FROM {agreement} WHERE nid = ?";
		$dbm->execute($que,array("d",$nid));

		$que = "DELETE FROM {agreement_organize} WHERE nid = ?";
		$dbm->execute($que,array("d",$nid));

		$que = "DELETE FROM {taxonomy_term_relative} WHERE `table` = ? AND `rid` = ?";
		$dbm->execute($que,array("sd",'agreement',$nid));

		return 0;
	}

	public static function fork($fields,$nid,$did) {
		$dbm = \CADB\DBM::instance();

		self::$fields = $fields;

		$que = "SELECT * FROM {agreement} WHERE nid = ".$nid." AND did = ".$did;
		$articles = $dbm->getFetchArray($que);

		if(!$articles) return -1;

		$que = "INSERT INTO {agreement} (";
		$que2 = ") VALUES (";
		$array1 = 'array("';
		$array2 = "";

		$c = 0;
		foreach($articles as $k => $v) {
			if($k == 'nid' || $k == 'did' ) continue;
			if($k == 'created') continue;
			$que .= ($c ? ", " : "")."`".$k."`";
			$que2 .= ($c ? ", " : "")."?";
			if(is_numeric($v)) {
				$array1 .= 'd';
			} else {
				$articles[$k] = stripslashes($v);
				$array1 .= 's';
			}
			$array2 .= ($c ? ", " : "").'$'.'articles['.$k.']';
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
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$insert_nid = $dbm->getLastInsertId();

		$que = "UPDATE {agreement} SET did = ? WHERE nid = ?";
		if( $dbm->execute($que,array("dd",$insert_nid,$insert_nid)) < 1) {
			self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
			return -1;
		}

		$que = "UPDATE {agreement} SET `current` = ? WHERE nid = ? AND did = ?";
		$dbm->execute($que,array("ddd",0,$nid,$did));

		$article_orgs = array();
		$que = "SELECT * FROM {agreement_organize} WHERE nid = ".$nid." AND did = ".$did;
		while($row = $dbm->getFetchArray($que)) {
			$article_orgs[] = $row;
		}
		if(is_array($article_orgs)) {
			foreach($article_orgs as $orgs) {
				$que = "INSERT INTO {agreement_organize} (`nid`,`did`,`oid`,`vid`,`owner`) VALUES (?,?,?,?,?)";
				if( $dbm->execute($que,array("ddddd",$insert_nid,$insert_nid,$orgs['oid'],$orgs['vid'],$orgs['owner'])) < 1) {
					self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
					return -1;
				}
			}
		}

		$taxonomy_terms = array();
		$que = "SELECT * FROM {taxonomy_term_relative} WHERE `table` = 'agreement' AND rid = ".$nid;
		while($row = $dbm->getFetchArray($que)) {
			$taxonomy_terms[] = $row;
		}
		if(is_array($taxonomy_terms)) {
			foreach($taxonomy_terms as $terms) {
				$que = "INSERT INTO {taxonomy_term_relative} (`tid`,`table`,`rid`,`fid`) VALUES (?,?,?,?)";
				if( $dbm->execute($que,array("dsdd",$terms['tid'],'agreement',$insert_nid,$terms['fid'])) < 1) {
					self::setErrorMsg($que." 가 DB에 반영되지 않았습니다.");
					return -1;
				}
			}
		}

		return $insert_nid;
	}

	public static function reBuildTaxonomy($nid, $did, $taxonomy_map) {
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
										if( $dbm->execute( $que, array("dsdd",$tid,'agreement',$nid,$term['fid']) ) < 1 ) {
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
										if( $dbm->execute( $que, array("dsd",$tid,'agreement',$nid) ) < 1 ) {
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

	public static function reBuildOrganize($nid, $did, $organize_map, $old_map) {
		$dbm = \CADB\DBM::instance();

		if( is_array($organize_map) ) {
			foreach($organize_map as $oid => $org) {
				$que = "INSERT INTO {agreement_organize} (`nid`, `did`, `oid`, `vid`, `owner`) VALUES (?,?,?,?,?)";
				if( $dbm->execute( $que, array("ddddd",$nid,$did,$oid,($org['vid'] ? $org['vid'] : $oid),$org['owner']) ) < 1) {
					self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
					return -1;
				}
			}
		}
		if( is_array($old_map) ) {
			foreach( $old_map as $oid => $org ) {
				if(!$org['matched']) {
					$que = "DELETE FROM {agreement_organize} WHERE `nid` = ? AND `did` = ? AND `oid` = ? AND `vid` = ?";
					if( $dbm->execute( $que, array("dddd",$nid,$did,$oid,($org['vid'] ? $org['vid'] : $oid) ) ) < 1 ) {
						self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
						return -1;
					}
				} else if($org['change_owner'] || $org['change_vid']) {
					$qc = 0;
					$que = "UPDATE {agreement_organize} SET ";
					$array1 = 'array("';
					$array2 = "";
					if($org['change_owner']) {
						$que .= ($qc++ ? ", " : "")."`owner` = '".$org['new_owner']."'";
						$array1 .= "d";
						$array2 .= ", ".'$'."org['new_owner']";
					}
					if($org['change_vid']) {
						$que .= ($qc++ ? ", " : "")."`vid` = '".$org['new_vid']."'";
						$array1 .= "d";
						$array2 .= ", ".'$'."org['new_vid']";
					}
					$array1 .= 'dddd" ';
					$array2 .= ", ".'$'."nid ,".'$'."did ,".'$'."oid ,(".'$'."org['vid'] ? ".'$'."org['vid'] : ".'$'."oid));";
					$eval_str = '$'."q_args = ".$arra1.$array2;
					eval($eval_str);
					$que .= " WHERE `nid` = ? AND `did` = ? AND `oid` = ? AND `vid` = ?";

					if( $dbm->execute($que,$q_args) < 1 ) {
						self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
						return -1;
					}
				}
			}
		}

		return 0;
	}

	public static function reBuildGuideTaxonomy($cid, $nid, $guides) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT r.* FROM {taxonomy_term_relative} AS r LEFT JOIN {taxonomy_terms} AS t ON r.tid = t.tid WHERE t.cid = ".$cid." AND r.`table` = 'agreement' AND r.`rid` = ".$nid." ORDER BY r.fid";
		while( $row = $dbm->getFetchArray($que) ) {
			$preTaxonomy[$row['fid']][$row['tid']] = $row;
		}

		if(is_array($guides)) {
			foreach($guides as $fid => $guide) {
				if(@count($guide['items']) > 0) {
					foreach($guide['items'] as $item) {
						if($preTaxonomy[$fid][$item['tid']]) {
							$preTaxonomy[$fid][$item['tid']]['matched'] = 1;
						} else {
							$que = "INSERT INTO {taxonomy_term_relative} (`tid`,`table`,`rid`,`fid`) VALUES (?,?,?,?)";
							if( $dbm->execute( $que, array("dsdd",$item['tid'],'agreement',$nid,$fid) ) < 1) {
								self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
								return -1;
							}
						}
					}
				}
			}
		}

		if(is_array($preTaxonomy)) {
			foreach($preTaxonomy as $fid => $data) {
				if(is_array($data)) {
					foreach($data as $tid => $terms) {
						if(!$terms['matched']) {
							$que = "DELETE FROM {taxonomy_term_relative} WHERE `tid` = ? AND `table` = ? AND `rid` = ? AND `fid` = ?";
							if( $dbm->execute( $que, array("dsdd",$tid,'agreement',$nid,$fid) ) < 1 ) {
								self::setErrorMsg( $que." 가 DB에 반영되지 않았습니다." );
								return -1;
							}
						}
					}
				}
			}
		}

		return 0;
	}

	private static function copy($table,$args,$nid=0) {
		$dbm = \CADB\DBM::instance();

		$que = "INSERT INTO {".$table."} (";
		$que2 = ") VALUES (";
		$array1 = 'array("';
		$array2 = "";

		$c = 0;
		foreach($args as $k => $v) {
			if($k == 'nid' || $k == 'rid' || $k == 'did' ) {
				if($nid) {
					$que .= ($c ? ", " : "")."`".$k."`";
					$que2 .= ($c ? ", " : "")."?";
					if(is_numeric($v)) {
						$array1 .= 'd';
					} else {
						$array1 .= 's';
					}
					$array2 .= ($c ? ", " : "").'$'.'nid';
				}
			} else {
				$que .= ($c ? ", " : "")."`".$k."`";
				$que2 .= ($c ? ", " : "")."?";
				if(is_numeric($v)) {
					$array1 .= 'd';
				} else {
					$array1 .= 's';
				}
				$array2 .= ($c ? ", " : "").'$'.'args['.$k.']';
			}
		}
	}

	public static function setErrorMsg($errmsg) {
		self::$errmsg = $errmsg;
	}

	public static function errorMsg() {
		return self::$errmsg;
	}
}
?>
