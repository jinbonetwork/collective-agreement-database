<?php
namespace CADB\App\admin\orgs;

$Acl = 'administrator';

class excel extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		\CADB\Organize::setMode('admin');
		$fields = \CADB\Organize::getFieldInfo(1);
		foreach($fields as $f => $v) {
			$this->fields[] = array('field' => 'f'.$f, 'subject' => $v['subject'],'type'=>$v['type'], 'multiple'=>( $v['multiple'] ? true : false ),'cid'=>$v['cid']);
		}

		foreach($this->params as $k => $v) {
			if(preg_match("/^o[0-9]+$/i",$k)) {
				$args[$k] = $v;
			}
		}

		$this->total_cnt = \CADB\Organize::totalCnt($this->params['q'],$args);
		$this->orgs = \CADB\Organize::getList($this->params['q'],-1,0,$args)

		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator($context->getProperty('service.title'))
									->setLastModifiedBy($context->getProperty('service.title'))
									->setTitle('조직현황')
									->setSubject('조직현황')
									->setDescription('조직현황')
									->setKeywords('조직현황')
									->setCategory('조직현황')

		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', '조직번호')
					->setCellValue('B1', '노조명')
					->setCellValue('C1', '본부명')
					->setCellValue('D1', '지부명')
					->setCellValue('E1', '지회명')
					->setCellValue('F1', '분회명');
		for($i=0; $i<count($this->fields); $i++) {
			$fidx = chr(ord("G")+$i);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($fidx."1",$this->fields[$i]['subject']);
		}

		for($i=0; $i<@count($this->orgs); $i++) {
			$idx = 2+$i;
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue("A".$idx,$this->org[$i]['oid'])
						->setCellValue("B".$idx,$this->org[$i]['nojo'])
						->setCellValue("C".$idx,$this->org[$i]['sub1'])
						->setCellValue("D".$idx,$this->org[$i]['sub2'])
						->setCellValue("E".$idx,$this->org[$i]['sub3'])
						->setCellValue("F".$idx,$this->org[$i]['sub4']);
			for($j=0; $j<count($this->fields); $j++) {
				$fidx = chr(ord("G")+$j);
		}
	}
}
