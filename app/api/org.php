<?php
namespace CADB\App\api;

class org extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Organize::getFieldInfo(1);
		$this->fields = array();
		foreach($this->params as $k => $v) {
			if(preg_match("/^o[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}
		foreach($fields as $f => $v) {
			$this->fields["f".$f] = array('subject' => $v['subject'],'type'=>$v['type'],'mutiple'=>( 1 ?true : false ),'cid'=>$v['cid']);
		}

		if(!$this->params['page']) $this->params['page'] = 1;
		$this->total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
		$this->total_page = ( ( $this->total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 20) ) + 1;
		if($this->total_cnt && $this->params['page'] <= $this->total_page) {
			$this->organize = \CADB\Organize::getList($this->params['q'],$this->params['page'],($this->params['limit'] ? $this->params['limit'] : 20),$args);
		}
	}
}
?>
