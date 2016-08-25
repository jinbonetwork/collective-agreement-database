<?php
namespace CADB\App\admin\standards;

$Acl = 'administrator';

class save extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';

		$context = \CADB\Model\Context::instance();

		if(!$this->params['table']) {
			\CADB\RespondJson::ResultPage( array( 0, '작업 테이블을 지정하세요') );
		}
		if(!$this->params['mode']) {
			\CADB\RespondJson::ResultPage( array( 0, '작업 모드를 지정하세요') );
		}
		switch($this->params['table']) {
			case 'guide':
				$this->validateGuide();
				switch($this->params['mode']) {
					case 'modify':
						$this->modifyGuide();
						break;
					case 'delete':
						$this->deleteGuide();
						break;
				}
				break;
			case 'clause':
				$this->validateClause();
				$this->fields = \CADB\Guide::getFieldInfo(1);
				switch($this->params['mode']) {
					case 'add':
						$this->addClause();
						break;
					case 'modify':
						$this->modifyClause();
						break;
					case 'delete':
						$this->deleteClause();
						break;
				}
				break;
		}
	}

	private function validateGuide() {
		if($this->params['mode'] != 'add') {
			if(!$this->params['nid']) {
				\CADB\RespondJson::ResultPage( array( -1, '모범단협번호를 지정하세요') );
			}
			$this->guide = \CADB\Guide\DBM::getGuide($this->params['nid']);
			if(!$this->guide) {
				\CADB\RespondJson::ResultPage( array( -1, '존재하지 않는 모범단협번호입니다') );
			}
		}
		if($this->params['mode'] != 'delete') {
			if(!$this->params['subject']) {
				\CADB\RespondJson::ResultPage( array( -2, '모범단협 제목을 입력하세요') );
			}
			if(!$this->params['year']) {
				\CADB\RespondJson::ResultPage( array( -2, '모범단협 년도를 입력하세요') );
			}
		}
	}

	private function modifyGuide() {
		$this->nid = \CADB\Guide\DBM::modifyGuide($this->guide,$this->params);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->nid, 'message' => '수정되었습니다' ) );
	}

	private function deleteGuide() {
		\CADB\Guide\DBM::deleteGuide($this->guide);
		$this->nid = $this->params['nid'];
		\CADB\RespondJson::PrintResult( array( 'error' => $this->nid, 'message' => '삭제되었습니다.' ) );
	}

	private function validateClause() {
		if($this->params['mode'] != 'add') {
			if(!$this->params['id']) {
				\CADB\RespondJson::ResultPage( array( -1, '모범단협 조항번호를 지정하세요') );
			}
			$this->clause = \CADB\Guide::getClause($this->params['id']);
			if(!$this->clause) {
				\CADB\RespondJson::ResultPage( array( -1, '존재하지 않는 모범단협 조항번호입니다') );
			}
		}
		if($this->params['mode'] != 'delete') {
			if(!$this->params['subject']) {
				\CADB\RespondJson::ResultPage( array( -2, '모범단협 조항제목을 입력하세요') );
			}
		}
	}

	private function addClause() {
		$this->id = \CADB\Guide\DBM::addClause($this->params, $this->fields);
		if($this->id > 0) {
			\CADB\RespondJson::PrintResult( array( 'error' => $this->id, 'message' => '추가되었습니다' ) );
		} else {
			\CADB\RespondJson::PrintResult( array( 'error' => $this->id, 'message' => \CADB\Guide\DBM::error() ) );
		}
	}

	private function modifyClause() {
		$this->id = \CADB\Guide\DBM::modifyClause($this->clause, $this->params, $this->fields);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->id, 'message' => '수정되었습니다' ) );
	}

	private function deleteClause() {
		\CADB\Guide\DBM::deleteClause($this->clause);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->clause['id'], 'message' => '삭제되었습니다' ) );
	}
}
