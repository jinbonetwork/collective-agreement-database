<?php
namespace CADB\App\admin\search;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		$this->search_option = \CADB\Options::getOption('search_option');
		$this->column_cnt = @count($this->search_option);
		$this->taxonomies = \CADB\Taxonomy\DBM::getTaxonomyList();
	}
}
