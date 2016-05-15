<?php
namespace CADB\App\admin\articles;

$Acl = 'administrator';

class add extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Agreement::getFieldInfo(1);

		$g_cids = \CADB\Guide::getTaxonomy();
		foreach($g_cids as $id) {
			$this->guide_taxonomy_terms[$id] = \CADB\Guide::getRelativeGuideTerm($id);
		}

		$this->taxonomy = $this->fields['taxonomy'];
		$taxonomy_cids = array();
		foreach($this->fields['field'] as $fid => $f) {
			if($f['table'] == 'agreement') {
				if($f['type'] == 'taxonomy') {
					$taxonomy_cids[] = $f['cid'];
				}
			}
		}
		if(count($taxonomy_cids)) {
			$this->taxonomy += \CADB\Taxonomy::getTaxonomy($taxonomy_cids);
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
