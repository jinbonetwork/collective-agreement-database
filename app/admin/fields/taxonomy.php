<?php
namespace CADB\App\admin\fields;

$Acl = 'administrator';

class taxonomy extends \CADB\Controller {
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
		if($mode != 'add') {
			if(!$this->params['cid']) {
				\CADB\RespondJson::ResultPage( array( -1, '분류번호를 지정하세요') );
			}
			$this->taxonomy = \CADB\Taxonomy\DBM::getTaxonomy($this->params['cid']);
			if(!$this->taxonomy) {
				\CADB\RespondJson::ResultPage( array( -1, '존재하지 않는 분류입니다') );
			}
		}
		if($mode != 'delete') {
			if(!$this->params['subject']) {
				\CADB\RespondJson::ResultPage( array( -2, '분류이름을 지정하세요') );
			}
			$this->params['subject'] = trim($this->params['subject']);
			$taxonomies = \CADB\Taxonomy\DBM::searchTaxonomy('subject', $this->params['name']);
			if($mode == 'add' && @count($taxonomies) > 0) {
				\CADB\RespondJson::ResultPage( array( -2, '이미 사용중인 이름입니다.') );
			} else if($mode == 'modify') {
				if( is_array($taxonomies) ) {
					foreach($taxonomies as $_cid => $taxonomy) {
						if($_cid != $this->params['cid']) {
							\CADB\RespondJson::ResultPage( array( -2, '이미 사용중인 이름입니다.') );
						}
					}
				}
			}
		}
	}

	private function add() {
		$this->cid = \CADB\Taxonomy\DBM::insert($this->params);
		$this->params['cid'] = $this->cid;
		$this->taxonomy = \CADB\Taxonomy\DBM::getTaxonomy($this->cid);
		\CADB\RespondJson::PrintResult( array( 'error' => $this->cid, 'message' => $this->taxonomy) );
	}

	private function modify() {
		$this->cid = \CADB\Taxonomy\DBM::modify($this->taxonomy,$this->params);
		if($this->cid) {
			$this->taxonomy = \CADB\Taxonomy\DBM::getTaxonomy($this->cid);
			\CADB\RespondJson::PrintResult( array( 'error' => $this->cid, 'message' => $this->taxonomy) );
		}
	}

	private function delete() {
		\CADB\Taxonomy\DBM::delete($this->taxonomy);
		$this->cid = $this->params['cid'];
		\CADB\RespondJson::ResultPage( array( $this->cid, '삭제되었습니다.') );
	}
}
?>
