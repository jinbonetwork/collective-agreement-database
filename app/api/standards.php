<?php
namespace CADB\App\api;

$IV = array(
	'GET' => array(
		'id' => array('number','default'=>null),
		'q' => array('string','default'=>null)
	)
);

class standards extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Guide::getFieldInfo(1);
		$nid = \CADB\Guide::getCurrent(($this->params['nid'] ? $this->params['nid'] : 1));
		if($this->params['id']) {
			$this->fields = array();
			foreach($fields as $f => $v) {
				$this->fields[] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'],'mutiple'=>( 1 ? true : false ),'cid'=>$v['cid']);
			}
			$this->standard = \CADB\Guide::getClause($this->params['id']);
			if($this->standard) {
				$this->result = array(
					'found'=>true
				);
			} else {
				$this->result = array(
					'found'=>false,
					'error'=>'존재하지 모범단협 조항입니다.'
				);
			}
		} else {
			$taxonomys = \CADB\Guide::getTaxonomy($nid);
			foreach($this->params as $k => $v) {
				if( preg_match("/^a[0-9]+$/i",$k) && in_array( (int)substr($k,1), $taxonomys ) ) {
					$args[$k] = $v;
				}
			}
			$this->standard = \CADB\Guide::getList($this->params['q'],$args);
			$this->result = array(
				'q'=> $this->params['q'],
				'taxonomy'=>$args,
				'count' => (@count($this->standard) > 0 ? @count($this->standard) : 0)
			);
		}
	}
}
?>
