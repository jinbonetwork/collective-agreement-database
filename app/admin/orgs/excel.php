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
		$this->orgs = \CADB\Organize::getList($this->params['q'],-1,0,$args);

		$objPHPExcel = new \PHPExcel();
		$objPHPExcel->getProperties()->setCreator($context->getProperty('service.title'))
									->setLastModifiedBy($context->getProperty('service.title'))
									->setTitle('조직현황')
									->setSubject('조직현황')
									->setDescription('조직현황')
									->setKeywords('조직현황')
									->setCategory('조직현황');

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
						->setCellValue("A".$idx,$this->orgs[$i]['oid'])
						->setCellValue("B".$idx,$this->orgs[$i]['nojo'])
						->setCellValue("C".$idx,$this->orgs[$i]['sub1'])
						->setCellValue("D".$idx,$this->orgs[$i]['sub2'])
						->setCellValue("E".$idx,$this->orgs[$i]['sub3'])
						->setCellValue("F".$idx,$this->orgs[$i]['sub4']);
			for($j=0; $j<count($this->fields); $j++) {
				$fidx = chr(ord("G")+$j);
				$v = $this->orgs[$i][$this->fields[$j]['field']];
				switch($this->fields[$j]['type']) {
					case 'taxonomy':
						if($v) {
							$vl = "";
							$c=0;
							foreach($v as $t => $value) {
								$vl .= ($c++ ? "," : "").$value['name'];
							}
							$objPHPExcel->setActiveSheetIndex(0)
										->setCellValue($fidx.$idx,$vl);
						}
						break;
					default:
						$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue($fidx.$idx,$v);
						break;
				}
			}
		}

		$objPHPExcel->getActiveSheet()->setTitle('조직현황');
		$objPHPExcel->setActiveSheetIndex(0);

		$filename = "organize.".date("Y.m.d").".xls";

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');

		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
}
