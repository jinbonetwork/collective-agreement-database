<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class fork extends \CADB\Controller {
	public function process() {
		if(!$this->params['output'])
			$this->params['output'] = 'json';

		$context = \CADB\Model\Context::instance();

		if(!$this->params['nid']) {
			if($this->params['output'] != 'json')
				\CADB\Lib\Error('복사할 모범단체협약서 번호를 입력하세요.');
			else
				\CADB\RespondJson::ResultPage( array( -1, '복사할 모범단체협약서 번호를 입력하세요.' ) );
		}

		$this->nid = \CADB\Guide\DBM::fork($this->params['nid']);
		if(!$this->nid) {
			if($this->params['output'] != 'json')
				\CADB\Lib\Error(\CADB\Guide\DBM::error());
			else
				\CADB\RespondJson::ResultPage( array( -1, \CADB\Guide\DBM::error() ) );
		} else {
			if($this->params['output'] != 'json')
				\CADB\Lib\RedirectURL(\CADB\Lib\url("admin/standards/edit")."?nid=".$this->nid);
			else
				\CADB\RespondJson::ResultPage( array( $this->nid, \CADB\Lib\url("admin/standards/edit")."?nid=".$this->nid ) );
		}
	}
}
