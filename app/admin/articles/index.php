<?php
namespace CADB\App\admin\articles;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
		$this->css[] = 'app-admin-article.css';

		$context = \CADB\Model\Context::instance();

		\CADB\Agreement::setMode('admin');
		$fields = \CADB\Agreement::getFieldInfo(1);
		foreach($fields['field'] as $f => $v) {
			if($v['table'] == 'agreement') {
				$this->fields['article'][] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'], 'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);     
			}
		}

		foreach($this->params as $k => $v) {
			if(preg_match("/^[ao]{1}[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}

		if(!$this->params['page']) $this->params['page'] = 1;

		$this->total_cnt = \CADB\Agreement\DBM::totalCnt($this->params['q'],$args);
		$this->page = $this->params['page'];
		$this->limit = ( $this->params['limit'] ? $this->params['limit'] : 15 );
		$this->total_page = (int)(($this->total_cnt-1) / $this->limit)+1;
		if($this->total_cnt && $this->params['page'] <= $this->total_page) {
			$this->articles = \CADB\Agreement\DBM::getList($this->params['q'],$this->params['page'],$this->limit,$args);
		}
		$this->queryString = "?".$this->makeQuery($this->params['q'],$args);
		$this->pagelink = \CADB\Lib\url("admin/articles")."?".$this->makeQuery($this->params['q'],$args);
	}

	public function makeQuery($q,$args) {
		$arg = '';
		$c = 0;
		if($q) {
			$arg = ($c++ ? "&" : "")."q=".$q;
		}
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
