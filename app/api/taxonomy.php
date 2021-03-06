<?php
namespace CADB\App\api;

$Acl = "authenticated";

$IV = array(
	'GET' => array(
		'cid' => array('int','default'=>null),
		'q' => array('string','default'=>null)
	)
);

class taxonomy extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		if($this->params['cid'] && $this->params['q']) {
			$this->taxonomy = \CADB\Taxonomy::search($this->params['cid'],$this->params['q']);
			$this->result = array(
				'cid' => $this->params['cid'],
				'q' => $this->params['q'],
				'total_cnt'=>@count($this->taxonomy)
			);
		} else if($this->params['cid']) {
			$this->taxonomy = \CADB\Taxonomy::getTaxonomyTerms($this->params['cid']);
			if($this->params['type'] == 1) {
				$taxonomy = array();
				foreach($this->taxonomy as $c => $taxonomies) {
					foreach($taxonomies as $t => $taxo) {
						$taxonomy[] = $taxo;
					}
				}
				$this->taxonomy = $taxonomy;
			}
			$this->result = array(
				'cid' => $this->params['cid'],
				'total_cnt'=>@count($this->taxonomy)
			);
		} else {
			$this->result = array(
				'error' => '잘못된 검색입니다'
			);
		}
	}
}
?>
