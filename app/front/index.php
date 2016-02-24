<?php
namespace CADB\App\front;

class index extends \CADB\Controller {
	public function process() {
		$this->react[] = 'bundle.js';
		$this->css[] = 'app-front.css';

		$context = \CADB\Model\Context::instance();
	}
}
?>
