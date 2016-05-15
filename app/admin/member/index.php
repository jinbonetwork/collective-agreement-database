<?php
namespace CADB\App\admin\member;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
		$this->css[] = 'app-admin-member.css';

		$context = \CADB\Model\Context::instance();

		switch($context->getProperty('session.type')) {
			case 'gnu5':
			default:
				$this->total_cnt = \CADB\Member\Gnu5\User::totalCnt($this->params['s_mode'],$this->params['s_arg']);
				$this->page = ($this->params['page'] ? $this->params['page'] : 1);
				$this->limit = ( $this->params['limit'] ? $this->params['limit'] : 15 );
				$this->total_page = (int)(($this->total_cnt-1) / $this->limit)+1;
				if($this->total_cnt && $this->params['page'] <= $this->total_page) {
					$this->members = \CADB\Member\Gnu5\User::getList($this->params['s_mode'],$this->params['s_arg'],$this->params['page'],$this->limit);
				}
				break;
		}

		$this->queryString = "?".$this->makeQuery($this->params['q'],$args);
		$this->pagelink = \CADB\Lib\url("admin/member")."?".$this->makeQuery($this->params['s_mode'],$this->params['s_arg']);
	}

	public function makeQuery($s_mode,$s_arg) {
		$arg = '';
		$c = 0;
		if($s_mode && $s_arg) {
			$arg = "s_mode=".$s_mode."&s_arg=".$s_arg;
		}
		return $arg;
	}
}
?>
