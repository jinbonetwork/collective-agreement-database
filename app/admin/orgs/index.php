<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
		$this->fullscreen = true;
//		$this->css[] = 'app-admin-article.css';

		$context = \CADB\Model\Context::instance();

		\CADB\Organize::setMode('admin');
		$fields = \CADB\Organize::getFieldInfo(1);
		$cids = array();
		foreach($fields as $f => $v) {
			$this->fields[] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'], 'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);     
			if($v['type'] == 'taxonomy') {
				$cids[] = $v['cid'];
			}
		}
		if(@count($cids)) {
			$this->taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($cids);
		}

		foreach($this->params as $k => $v) {
			if(preg_match("/^o[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}

		if(!$this->params['page']) $this->params['page'] = 1;

		$this->total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
		$this->page = $this->params['page'];
		$this->limit = ( $this->params['limit'] ? $this->params['limit'] : 15 );
		$this->total_page = (int)(($this->total_cnt-1) / $this->limit)+1;
		if($this->total_cnt && $this->params['page'] <= $this->total_page) {
			$this->orgs = \CADB\Organize::getList($this->params['q'],$this->params['page'],$this->limit,$args);
		}
		$this->queryString = "?".$this->makeQuery($this->params['q'],$args);
		$this->pagelink = \CADB\Lib\url("admin/orgs")."?".$this->makeQuery($this->params['q'],$args);
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
