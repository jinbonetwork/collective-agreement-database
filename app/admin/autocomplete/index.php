<?php
namespace CADB\App\admin\autocomplete;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';

		$context = \CADB\Model\Context::instance();

		if(!($rdb = $context->getProperty('service.redis'))) {
			\CADB\Lib\Error("자동완성 기능이 활성화 되어 있지 않습니다.");
		}
		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$nid = \CADB\Guide::getCurrent();
		$cids = \CADB\Guide::getTaxonomy();
		$guide_taxonomy = \CADB\Taxonomy::getTaxonomy($cids);

		$fields = \CADB\Fields\DBM::searchField('autocomplete',1);
		$field_cids = array();
		$this->fields = array();
		if(is_array($fields)) {
			foreach($fields as $f) {
				switch($f['type']) {
					case 'taxonomy':
						$field_cids[] = $f['cid'];
						break;
					case 'char':
					case 'text':
						$this->fields[] = $f;
						break;
				}
			}
		}
		if(count($field_cids)) {
			$field_taxonomy = \CADB\Taxonomy::getTaxonomy($field_cids);
			$this->taxonomy = array_merge($guide_taxonomy,$field_taxonomy);
		} else {
			$this->taxonomy = $guide_taxonomy;
		}
	}
}
?>
