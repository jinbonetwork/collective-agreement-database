<?php
namespace CADB\App\admin\autocomplete;

$Acl = 'administrator';

class save extends \CADB\Controller {
	public function process() {
		$this->output = 'json';

		$context = \CADB\Model\Context::instance();

		if(!($rdb = $context->getProperty('service.redis'))) {
			\CADB\RespondJson::ResultPage( array( -1, '자동완성 기능이 활성화 되어 있지 않습니다.') );
		}
		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$nid = \CADB\Guide::getCurrent();
		$cids = \CADB\Guide::getTaxonomy();
		$guide_taxonomy = \CADB\Taxonomy::getTaxonomy($cids);

		$fields = \CADB\Fields\DBM::searchField('autocomplete',1);
		$field_cids = array();
		$this->fields = array();
		if(is_array($fields)) {
			foreach($fields as $fid => $f) {
				switch($f['type']) {
					case 'taxonomy':
						$field_cids[] = $f['cid'];
						break;
					case 'char':
						$this->fields[$f['table']][$fid] = $f;
						break;
				}
			}
		}
		if(count($field_cids)) {
			$field_taxonomy = \CADB\Taxonomy::getTaxonomy($field_cids);
			$this->taxonomy = array_merge($guide_taxonomy,$field_taxonomy);
		} else {
			$this->taxonomy = $guide_taxonomy;
		}
		if(is_array($this->taxonomy) && @count($this->taxonomy)) {
			$txcids = array();
			foreach($this->taxonomy as $cid => $tx) {
				$txcids[] = $cid;
			}
			$this->taxonomy_terms = \CADB\Taxonomy::getTaxonomyTerms($txcids);
			foreach($this->taxonomy_terms as $cid => $taxo_terms) {
				foreach($taxo_terms as $tid => $terms) {
					$this->buildAutocomplete($terms['name'],$terms['name'],20);
				}
			}
		}
		\CADB\Organize::setMode('admin');
		$total_cnt = \CADB\Organize::totalCnt('',null);
		$orgs = \CADB\Organize::getList('',-1,0,null);

		for($i=0; $i<$total_cnt; $i++) {
			$row = $orgs[$i];
			if($row['nojo']) {
				$nojo = trim($row['nojo']);
				$this->buildAutocomplete($nojo,$nojo,15);
				$this->spaceBuildAutocomplete($nojo,$nojo,15);
				if(preg_match("/^전국/i",$nojo)) {
					$nojo2 = preg_replace("/^전국/i","",trim($nojo));
					$this->buildAutocomplete($nojo2,$nojo,15);
					$this->spaceBuildAutocomplete($nojo2,$nojo,15);
				}
			}
			for($s = 1; $s <= 4; $s++) {
				$score = 15 - ($s * 3);
				if($row['sub'.$s]) {
					$name = trim($row['sub'.$s]);
					$this->buildAutocomplete($name,$name,$score);
					$this->spaceBuildAutocomplete($name,$name,$score);
				}
			}
			if(is_array($this->fields['organize'])) {
				foreach($this->fields['organize'] as $fid => $f) {
					if($row['f'.$fid]) {
						$v = explode(",",$row['f'.$fid]);
						for($k=0; $k<@count($v); $k++) {
							$name = trim($v[$k]);
							$this->buildAutocomplete($name,$name,1);
							$this->spaceBuildAutocomplete($name,$name,1);
						}
					}
				}
			}
		}
		if(is_array($this->fields)) {
			foreach($this->fields as $table => $fieldlist) {
				if($table == 'organize') continue;
			}
		}
		$this->remakeRedis();
	}

	function spaceBuildAutocomplete($name,$fullname,$score) {
		if(preg_match("/ /i",$name)) {
			$_nojo = explode(" ",$name);
			for($j=1; $j<@count($_name); $j++) {
				$__name = trim($_name[$j]);
				$this->buildAutocomplete($__name,$fullname,$score);
			}
		}
	}

	function buildAutocomplete($name,$fullname,$score) {
		$name = trim($name);
		if($name) {
			for($l=0; $l<mb_strlen($name,'utf-8'); $l++) {
				if($name[$l] == ' ') continue;
				$key = mb_substr($name,0,($l+1),'utf-8');
				if(!$this->autocomplete[$key]) $this->autocomplete[$key] =  array();
				if(!$this->autocomplete[$key][$fullname]) {
					$this->autocomplete[$key][$fullname] = array('name'=>$fullname, 'score'=> 20);
				}
			}
		}
	}

	function remakeRedis() {
		$total_cnt = 0;
		$redis = new \Redis();
		try {
			$redis->connect('127.0.0.1','6379', 2.5, NULL, 150);
			if($redis->select(1) == false) {
				\CADB\RespondJson::ResultPage( array( -1, 'redis 데이터베이스에 연결할 수 없습니다.') );
			}
			$redis->flushDb();
			if(is_array($this->autocomplete)) {
				foreach($this->autocomplete as $k => $_data) {
					foreach($_data  as $data) {
						$redis->zAdd($k,$data['score'],$data['name']);
						$total_cnt++;
					}
				}
			}
		} catch(RedisException $e) {
			var_dump($e);
		}
		$redis->close();
		\CADB\RespondJson::ResultPage( array( 0, $total_cnt.'건의 자동완성문장을 입력했습니다.') );
	}
}
?>
