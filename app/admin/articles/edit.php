<?php
namespace CADB\App\admin\articles;

$Acl = 'administrator';

class edit extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		$this->layout = 'admin';

		if(!$this->params['nid']) {
			\CADB\Lib\Error('단체협약서 번호를 입력하세요.');
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Agreement::getFieldInfo(1);
		$this->articles = \CADB\Agreement::getAgreement($this->params['nid'],($this->params['did'] ? $this->params['did'] : 0));
		if(!$this->articles) {
			\CADB\Lib\Error('존재하지 않는 단체협약입니다.');
		}
		if(\CADB\Privilege::checkAgreement($this->articles) == false) {
			\CADB\Lib\Error('접근 권한이 없습니다.');
		}

		$current_guide = \CADB\Guide\DBM::getCurrent();
		$guide_subject = preg_split("/ /i",$current_guide['subject']);
		$this->guide_subject = implode(" ",array_slice($guide_subject,1));

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
