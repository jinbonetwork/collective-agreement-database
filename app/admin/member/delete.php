<?php
namespace CADB\App\admin\member;

$Acl = 'administrator';

class delete extends \CADB\Controller {
	public function process() {
		if(!$this->params['output'])
			$this->params['output'] = 'json';

		$context = \CADB\Model\Context::instance();

		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				$this->checkfield_gnu5();
				$ret = \CADB\Member\Gnu5\User::delete($this->params['mb_no'],$this->member['mb_id']);
				if($ret < 0) {
					\CADB\RespondJson::ResultPage( array( -10, \CADB\Member\Gnu5\User::errorMsg() ? \CADB\Member\Gnu5\User::errorMsg() : '데이터베이스에 입력하는 도중 장애가 발생했습니다.' ) );
				}
				$this->mb_no = $this->params['mb_no'];
				break;
		}
		if($this->params['output'] != 'json') {
			$queryString = $this->makeQuery($this->params['s_mode'], $this->params['s_arg']);
			\CADB\Lib\RedirectURL( '/admin/member/'.($queryString ? "?".$queryString : "") );
		}
	}

	public function checkfield_gnu5() {
		if(!$this->params['mb_no']) {
			\CADB\RespondJson::ResultPage( array( -10, '회원 번호를 입력하세요' ) );
		}
		$this->member = \CADB\Member\Gnu5\User::getMember($this->params['mb_no']);
		if(!$this->member) {
			\CADB\RespondJson::ResultPage( array( -10, '존재하지 않는 회원 번호입니다' ) );
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
