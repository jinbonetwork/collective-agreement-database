<?php
namespace CADB\App\api;

$Acl = "authenticated";

class orgs extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Organize::getFieldInfo(1);
		$this->fields = array();
		foreach($fields as $f => $v) {
			$this->fields[] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'],'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);
		}

		if($this->params['oid']) {
			$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
			if($this->organize) {
				$org_map = array();
				$org_map[] = array(
					'oid' => ($this->organize['depth'] > 1 ? $this->organize['oid'] : 0),
					'name' => $this->organize['nojo']
				);
				for($i=2; $i<($this->organize['depth']); $i++) {
					if($this->organize['p'.$i]) {
						$org_map[] = array(
							'oid' => $this->oraganize['p'.$i],
							'name' => $this->organize['sub'.($i-1)]
						);
					}
				}
				if($this->organize['depth'] > 1) {
					$org_map[] = array(
						'oid' => 0,
						'name' => $this->organize['sub'.($this->organize['depth']-1)]
					);
				}
				$this->organize['organizes'] = $org_map;
				if(!$this->organize['f7']) {
					$this->organize['f7'] = '정보없음';
				}
				if(\CADB\Privilege::checkOrganize($this->organize['oid'])) {
					$this->organize['owner'] = 1;
				} else {
					$this->organize['owner'] = 0;
				}
				$agreement = \CADB\Agreement::getAgreementsByOid($this->params['oid']);
				if($agreement && is_array($agreement)) {
					$this->fields['nid'] = array('subject' => '단체협약','type'=>'int','multiple'=>true);
					$this->organize['nid'] = array();
					foreach($agreement as $ag) {
						$this->organize['nid'][] =  array(
							'nid'=>$ag['nid'],
							'did'=>$ag['did'],
							'subject'=>$ag['subject']
						);
					}
				}
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
					'orgs'=> array(
						'total_cnt'=>$total_cnt,
						'total_page'=>$total_page,
						'page'=>$this->params['page'],
						'count'=>@count($this->organize)
					)
				);
			} else {
				$this->result = array(
					'orgs'=> array(
						'total_cnt'=>$total_cnt,
						'total_page'=>$total_page,
						'page'=>$this->params['page'],
						'count'=>0,
						'error'=>'검색결과가 없습니다.'
					)
				);
			}
		}
	}
}
?>
