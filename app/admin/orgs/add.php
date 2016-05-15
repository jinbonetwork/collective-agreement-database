<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class add extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Organize::getFieldInfo(1);
		foreach($this->fields as $fid => $f) {
			if($f['type'] == 'taxonomy') {
				$taxonomy_cids[] = $f['cid'];
			}
		}
		if(count($taxonomy_cids)) {
			$this->taxonomy = \CADB\Taxonomy::getTaxonomy($taxonomy_cids);
		}
		$cids = array_keys($this->taxonomy);
		if($cids) {
			$taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($cids);
			foreach($taxonomy_terms as $cid => $terms) {
				$this->taxonomy_terms[$cid] = \CADB\Taxonomy::makeTree($terms);
			}
		}
	}
}
?>
