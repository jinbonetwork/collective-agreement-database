<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class edit extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		if(!$this->params['oid']) {
			\CADB\Lib\Error("조직 고유아이디를 입력하세요.");
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Organize::getFieldInfo(1);
		$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
		if(!$this->organize) {
			\CADB\Lib\Error("조직정보를 검색할 수 없습니다.");
		}
		$agreement = \CADB\Agreement::getAgreementsByOid($this->params['oid']);
//		$this->fields['nid'] = array('subject' => '단체협약','type'=>'int','multiple'=>true);
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
