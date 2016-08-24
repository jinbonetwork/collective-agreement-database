<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class delete extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		$this->params['table'] = 'guide';
		$this->params['mode'] = 'delete';
		if(!$this->params['nid']) {
			\CADB\Lib\Error('모범단협 번호를 지정하세요');
		}
		$this->guide = \CADB\Guide\DBM::getGuide($this->params['nid']);
		if(!$this->guide) {
			\CADB\Lib\Error('존재하지 않는 모범단협번호입니다');
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		\CADB\Guide\DBM::deleteGuide($this->guide);
		$this->nid = $this->params['nid'];
		\CADB\Lib\RedirectURL( '/admin/standards' );
	}
}
