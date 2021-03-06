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
		if($this->params['q'] && !mb_detect_encoding($this->params['q'],'UTF-8',true)) {
			$this->params['q'] = mb_convert_encoding($this->params['q'],'utf-8','euckr');
		}

		if($this->params['oid']) {
			$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
			if($this->organize) {
				if(!$this->organize['f7']) {
					$this->organize['f7'] = '정보없음';
				}
				$this->fields['owner'] = array('subject' => '운영자','type'=>'int','multiple'=>false);
				if(\CADB\Privilege::checkOrganizes($this->organize)) {
					$this->organize['owner'] = 1;
				} else {
					$this->organize['owner'] = 0;
				}
				$agreement = \CADB\Agreement::getAgreementsByOid($this->params['oid']);
				$this->fields['nid'] = array('subject' => '단체협약','type'=>'int','multiple'=>true);
				$this->organize['nid'] = array();
				if($agreement && is_array($agreement)) {
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
				if(preg_match("/^p[0-9]+$/i",$k) || $k == 'pdepth') {
					$this->search_mode = 'depth';
					$args[$k] = $v;
				} else if(preg_match("/^o[0-9]+$/i",$k)) {
					$this->search_mode = 'args';
					$args[$k] = $v;
				}
			}

			if(!$this->params['page']) $this->params['page'] = 1;
			\CADB\Organize::setMode($this->search_mode);
			$total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
			$total_page = (int)( ( $total_cnt - 1 ) / ($this->params['limit'] ? $this->params['limit'] : 20) ) + 1;
			$this->fields['owner'] = array('subject' => '운영자','type'=>'int','multiple'=>false);
			$this->fields['nid'] = array('subject' => '단체협약','type'=>'int','multiple'=>true);
			if($total_cnt && $this->params['page'] <= $total_page) {
				$this->organize = \CADB\Organize::getList($this->params['q'],$this->params['page'],($this->params['limit'] ? $this->params['limit'] : 20),$args);
				for($i=0; $i<count($this->organize); $i++) {
					if(\CADB\Privilege::checkOrganizes($this->organize[$i])) {
						$this->organize[$i]['owner'] = 1;
					} else {
						$this->organize[$i]['owner'] = 0;
					}
					$this->organize[$i]['nid'] = array();
					$agreement = \CADB\Agreement::getAgreementsByOid( $this->organize[$i]['oid'], $this->organize[$i]['vid'] );
					if($agreement && is_array($agreement)) {
						foreach($agreement as $ag) {
							$this->organize[$i]['nid'][] =  array(
								'nid'=>$ag['nid'],
								'did'=>$ag['did'],
								'subject'=>$ag['subject']
							);
						}
					}
				}
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
