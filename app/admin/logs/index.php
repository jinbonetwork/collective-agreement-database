<?php
namespace CADB\App\admin\logs;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
		$this->fullscreen = true;

		foreach($this->params as $k => $v) {
			if($k == 'action' || $k == 'name' || $k == 'start' || $k == 'end')
				$args[$k]=$v;
		}
		\CADB\Log\DBM::initQuery($args);

		$this->total_cnt = \CADB\Log\DBM::totalCnt();
		$this->page = ($this->params['page'] ? $this->params['page'] : 1);
		$this->limit = ( $this->params['limit'] ? $this->params['limit'] : 50 );
		$this->total_page = (int)(($this->total_cnt-1) / $this->limit)+1;
		if($this->total_cnt && $this->page <= $this->total_page) {
			$this->logs = \CADB\Log\DBM::getList($this->page,$this->limit);
		}
		$this->queryString = "?".$this->makeQuery($args);
		$this->pagelink = \CADB\Lib\url("admin/logs")."?".$this->makeQuery($args);
	}

	public function makeQuery($args) {
		$c = 0;
		if(is_array($args)) {
			foreach($args as $k => $v) {
				$arg .= ($c++ ? "&" : "").$k."=";
				if(is_array($v)) {
					$arg .= "[".implode(",",$v)."]";
				} else {
					$arg .= $v;
				}
			}
		}
		if($c) $arg .= "&";
		return $arg;
	}
}
?>
