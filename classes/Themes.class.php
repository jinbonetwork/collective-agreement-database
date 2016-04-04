<?php
namespace CADB;

class Themes extends \CADB\Controller {
	public function themeHeader() {
		$context = \CADB\Model\Context::instance();
		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->breadcrumbs_class = "cadb-theme-".$this->themes;
		$this->initTheme();

		$header_file = $this->LayoutHeader();
		if($header_file) {
			extract((array)$this);
			ob_start();
			include_once $header_file;
			$html = ob_get_contents();
			ob_end_clean();
			print $html;
		}
	}

	public function themeFooter() {
		$context = \CADB\Model\Context::instance();
		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->initTheme();

		$footer_file = $this->LayoutFooter();
		if($footer_file) {
			extract((array)$this);
			ob_start();
			include_once $footer_file;
			$html = ob_get_contents();
			ob_end_clean();
			print $html;
		}
	}

	private function LayoutHeader() {
		$header_file = CADB_PATH."/themes/".$this->themes."/header.html.php";
		if(file_exists($header_file)) return $header_file;
		return null;
	}

	private function LayoutFooter() {
		$header_file = CADB_PATH."/themes/".$this->themes."/footer.html.php";
		if(file_exists($header_file)) return $header_file;
		return null;
	}
}
