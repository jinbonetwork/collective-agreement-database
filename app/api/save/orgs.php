<?php
namespace CADB\App\api\save;

$Acl = 'owner';

class orgs extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		$this->fields = \CADB\Organize::getFieldInfo(1);

		/* check field type */
		if($this->params['mode'] == 'delete') {
			if(!$this->params['oid']) {
				\CADB\RespondJson::ResultPage( array( -1, '삭제할 단체 번호를 입력하세요' ) );
			}
		} else {
			if(!$this->params['nojo']) {
				\CADB\RespondJson::ResultPage( array( -1, '노조명을 입력하세요' ) );
			}
			foreach($this->fields as $fid => $v) {
				if( $v['required'] ) {
					if(!$this->params['f'.$fid]) {
						\CADB\RespondJson::ResultPage( array( $fid, $v['subject'].'을 입력하세요' ) );
					}
				}
			}
		}

		if($this->params['oid']) {
			$this->organize = \CADB\Organize::getOrganizeByOid( $this->params['oid'] );
			if(!$this->organize) {
				\CADB\RespondJson::ResultPage( array( -2, '존재하지 않는 단체입니다.' ) );
			}
			if($this->params['mode'] == 'delete') {
				$ret = \CADB\Organize\DBM::delete($this->fields,$this->params['oid']);
			} else {
				$ret = \CADB\Organize\DBM::modify($this->fields,$this->organize,$this->params);
			}
			if($ret < 0) {
				\CADB\RespondJson::ResultPage( array( -3, '데이터베이스를 수정하는 도중 장애가 발생했습니다.' ) );
			}
		} else {
			$ret = \CADB\Organize\DBM::insert($this->fields,$this->params);
			if($ret < 0) {
				\CADB\RespondJson::ResultPage( array( -3, \CADB\Organize\DBM::errorMsg() ? \CADB\Organize\DBM::errorMsg() : '데이터베이스에 입력하는 도중 장애가 발생했습니다.' ) );
			}
		}
		$this->oid = $ret;
	}
}
?>
