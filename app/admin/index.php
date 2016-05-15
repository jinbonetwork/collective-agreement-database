<?php
namespace CADB\App\admin;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';

		$context = \CADB\Model\Context::instance();
		\CADB\Lib\RedirectURL('/admin/orgs');
	}
}
?>
