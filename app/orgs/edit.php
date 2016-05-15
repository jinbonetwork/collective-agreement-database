<?php
namespace CADB\App\orgs;

$Acl = 'owner';

class edit extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		if(!$this->params['oid']) {
			Error("조직 고유아이디를 입력하세요.");
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Organize::getFieldInfo(1);
		$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
		if(!$this->organize) {
			Error("조직정보를 검색할 수 없습니다.");
		}
		$agreement = \CADB\Agreement::getAgreementsByOid($this->params['oid']);
		if($agreement && is_array($agreement)) {
			foreach($agreement as $ag) {
				$this->organize['nid'][] =  array(
					'nid'=>$ag['nid'],
					'did'=>$ag['did'],
					'subject'=>$ag['subject']
				);
			}
		}
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
