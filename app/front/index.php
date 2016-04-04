<?php
namespace CADB\App\front;

$Acl = 'authenticated';

class index extends \CADB\Controller {
	public function process() {
		\CADB\Lib\importResource('es6-promise');
		$this->react[] = 'public/js/bundle.js';

		$context = \CADB\Model\Context::instance();
	}
}
?>
