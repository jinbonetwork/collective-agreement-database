<?php
namespace CADB\App\api;

class orgs extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Organize::getFieldInfo(1);
		$this->fields = array();
		foreach($fields as $f => $v) {
			$this->fields["f".$f] = array('subject' => $v['subject'],'type'=>$v['type'],'mutiple'=>( 1 ?true : false ),'cid'=>$v['cid']);
		}

		if($this->params['oid']) {
			$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
			if($this->organize) {
				$this->result = array(
					'found'=>true
				);
			} else {
				$this->result = array(
					'found'=>false,
					'error'=>'존재하지 않는 노조입니다.'
				);
			}
		} else {
			foreach($this->params as $k => $v) {
				if(preg_match("/^o[0-9]+$/i",$k)) {
					$args[$k] = $v;
				}
			}

			if(!$this->params['page']) $this->params['page'] = 1;
			$total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
			$total_page = (int)( ( $total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 20) ) + 1;
			if($total_cnt && $this->params['page'] <= $total_page) {
				$this->organize = \CADB\Organize::getList($this->params['q'],$this->params['page'],($this->params['limit'] ? $this->params['limit'] : 20),$args);
				$this->result = array(
					'total_cnt'=>$total_cnt,
					'total_page'=>$total_page,
					'page'=>$this->params['page'],
					'count'=>@count($this->organize)
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