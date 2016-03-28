<?php
namespace CADB\App\api;

$Acl = "authenticated";

class index extends \CADB\Controller {
    public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$search_option = \CADB\Options::getOption('search_option');
		if($search_option) {
			if(isset($this->params['option'])) {
				$this->search_option = $this->makeOption($search_option[$this->params['option']]);
			} else {
				foreach($search_option as $i => $option) {
					$this->search_option[$i] = $this->makeOption($option);
				}
			}
		}
	}

	private function makeOption($options) {
		if($options['type'] == 'block' || $options['type'] == 'masonry') {
			foreach( $options['items'] as $i => $it ) {
				$options['items'][$i] = self::makeOption($it);
			}
		} else {
			$options = self::makeItem($options);
		}
		return $options;
	}

	private function makeItem($item) {
		switch($item['option']) {
			case 'taxonomy':
				$cids = \CADB\Guide::getTaxonomy();
				$taxonomy = \CADB\Taxonomy::getTaxonomy($item['id']);
				$item['name'] = $taxonomy[$item['id']]['subject'];
				if(in_array($item['id'],$cids)) {
					$taxonomy_terms = \CADB\Guide::getRelativeGuideTerm($item['id']);
				} else {
					$taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($item['id']);
				}
				$item['options'] = array();
				$taxonomy_list = \CADB\Taxonomy::makeTree($taxonomy_terms[$item['id']]);
				foreach($taxonomy_list as $term) {
					$item['options'][] = array('name'=>$term['name'],'depth'=>$term['depth'],'parent'=>$term['parent'],'nsubs'=>$term['nsubs'],'value'=>$term['tid']);
				}
				break;
			case 'field':
				if(!$fields) {
					$fields = \CADB\Fields::getFields('all');
				}
				if($fields[$item['id']]) {
					$item['name'] = $fields[$item['id']]['subject'];
					switch($fields[$item['id']]['type']) {
						case 'taxonomy':
							$taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($fields[$item['id']]['cid']);
							$item['options'] = array();
							$taxonomy_list = \CADB\Taxonomy::makeTree($taxonomy_terms[$fields[$item['id']]['cid']]);
							foreach($taxonomy_list as $term) {
								$item['options'][] = array('name'=>$term['name'],'depth'=>$term['depth'],'parent'=>$term['parent'],'nsubs'=>$term['nsubs'],'value'=>$term['tid']);
							}
							break;
						case 'int':
							if($fields[$item['id']]['table'] == 'organize') {
							}
							break;
						default:
							break;
					}
					$item['multiple'] = $fields[$item['id']]['multiple'];
				}
				break;
		}

		return $item;
	}
}
?>
