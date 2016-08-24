<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class edit extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		\CADB\Lib\importResource('app-guide-edit');

		$this->layout = 'admin';

		if(!$this->params['nid']) {
			\CADB\Lib\Error('모범단체협약서 번호를 입력하세요.');
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Guide::getFieldInfo(1);

		$this->guide = \CADB\Guide\DBM::getGuide($this->params['nid']);
		if(!$this->guide) {
			\CADB\Lib\Error('존재하지않는 모범단체협약서입니다.');
		}

		$this->taxonomylist = \CADB\Taxonomy\DBM::getTaxonomyList();
		$taxonomys = \CADB\Guide::getTaxonomy($this->guide['nid']);
		$this->taxonomy = \CADB\Taxonomy::getTaxonomy($taxonomys);
		$this->taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($taxonomys);
		$this->current_taxonomys = $taxonomys;

		$clauses = \CADB\Guide\DBM::getClauses($this->params['nid']);
		$c=0;
		foreach($clauses as $i => $cl) {
			if(!$c) $this->preamble = \CADB\Guide::getClause($cl['id']);
			if( !$cl['parent'] ) {
				$cl['nsubs'] = 0;
				$cl['articles'] = array();
				$this->indexes[$i] = $cl;
				$index_map[$cl['id']] = $i;
			} else {
				$idx = $index_map[$cl['parent']];
				$this->indexes[$idx]['articles'][] = $cl;
				$this->indexes[$idx]['nsubs']++;
			}
			$c++;
		}
		if($this->preamble) {
			$this->preamble['terms'] = \CADB\Guide\DBM::getClauseTerms($this->guide['vid'],$this->preamble['id']);
		}
	}
}
?>
