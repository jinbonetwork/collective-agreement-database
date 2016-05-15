<?php
namespace CADB\Member\Gnu5;
        
class User extends \CADB\Objects  {
	private static $fields;
	public static $errmsg;
				        
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public static function totalCnt($s_mode,$s_arg) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT count(*) AS cnt FROM `g5_member`";
		if($s_mode && $s_arg) {
			$que .= " WHERE `".$s_mode."` LIKE '%".$s_arg."%'";
		}
		$row = $dbm->getFetchArray($que);
		
		return ($row['cnt'] ? $row['cnt'] : 0);
	}

	public static function getList($s_mode,$s_arg,$page=1,$limit=20) {
		if(!$page) $page = 1;
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM `g5_member`";
		if($s_mode && $s_arg) {
			$que .= " WHERE `".$s_mode."` LIKE '%".$s_arg."%'";
		}
		$que .= " ORDER BY mb_no ASC LIMIT ".(($page-1)*$limit).",".$limit;

		$members = array();
		$m_array = array();
		while($row = $dbm->getFetchArray($que)) {
			$members[$row['mb_id']] = self::fetchMember($row);
			$m_array[] = $row['mb_id'];
		}

		if(is_array($m_array) && @count($m_array) > 0) {
			$privileges = \CADB\Member\DBM::getPrivilegeByID($m_array);
			if(is_array($privileges) && count($privileges) > 0) {
				foreach($privileges as $mb_id => $roles) {
					$members[$mb_id]['roles'] = $roles;
					if($roles && @count($roles) > 0) {
						$members[$mb_id]['level_name'] = '담당자';
						$members[$mb_id]['glevel'] = BITWISE_USER;
					}
				}
			}
		}

		return $members;
	}

	public static function getMember($mb_no) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM `g5_member` WHERE mb_no = ".$mb_no;
		$member = self::fetchMember($dbm->getFetchArray($que));
		if(!$member) return null;
		$privileges = \CADB\Member\DBM::getPrivilegeByID(array($member['mb_id']));
		if(is_array($privileges[$member['mb_id']]) && count($privileges[$member['mb_id']]) > 0) {
			$member['roles'] = $privileges[$member['mb_id']];
			if($member['roles'] && @count($member['roles']) > 0) {
				$member['level_name'] = '담당자';
				$member['glevel'] = BITWISE_USER;
			}
		}
		return $member;
	}

	private static function fetchMember($row) {
		if(!$row) return null;
		$member = array();
		foreach($row as $k => $v) {
			if(is_string($v)) {
				$v = stripslashes($v);
			}
			$member[$k] = $v;
			if($k == 'mb_level') {
				$member['glevel'] = (11 - $v);
				switch($member['glevel']) {
					case BITWISE_ADMINISTRATOR:
						$member['level_name'] = '운영자';
						break;
					default:
						$member['level_name'] = '이용자';
						break;
				}
			}
		}
		return $member;
	}

	public static function add($args) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM `g5_member` WHERE mb_id = '".$args['mb_id']."'";
		$row = $dbm->getFetchArray($que);
		if($row['mb_no']) {
			self::setErrorMsg( $args['mb_id']."는 이미 존재하는 아이디입니다." );
			return -1;
		}

		$que = "INSERT INTO `g5_member` (
			mb_id,
			mb_password,
			mb_name,
			mb_nick,
			mb_nick_date,
			mb_email,
			mb_homepage,
			mb_level,
			mb_sex,
			mb_birth,
			mb_tel,
			mb_hp,
			mb_certify,
			mb_adult,
			mb_dupinfo,
			mb_zip1,
			mb_zip2,
			mb_addr1,
			mb_addr2,
			mb_addr3,
			mb_addr_jibeon,
			mb_signature,
			mb_recommend,
			mb_point,
			mb_today_login,
			mb_login_ip,
			mb_datetime,
			mb_ip,
			mb_leave_date,
			mb_intercept_date,
			mb_email_certify,
			mb_memo,
			mb_lost_certify,
			mb_mailling,
			mb_sms,
			mb_open,
			mb_open_date,
			mb_profile,
			mb_memo_call,
			mb_1,
			mb_2,
			mb_3,
			mb_4,
			mb_5,
			mb_6,
			mb_7,
			mb_8,
			mb_9,
			mb_10
		) VALUES (?,password(".$args['mb_password']."),?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

		$fp = fopen("/tmp/cadb.txt","w");
		fputs($fp,$que."\n");
		fclose($fp);

		$dbm->execute($que,array(
			"ssssssdsssssdsssssssssssssssssssdddsssssssssssss",
			$args['mb_id'],
			$args['mb_name'],
			$args['mb_nick'],
			date("Y-m-d"),
			$args['mb_email'],
			'',
			$args['mb_level'],
			'',
			'',
			'',
			'',
			'',
			0,
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			0,
			date("Y-m-d H:i:s"),
			'',
			date("Y-m-d H:i:s"),
			'',
			'',
			'',
			date("Y-m-d H:i:s"),
			'',
			'',
			0,
			0,
			0,
			date("Y-m-d"),
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			''
		));

		$insert_mb_no = $dbm->getLastInsertId();

		if(is_array($args['roles']) && @count($args['roles']) > 0) {
			foreach($args['roles'] as $role) {
				\CADB\Member\DBM::addPrivileges($insert_mb_no,$args['mb_id'],$role['oid'],$row['role']);
			}
		}

		return $insert_mb_no;
	}

	public static function modify($member,$args) {
		$dbm = \CADB\DBM::instance();

		$que = "SELECT * FROM `g5_member` WHERE mb_id = '".$args['mb_id']."' AND mb_no != ".$args['mb_no'];
		$row = $dbm->getFetchArray($que);
		if($row['mb_no']) {
			self::setErrorMsg( $args['mb_id']."는 이미 존재하는 아이디입니다." );
			return -1;
		}

		$c = 0;
		if($args['mb_password']) {
			$que = "UPDATE `g5_member` SET mb_id = ?, mb_password = password('".$args['mb_password']."'), mb_name = ?, mb_nick = ?, mb_email = ?, mb_level = ? WHERE mb_no = ?";
		} else {
			$que = "UPDATE `g5_member` SET mb_id = ?, mb_name = ?, mb_nick = ?, mb_email = ?, mb_level = ? WHERE mb_no = ?";
		}
		$dbm->execute($que,array("ssssdd",$args['mb_id'], $args['mb_name'],$args['mb_nick'],$args['mb_email'],$args['mb_level'],$args['mb_no']));

		$n_role = array();
		if(is_array($args['roles']) && @count($args['roles']) > 0) {
			foreach($args['roles'] as $role) {
				$n_role[$role['oid']] = $role['role'];
				if(!$member['roles'][$role['oid']]) {
					\CADB\Member\DBM::addPrivileges($args['mb_no'],$args['mb_id'],$role['oid'],$role['role']);
				}
			}
		}

		if(is_array($member['roles']) && @count($member['roles']) > 0) {
			foreach($member['roles'] as $oid => $role) {
				if(!$n_role[$oid]) {
					\CADB\Member\DBM::removePrivileges($args['mb_id'],$role['oid']);
				}
			}
		}

		return $args['mb_no'];
	}

	public static function delete($mb_no,$mb_id) {
		$dbm = \CADB\DBM::instance();

		$que = "DELETE FROM `g5_member` WHERE mb_no = ?";
		$dbm->execute($que,array("d",$mb_no));

		\CADB\Member\DBM::deletePrivilegeByID($mb_id);

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
