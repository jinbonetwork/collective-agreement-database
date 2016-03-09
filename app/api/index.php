<?php
namespace CADB\App\api;

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

	private function makeOption($option) {
		foreach( $option['items'] as $i => $it ) {
			foreach( $it['items'] as $j => $item ) {
				switch($item['option']) {
					case 'taxonomy':
						$taxonomy = \CADB\Taxonomy::getTaxonomy($item['id']);
						$option['items'][$i]['items'][$j]['name'] = $taxonomy[$item['id']]['subject'];
						$taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($item['id']);
						$option['items'][$i]['items'][$j]['options'] = array();
						$taxonomy_list = \CADB\Taxonomy::makeTree($taxonomy_terms[$item['id']]);
						foreach($taxonomy_list as $term) {
							$option['items'][$i]['items'][$j]['options'][] = array('name'=>$term['name'],'depth'=>$term['depth'],'parent'=>$term['parent'],'value'=>$term['tid']);
						}
						break;
					case 'field':
						if(!$fields) {
							$fields = \CADB\Fields::getFields('all');
						}
						if($fields[$item['id']]) {
							$option['items'][$i]['items'][$j]['name'] = $fields[$item['id']]['subject'];
							switch($fields[$item['id']]['type']) {
								case 'taxonomy':
									$taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($fields[$item['id']]['cid']);
									$option['items'][$i]['items'][$j]['options'] = array();
									$taxonomy_list = \CADB\Taxonomy::makeTree($taxonomy_terms[$fields[$item['id']]['cid']]);
									foreach($taxonomy_list as $term) {
										$option['items'][$i]['items'][$j]['options'][] = array('name'=>$term['name'],'depth'=>$term['depth'],'parent'=>$term['parent'],'value'=>$term['tid']);
									}
									break;
								case 'int':
									if($fields[$item['id']]['table'] == 'organize') {
									}
									break;
								default:
									break;
							}
							$option['items'][$i]['items'][$j]['multiple'] = $fields[$item['id']]['multiple'];
						}
						break;
				}
			}
		}

		return $option;
	}
}
?>
