<?php
namespace CADB\App\admin\fields;

$Acl = 'administrator';

class save extends \CADB\Controller {
	public function process() {
		$mode = $this->params['mode'];
		if(!$mode) {
			\CADB\RespondJson::ResultPage( array( -101, '작업 모드를 지정하세요') );
		}
		$this->validate($mode);
		switch($mode) {
			case "add":
				$this->add();
				break;
			case "modify":
				$this->modify();
				break;
			case "delete":
				$this->delete();
				break;
		}
	}

	private function validate($mode) {
		if(!$this->params['table']) {
			\CADB\RespondJson::ResultPage( array( -1, '테이블을 지정하세요') );
		}
		if($mode != 'add') {
			if(!$this->params['fid']) {
				\CADB\RespondJson::ResultPage( array( -2, '필드번호를 지정하세요') );
			}
			$this->field = \CADB\Fields\DBM::getField($this->params['fid']);
			if(!$this->field) {
				\CADB\RespondJson::ResultPage( array( -2, '존재하지 않는 필드입니다') );
			}
		}
		if($mode != 'delete') {
			if(!$this->params['subject']) {
				\CADB\RespondJson::ResultPage( array( -3, '필드제목을 지정하세요') );
			}
			$this->params['subject'] = trim($this->params['subject']);
			$fields = \CADB\Fields\DBM::searchField('subject',$this->params['subject']);
			if($mode == 'add' && @count($fields) > 0) {
				\CADB\RespondJson::ResultPage( array( -3, '다른 필드에서 사용중인 이름입니다.') );
			} else if($mode == 'modify') {
				if( is_array($fields) ) {
					foreach($fields as $_fid => $field) {
						if($_fid != $this->params['fid']) {
							\CADB\RespondJson::ResultPage( array( -3, '다른 필드에서 사용중인 이름입니다.') );
						}
					}
				}
			}

			if( empty($this->params['iscolumn']) ) $this->params['iscolumn'] = 0;
			if($mode == 'modify') {
				if($this->params['iscolumn'] != $this->field['iscolumn']) {
					\CADB\RespondJson::ResultPage( array( -5, 'field column 여부는 변경할 수 없습니다.'.$this->params['iscolumn']." ".$this->field['iscolumn']) );
				}
			}

			if(!$this->params['type']) {
				\CADB\RespondJson::ResultPage( array( -5, '입력방식을 지정하세요') );
			}
			if($mode == 'modify') {
				if($this->params['type'] != $this->field['type']) {
					\CADB\RespondJson::ResultPage( array( -5, '입력방식은 변경할 수 없습니다') );
				}
			}
			if( !in_array( $this->params['type'], array('char','text','taxonomy','int','date') ) ) {
				\CADB\RespondJson::ResultPage( array( -5, '허용되지 않는 입력방식입니다.') );
			}
			if($this->params['type'] == 'taxonomy') {
				if(!$this->params['cid']) {
					\CADB\RespondJson::ResultPage( array( -8, '분류를 선택하세요.') );
				}
				if($mode == 'modify') {
					if($this->params['cid'] != $this->field['cid']) {
						\CADB\RespondJson::ResultPage( array( -8, '분류값을 변경할 수 없습니다.') );
					}
				}
			}
			if(!$this->params['iscolumn'] & $this->params['indextype'] != 'none') {
				\CADB\RespondJson::ResultPage( array( -11, '칼럼이 아니면 검색키로 사용할 수 없습니다.') );
			}
		}
	}

	private function add() {
		 $this->fid = \CADB\Fields\DBM::insert($this->params);
		 $this->params['fid'] = $this->fid;
		 if($this->params['iscolumn']) {
		 	\CADB\Fields\DBM::addColumn($this->fid, $this->params);
		 }
		$this->field = \CADB\Fields\DBM::getField($this->fid);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->fid, 'message' => $this->field) );
	}

	private function modify() {
		$this->fid = \CADB\Fields\DBM::modify($this->field,$this->params);
		if($this->fid) {
			$this->field = \CADB\Fields\DBM::getField($this->fid);
			\CADB\RespondJson::PrintResult( array( 'error' => $this->fid, 'message' => $this->field) );
		}
	}

	private function delete() {
		\CADB\Fields\DBM::delete($this->field);
		if($this->field['iscolumn']) {
			\CADB\Fields\DBM::dropColumn($this->params);
		}
		$this->fid = $this->params['fid'];
		\CADB\RespondJson::ResultPage( array( $this->fid, '삭제되었습니다.') );
	}
}
?>
