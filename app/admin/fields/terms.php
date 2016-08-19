<?php
namespace CADB\App\admin\fields;

$Acl = 'administrator';

class terms extends \CADB\Controller {
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
		if(!$this->params['cid']) {
			\CADB\RespondJson::ResultPage( array( -1, '분류번호를 지정하세요') );
			$this->taxonomy = \CADB\Taxonomy\DBM::getTaxonomy($this->params['cid']);
			if(!$this->taxonomy) {
				\CADB\RespondJson::ResultPage( array( -1, '존재하지 않는 분류입니다') );
			}
		}
		if($mode != 'add') {
			if(!$this->params['tid']) {
				\CADB\RespondJson::ResultPage( array( -2, '분류항목 번호를 지정하세요') );
			}
			$this->terms = \CADB\Taxonomy\DBM::getTaxonomyTerms($this->params['tid']);
			if(!$this->terms) {
				\CADB\RespondJson::ResultPage( array( -2, '존재하지 않는 필드입니다') );
			}
		}
		if($mode != 'delete') {
			if(!$this->params['name']) {
				\CADB\RespondJson::ResultPage( array( -3, '분류항목 이름을 지정하세요') );
			}
			$this->params['name'] = trim($this->params['name']);
			$terms = \CADB\Taxonomy\DBM::searchTerms($this->params['cid'], 'name', $this->params['name']);
			if($mode == 'add' && @count($terms) > 0) {
				\CADB\RespondJson::ResultPage( array( -3, '이미 사용중인 이름입니다.') );
			} else if($mode == 'modify') {
				if( is_array($terms) ) {
					foreach($terms as $_tid => $term) {
						if($_tid != $this->params['tid']) {
							\CADB\RespondJson::ResultPage( array( -3, '이미 사용중인 이름입니다.') );
						}
					}
				}
			}
		}
	}

	private function add() {
		$this->tid = \CADB\Taxonomy\DBM::insert($this->params);
		$this->params['tid'] = $this->tid;
		$this->terms = \CADB\Taxonomy\DBM::getTaxonomyTerms($this->tid);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->tid, 'message' => $this->terms) );
	}

	private function modify() {
		$this->tid = \CADB\Taxonomy\DBM::modify($this->terms,$this->params);
		if($this->tid) {
			$this->terms = \CADB\Taxonomy\DBM::getTaxonomyTerms($this->tid);
			\CADB\RespondJson::PrintResult( array( 'error' => $this->tid, 'message' => $this->terms) );
		}
	}

	private function delete() {
		\CADB\Taxonomy\DBM::delete($this->terms);
		$this->tid = $this->params['tid'];
		\CADB\RespondJson::ResultPage( array( $this->tid, '삭제되었습니다.') );
	}
}
?>
