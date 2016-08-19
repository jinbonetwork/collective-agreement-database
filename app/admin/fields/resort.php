<?php
namespace CADB\App\admin\fields;

$Acl = 'administrator';

class resort extends \CADB\Controller {
	public function process() {
		if(!$this->params['table']) {
			\CADB\RespondJson::ResultPage( array( -1, '테이블을 지정하세요') );
		}

		\CADB\Fields\DBM::resort($this->params['table'],$this->params['index']);
		\CADB\RespondJson::ResultPage( array( 0, '정렬이 완료되었습니다.') );
	}
}
?>
