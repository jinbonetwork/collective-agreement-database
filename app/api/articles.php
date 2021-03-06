<?php
namespace CADB\App\api;

$Acl = "authenticated";

class articles extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Agreement::getFieldInfo(1);
		$this->fields['standard'] = array();
		$this->fields['article'] = array();
		foreach($fields['field'] as $f => $v) {
			if($v['table'] == 'agreement') {
				$this->fields['article'][] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'], 'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);
			}
		}

		if($this->params['q'] && !mb_detect_encoding($this->params['q'],'UTF-8',true)) {
			$this->params['q'] = mb_convert_encoding($this->params['q'],'utf-8','euckr');
		}

		foreach($this->params as $k => $v) {
			if(preg_match("/^[ao]{1}[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}
		if($this->params['nid']) {
			$this->articles = \CADB\Agreement::getAgreement( $this->params['nid'], ($this->params['did'] ? $this->params['did'] : 0), $this->params['q'], $args, 0 );
			if(\CADB\Privilege::checkAgreement($this->articles)) {
				$this->articles['owner'] = 1;
			} else {
				$this->articles['owner'] = 0;
			}
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
			if(!$this->params['page']) $this->params['page'] = 1;

			$this->standard = array();
			if($this->params['mode'] == 'init') {
				$nid = \CADB\Guide::getCurrent(($this->params['nid'] ? $this->params['nid'] : 1));
				$taxonomys = \CADB\Guide::getTaxonomy($nid);
				foreach($this->params as $k => $v) {
					if( preg_match("/^a[0-9]+$/i",$k) && in_array( (int)substr($k,1), $taxonomys) ) {           
						$g_args[$k] = $v;
					}
				}
				$this->standard = \CADB\Guide::getList($this->params['q'],$g_args);
				$this->result['standard'] = array(
					'q'=> $this->params['q'],
					'taxonomy'=>$args,
					'count' => (@count($this->standard) > 0 ? @count($this->standard) : 0)
				);
			}

			$total_cnt = \CADB\Agreement::totalCnt($this->params['q'],$args);
			$total_page = (int)( ( $total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 20) ) + 1;
			if($total_cnt && $this->params['page'] <= $total_page) {
				$this->articles = \CADB\Agreement::getList($this->params['q'],$this->params['page'],($this->params['limit'] ? $this->params['limit'] : 20),$args);
				$this->result['articles'] = array(
					'total_cnt'=>$total_cnt,
					'total_page'=>$total_page,
					'page'=>$this->params['page'],
					'count'=>@count($this->articles)
				);
			} else {
				$this->result['articles'] = array(
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
