<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class clause extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Guide::getFieldInfo(1);

		if(!$this->params['id']) {
			$this->result = array(
				'found' => false,
				'error' => '모범단협 조항번호를 입력하세요'
			);
		} else {
			$this->fields = array();
			foreach($fields as $f => $v) {
				$this->fields[] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'],'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);
			}
			$this->standard = \CADB\Guide::getClause($this->params['id']);
			if(!$this->standard) {
				$this->result = array(
					'found' => false,
					'error' => '존재하지 않는 모범단협 조항입니다'
				);
			} else {
				$this->result = array(
					'found'=>true
				);
				$terms = \CADB\Guide\DBM::getClauseTaxonomyTerms($this->standard['nid'],$this->standard['id']);
				$this->standard['taxonomy'] = [];
				if(is_array($terms)) {
					$this->standard['taxonomy'] = [];
					foreach( $terms as $t => $taxo ) {
						$this->standard['taxonomy'][] = array('tid'=>$t, 'cid'=>$taxo['cid'], 'name'=>$taxo['name']);
					}
				}
			}
		}
	}
}
?>
