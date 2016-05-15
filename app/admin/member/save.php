<?php
namespace CADB\App\admin\member;

$Acl = 'administrator';

class save extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';

		$context = \CADB\Model\Context::instance();

		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				$this->checkfield_gnu5();
				if($this->params['mode'] == 'modify') {
					$ret = \CADB\Member\Gnu5\User::modify($this->member, $this->params);
				} else {
					$ret = \CADB\Member\Gnu5\User::add($this->params);
				}
				if($ret < 0) {
					\CADB\RespondJson::ResultPage( array( -10, \CADB\Member\Gnu5\User::errorMsg() ? \CADB\Member\Gnu5\User::errorMsg() : '데이터베이스에 입력하는 도중 장애가 발생했습니다.' ) );
				}
				$this->mb_no = $ret;
				break;
		}
	}

	public function checkfield_gnu5() {
		if($this->params['mode'] == 'modify') {
			if(!$this->params['mb_no']) {
				\CADB\RespondJson::ResultPage( array( -10, '회원 번호를 입력하세요' ) );
			}
			$this->member = \CADB\Member\Gnu5\User::getMember($this->params['mb_no']);
			if(!$this->member) {
				\CADB\RespondJson::ResultPage( array( -10, '존재하지 않는 회원 번호입니다' ) );
			}
		}
		if(!$this->params['mb_id']) {
			\CADB\RespondJson::ResultPage( array( -1, '회원 아이디를 입력하세요' ) );
		}
		if($this->params['mode'] == 'add') {
			if(!$this->params['mb_password']) {
				\CADB\RespondJson::ResultPage( array( -2, '회원 비밀번호를 입력하세요' ) );
			}
		}
		if($this->params['mb_password']) {
			if(!$this->params['mb_password_confirm']) {
				\CADB\RespondJson::ResultPage( array( -3, '회원 비밀번호 확인을 입력하세요' ) );
			}
			if($this->params['mb_password'] != $this->params['mb_password_confirm']) {
				\CADB\RespondJson::ResultPage( array( -3, '비밀번호가 일치하지 않습니다' ) );
			}
		}
		if(!$this->params['mb_name']) {
			\CADB\RespondJson::ResultPage( array( -4, '회원 이름을 입력하세요' ) );
		}
		if(!$this->params['mb_nick']) {
			\CADB\RespondJson::ResultPage( array( -5, '회원 닉네임을 입력하세요' ) );
		}
		if(!$this->params['mb_email']) {
			\CADB\RespondJson::ResultPage( array( -6, '회원 이메일을 입력하세요' ) );
		}
		if(!$this->params['mb_level']) {
			\CADB\RespondJson::ResultPage( array( -7, '회원 등급을 입력하세요' ) );
		}
	}

	public function makeQuery($s_mode,$s_arg) {
		$arg = '';
		$c = 0;
		if($s_mode && $s_arg) {
			$arg = "s_mode=".$s_mode."&s_arg=".$s_arg;
		}
		return $arg;
	}
}?>
