<?php
namespace CADB\App\front;

class index extends \CADB\Controller {
	public function process() {
		importResource('es6-promise');
		$this->react[] = 'public/js/bundle.js';
		$this->css[] = 'app-front.css';

		$context = \CADB\Model\Context::instance();
	}
}
?>
