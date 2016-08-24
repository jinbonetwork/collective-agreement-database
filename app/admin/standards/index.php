<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
//		$this->css[] = 'app-admin-standard.css';

		$context = \CADB\Model\Context::instance();

		if(!$this->params['page']) $this->params['page'] = 1;

		$this->total_cnt = \CADB\Guide\DBM::totalCnt($this->params['q']);
		$this->page = $this->params['page'];
		$this->limit = ( $this->params['limit'] ? $this->params['limit'] : 15 );
		$this->total_page = (int)(($this->total_cnt-1) / $this->limit)+1;
		if($this->total_cnt && $this->params['page'] <= $this->total_page) {
			$this->standards = \CADB\Guide\DBM::getList($this->params['q'],$this->params['page'],$this->limit);
		}
		$this->queryString = "?".$this->makeQuery($this->params['q']);
		$this->pagelink = \CADB\Lib\url("admin/standards")."?".$this->makeQuery($this->params['q']);
	}

	public function makeQuery($q) {
		$arg = '';
		$c = 0;
		if($q) {
			$arg = ($c++ ? "&" : "")."q=".$q;
		}
		if($c) $arg .= "&";
		return $arg;
	}
}
?>
