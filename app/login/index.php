<?php
namespace CADB\App\login;

importLibrary('auth');

class index extends \CADB\Controller {

	public function process() {
		$context = \CADB\Model\Context::instance();

		if($this->params['request_URI'])
			$redirect_uri .= "?requestURI=".rawurldecode($this->params['request_URI']);

		if($this->params['output'] != "json" && $this->params['output'] != "xml") {
			importResource('app-login',true);
		}
		if(doesHaveMembership()) {
			if($this->params['output'] == "xml") {
				Respond::ResultPage(array(2,"이미 로그인하셨습니다"));
			} else if($this->params['output'] == "json") {
				RespondJson::ResultPage(array(2,"이미 로그인하셨습니다"));
			} else { 
				Respond::ResultPage(array(-3, "이미 로그인하셨습니다."));
			}
		}
		$this->title = $context->getProperty('service.title')." 로그인";
	}
}
?>
