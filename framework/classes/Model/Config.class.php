<?php
namespace CADB\Model;

final class Config extends \CADB\Objects {
	public $database, $service, $session;

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	protected function __construct() {
		$this->ConfigLoader();
	}

	private function ConfigLoader() {
		global $database, $service, $session;
		$this->settings = array();
		if(file_exists(CADB_PATH."/config/settings.php")) {
			@include(CADB_PATH."/config/settings.php");
			$this->userdatabase = $userdatabase;
			$this->database = $database;
			$this->service = $service;
			$this->timeline = $timeline;
			$this->session = $session;
		}
		$this->updateContext();
	}

	public function updateContext() {
		$context = \CADB\Model\Context::instance();
		$configs = array('database','service','session');
		foreach($configs as $namespace) {
			if($namespace) {
				foreach($this->$namespace as $k => $v) {
					$context->setProperty($namespace.".".$k,$v);
				}
			}
		}
	}

	public function readResourceMap() {
		if(!$this->resoure_map) {
			$context = \CADB\Model\Context::instance();
			$map_file = CADB_PATH."/config/resources.map.json";
			if(file_exists($map_file)) {
				$fp = fopen($map_file,"r");
				$json = trim(fread($fp,filesize($map_file)));
				fclose($fp);
				$this->resource_map = json_decode($json,true);
			}
		}
		return $this->resource_map;
	}
}
?>
