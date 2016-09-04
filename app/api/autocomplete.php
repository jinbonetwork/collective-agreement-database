<?php
namespace CADB\App\api;

$Acl = "authenticated";

class autocomplete extends \CADB\Controller {
	public function process() {
		$this->params['output'] = 'json';
		$context = \CADB\Model\Context::instance();

		if(!$this->params['q']) {
			$this->result = array(
				'found' => false,
				'error' => "자동완성할 키워드를 입력하세요."
			);
		} else {
			$redis = new \Redis();
			try {
				$redis->connect('127.0.0.1','6379', 2.5, NULL, 150);
				if($redis->select(1) == false) {
					$this->result = array(
						'found' => false,
						'error' => "index 1 database 에 연결할 수 없습니다."
					);
				} else {
					$this->recommand = $redis->zRange($this->params['q'],0,-1);
					if(@count($this->recommand)) {
						$this->result = array(
							'found' => true,
							'total_cnt'=>@count($this->recommand)
						);
					} else {
						$this->result = array(
							'found' => true,
							'total_cnt'=>0
						);
					}
				}
			} catch(RedisException $e) {
				var_dump($e);
			}
			$redis->close();
		}
	}
}
