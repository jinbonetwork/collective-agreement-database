<?php
namespace CADB\App\api;

class articles extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Agreement::getFieldInfo(1);
		$this->fields = array();
		foreach($fields['field'] as $f => $v) {
			if($v['table'] == 'agreement') {
				$this->fields['f'.$f] = array('subject' => $v['subject'],'type'=>$v['type'], 'mutiple'=>( 1 ?true : false ),'cid'=>$v['cid']);
			}
		}

		if($this->params['nid']) {
			$this->articles = \CADB\Agreement::getAgreement($this->params['nid'],($this->params['did'] ? $this->params['did'] : 0));
			if($this->articles) {
				$this->result = array(
					'found'=>true
				);
			} else {
				$this->result = array(
					'found'=>false,
					'error'=>'존재하지 않는 단협입니다.'
				);
			}
		} else {
			foreach($this->params as $k => $v) {
				if(preg_match("/^[ao]{1}[0-9]+$/i",$k)) {
					$args[$k] = $v;
				}
			}

			if(!$this->params['page']) $this->params['page'] = 1;
			$total_cnt = \CADB\Agreement::totalCnt($this->params['q'],$args);
			$total_page = (int)( ( $total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 20) ) + 1;
			if($total_cnt && $this->params['page'] <= $total_page) {
				$this->articles = \CADB\Agreement::getList($this->params['q'],$this->params['page'],($this->params['limit'] ? $this->params['limit'] : 20),$args);
				$this->result = array(
					'total_cnt'=>$total_cnt,
					'total_page'=>$total_page,
					'page'=>$this->params['page'],
					'count'=>@count($this->articles)
				);
			} else {
				$this->result = array(
					'total_cnt'=>$total_cnt,
					'total_page'=>$total_page,
					'page'=>$this->params['page'],
					'count'=>0,
					'error'=>'검색결과가 없습니다.'
				);
			}
		}
	}
}
?>