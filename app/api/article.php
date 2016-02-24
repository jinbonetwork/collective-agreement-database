<?php
namespace CADB\App\api;

class article extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		if(!$this->params['page']) $this->params['page'] = 1;
	}
}
?>
