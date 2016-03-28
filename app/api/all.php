<?php
namespace CADB\App\api;

$Acl = "authenticated";

class all extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Fields::getFields('all',1);
		$this->fields = array();
		foreach($fields as $f => $v) {
			if(!$this->fields[$v['table']])
				$this->fields[$v['table']] = array();
			$this->fields[$v['table']][] = array('field'=>'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'], 'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);
		}

		$nid = \CADB\Guide::getCurrent(($this->params['nid'] ? $this->params['nid'] : 1));

		foreach($this->params as $k => $v) {
			if(preg_match("/^[ao]{1}[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}

		$this->params['page'] = 1;

		/* organize search */
		$organize_total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
		$organize_total_page = (int)( ( $organize_total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 10) ) + 1;
		if($organize_total_cnt) {
			$this->organize = \CADB\Organize::getList($this->params['q'],1,($this->params['limit'] ? $this->params['limit'] : 10),$args);
			$this->result['orgs'] = array(
				'total_cnt'=>$organize_total_cnt,
				'total_page'=>$organize_total_page,
				'count'=>@count($this->organize)
			);
		} else {
			$this->result['orgs'] = array(
				'total_cnt'=>0,
				'total_page'=>0,
				'count'=>0,
				'error'=>'검색결과가 없습니다.'
			);
		}

		/* standard guide */
		$taxonomys = \CADB\Guide::getTaxonomy($nid);
		foreach($this->params as $k => $v) {
			if( preg_match("/^a[0-9]+$/i",$k) && in_array( (int)substr($k,1), $taxonomys ) ) {
				$g_args[$k] = $v;
			}
		}
		$this->standard = \CADB\Guide::getList($this->params['q'],$g_args);
		$this->result['standard'] = array(
			'q'=> $this->params['q'],
			'taxonomy'=>$args,
			'count' => (@count($this->standard) > 0 ? @count($this->standard) : 0)
		);

		/* articles */
		\CADB\Agreement::setTaxonomy($taxonomys);
		$total_cnt = \CADB\Agreement::totalCnt($this->params['q'],$args);
		$total_page = (int)( ( $total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 10) ) + 1;
		if($total_cnt) {
			$this->articles = \CADB\Agreement::getList($this->params['q'],1,($this->params['limit'] ? $this->params['limit'] : 10),$args);
			$this->result['articles'] = array(
				'total_cnt'=>min($total_cnt,@count($this->articles)),
				'total_page'=>$total_page,
				'count'=>@count($this->articles)
			);
		} else {
			$this->result['articles'] = array(
				'total_cnt'=>0,
				'total_page'=>0,
				'count'=>0,
				'error'=>'검색결과가 없습니다.'
			);
		}
	}
}
?>
