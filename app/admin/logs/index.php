<?php
namespace CADB\App\admin\logs;

$Acl = 'administrator';

class index extends \CADB\Controller {
	public function process() {
		$this->layout = 'admin';
		$this->fullscreen = true;

		$this->fields = \CADB\Fields::getFields('all');
		$this->taxonomies = \CADB\Taxonomy\DBM::getTaxonomyList();
		$this->taxonomy_terms = \CADB\Taxonomy\DBM::getAllTaxonomyTerms();

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

	public function viewArticle($log) {
		switch($log['table']) {
			case 'article':
				$link = '<a href="'.\CADB\Lib\url('article/'.($log['fid'] ? $log['fid'] : $log['oid'])).'" target="_blank" class="article">단협보기</a>';
				break;
			case 'org':
				$link = '<a href="'.\CADB\Lib\url('orgs/'.$log['oid']).'" target="_blank" class="org">노조보기</a>';
				break;
			case 'guide':
				$link = '<a href="'.\CADB\Lib\url('standards/'.$log['oid']).'" target="_blank" class="standard">모범단협보기</a>';
				break;
			case 'field':
				$link = '<span class="field">'.$this->fields[$log['oid']]['subject'].'</span>';
				break;
			case 'taxonomy':
				$link = '<span class="taxonomy">'.$this->taxonomies[$log['oid']]['subject'].'</span>';
				break;
			case 'taxonomy_term':
				$link = '<span class="taxonomy-term">'.$this->taxonomies[$log['oid']]['subject']."::".$this->taxonomy_terms[$log['vid']]['name'].'</span>';
				break;
			case 'member':
				$link = '<a href="'.\CADB\Lib\url("admin/member/edit")."?mb_no=".$log['oid'].'" target="_blank" class="member">회원정보보기</a>';
				break;
			case 'user':
				$link = '<a href="'.\CADB\Lib\url("admin/member/edit")."?mb_no=".$log['oid'].'" target="_blank" class="member">회원정보보기</a>';
				break;
			default:
				break;
		}
		return $link;
	}

	public function viewMember($log) {
		$link = '<a href="'.\CADB\Lib\url("admin/member/edit")."?mb_no=".$log['editor'].'" target="_blank">'.$log['name'].'</a>';
		return $link;
	}
}
?>
