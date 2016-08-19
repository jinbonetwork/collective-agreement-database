<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class fields extends \CADB\Controller {
	public function process() {
		\CADB\Lib\importResource('app-field-edit');

		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		$this->taxonomy = \CADB\Taxonomy\DBM::getTaxonomyList();
		$this->fields = \CADB\Organize::getFieldInfo(0);
	}
}
