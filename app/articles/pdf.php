<?php
namespace CADB\App\articles;

$Acl = "authenticated";

class pdf extends \CADB\Controller {
	public function process() {
		$context = \CADB\Model\Context::instance();

		if(!$this->params['nid']) {
			Error('단체협약서 번호를 입력하세요.');
		}

		if(!$this->themes) $this->themes = $context->getProperty('service.themes');

		$this->fields = \CADB\Agreement::getFieldInfo(1);
		$this->articles = \CADB\Agreement::getAgreement($this->params['nid'],($this->params['did'] ? $this->params['did'] : 0));
		if(!$this->articles) {
			Error('존재하지 않는 단체협약입니다.');
		}

		\CADB\Log::articleLog('pdf',$this->params['nid'],($this->params['did'] ? $this->params['did'] : 0), "단체협약: [".$this->articles['subject']."]을 PDF 조회했습니다.");

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($context->getProperty('service.title'));
		$pdf->SetTitle($this->articles['subject']);
		$pdf->SetSubject($this->articles['subject']);
		$pdf->SetKeywords(preg_replace("/[ ]{1,}/i",", ", $this->article['subject']));

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $context->getProperty('service.title'), $context->getProperty('service.domain'), array(0,64,255), array(0,64,128));
		$pdf->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		$pdf->SetFont('nanumbarungothic', '', 14, '', true);

		$pdf->AddPage();

		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

		$pdf->writeHTMLCell(0, 0, '', '', '<h1>'.$this->articles['subject'].'</h1><br><br>', 0, 1, 0, true, '', true);

		ob_start();
		$theme_html_file = "";
		if($this->themes) {
			$theme_html_file = CADB_PATH."/themes/".$this->themes."/articles/pdf.html.php";
			if($theme_html_file && file_exists($theme_html_file)) {
				include $theme_html_file;
			} else {
				include dirname(__FILE__)."/pdf.html.php";
			}
		} else {
			include dirname(__FILE__)."/pdf.html.php";
		}
		$content = ob_get_contents();
		ob_end_clean();

		$pdf->SetFont('nanumbarungothic', '', 12, '', true);
		$pdf->writeHTML($content, true, false, false, false, 'center');

		$pdf->SetFont('nanumbarungothic', '', 14, '', true);
		$pdf->writeHTMLCell(0, 0, '', '', $this->articles['content'], 0, 1, 0, true, '', true);

		$pdf->Output($this->articles['subject'].'.pdf', 'I');
	}
}
