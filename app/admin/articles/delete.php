<?php
namespace CADB\App\admin\articles;

$Acl = 'administrator';

class delete extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();
		$this->layout = 'admin';

		if(!$this->params['nid']) {
			\CADB\Lib\Error("단체협약서 번호를 입력하세요.");
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Agreement::getFieldInfo(1);
		$this->articles = \CADB\Agreement::getAgreement($this->params['nid'],($this->params['did'] ? $this->params['did'] : 0));
		if(!$this->articles) {
			\CADB\Lib\Error("존재하지 않는 단체협약입니다.");
		}
		if(\CADB\Privilege::checkAgreement($this->articles) == false) {
			\CADB\Lib\Error('접근 권한이 없습니다.');
		}

		$ret = \CADB\Agreement\DBM::delete($this->fields,$this->params['nid']);
		if(!$ret) {
			foreach($this->params as $k => $v) {
				if(preg_match("/^[ao]{1}[0-9]+$/i",$k)) {
					$args[$k] = $v;
				}
			}
			$queryString = $this->makeQuery($this->params['q'],$args);
			\CADB\Lib\RedirectURL( '/admin/articles/'.($queryString ? "?".$queryString : "") );
		} else {
			\CADB\Lib\Error(\CADB\Agreement\DBM::errorMsg());
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
