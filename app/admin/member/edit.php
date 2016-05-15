<?php
namespace CADB\App\admin\member;

$Acl = 'administrator';

class edit extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
//		$this->css[] = 'app-admin-member.css';

		$context = \CADB\Model\Context::instance();

		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				$this->member = \CADB\Member\Gnu5\User::getMember($this->params['mb_no']);
				if(!$this->member) {
					\CADB\Lib\Error('존재하지 않는 회원입니다.');
				}
				$this->edit_component = 'member.gnu5.edit';
				break;
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
