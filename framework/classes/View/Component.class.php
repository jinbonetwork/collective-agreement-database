<?php
namespace CADB\View;

class Component extends \CADB\Objects {
	public static function instance() {
		return self::_instance(__CLASS__);
	}

	function __construct() {
	}

	public static function getComponent($component,$args) {
		@extract($args);
		$component_path = CADB_PATH."/component/".$component.".html.php";
		if(file_exists($component_path)) {
			include $component_path;
		}
	}
}
?>
