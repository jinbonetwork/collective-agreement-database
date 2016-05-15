<?php
namespace CADB\App\api\save;

$Acl = 'owner';

class articles extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$fields = \CADB\Agreement::getFieldInfo(1);
		$this->fields = array();
		foreach($fields['field'] as $f => $v) {
			if($v['table'] == 'agreement') {
				$this->fields[$f] = $v;
			}
		}

		/* check field type */
		if($this->params['mode'] == 'delete' || $this->params['mode'] == 'fork') {
			if(!$this->params['nid']) {
				\CADB\RespondJson::ResultPage( array( -1, '단체협약 번호를 입력하세요' ) );
			}
			if($this->params['mode'] == 'fork') {
				if(!$this->params['did']) {
					\CADB\RespondJson::ResultPage( array( -1, '단체협약 버젼번호를 입력하세요' ) );
				}
			}
		} else {
			if(!$this->params['subject']) {
				\CADB\RespondJson::ResultPage( array( -1, '단협 제목을 입력하세요' ) );
			}
			if(!$this->params['content']) {
				\CADB\RespondJson::ResultPage( array( -2, '단협 내용을 입력하세요' ) );
			}
			foreach($this->fields as $fid => $v) {
				if( $v['required'] ) {
					if(!$this->params['f'.$fid]) {
						\CADB\RespondJson::ResultPage( array( $fid, $v['subject'].'을 입력하세요' ) );
					}
				}
			}
		}

		if($this->params['nid']) {
			$this->articles = \CADB\Agreement::getAgreement( $this->params['nid'], $this->params['did'] );
			if(!$this->articles) {
				\CADB\RespondJson::ResultPage( array( -3, '존재하지 않는 단협입니다.' ) );
			}
			if($this->params['mode'] == 'delete') {
				$ret = \CADB\Agreement\DBM::delete($this->fields,$this->params['nid']);
			} else if($this->params['mode'] == 'fork') {
				$ret = \CADB\Agreement\DBM::fork($this->fields,$this->params['nid'],$this->params['did']);
			} else {
				$ret = \CADB\Agreement\DBM::modify($this->fields,$this->articles,$this->params);
			}
			if($ret < 0) {
				\CADB\RespondJson::ResultPage( array( -4, '데이터베이스를 수정하는 도중 장애가 발생했습니다.' ) );
			}
		} else {
			$ret = \CADB\Agreement\DBM::insert($this->fields,$this->params);
			if($ret < 0) {
				\CADB\RespondJson::ResultPage( array( -4, \CADB\Agreement\DBM::errorMsg() ? \CADB\Agreement\DBM::errorMsg() : '데이터베이스에 입력하는 도중 장애가 발생했습니다.' ) );
			}
		}
		$this->nid = $ret;
	}
}
?>
