<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class delete extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		if(!$this->params['oid']) {
			\CADB\Lib\Error("조직 고유아이디를 입력하세요.");
		}

		$this->fields = \CADB\Organize::getFieldInfo(1);
		$this->organize = \CADB\Organize::getOrganizeByOid($this->params['oid']);
		if(!$this->organize) {
			\CADB\Lib\Error("조직정보를 검색할 수 없습니다.");
		}

		$ret = \CADB\Organize\DBM::delete($this->fields,$this->params['oid']);
		if(!$ret) {
			foreach($this->params as $k => $v) {
				if(preg_match("/^o[0-9]+$/i",$k)) {
					$args[$k] = $v;
				}
			}
			$queryString = $this->makeQuery($this->params['q'],$args);
			\CADB\Lib\RedirectURL( '/admin/orgs/'.($queryString ? "?".$queryString : "") );
		} else {
			\CADB\Lib\Error(\CADB\Organize\DBM::errorMsg());
		}
	}

	public function makeQuery($q,$args) {
		$arg = '';
		$c = 0;
		if($q) {
			$arg = ($c++ ? "&" : "")."q=".$q;
		}
		if(is_array($args)) {
			foreach($args as $k => $v) {
				$arg .= ($c++ ? "&" : "").$k."=";
				if(is_array($v)) {
					$arg .= "[".implode(",",$v)."]";
				} else {
					$arg .= $v;
				}
			}
		}
		if($c) $arg .= "&";
		return $arg;
	}
}
?>
