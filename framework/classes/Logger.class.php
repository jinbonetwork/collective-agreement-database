<?php
namespace CADB;

class Logger extends \CADB\Objects {
	var $_logType;
	var $_errLogPath;
	var $_loggingID;
	public $last;

	protected function __construct() {
		$this->setLogger();
	}

	private function setLogger() {
		$this->_logType		= CADB_LOG_TYPE;
		$this->_errLogPath	= CADB_ERROR_LOG_PATH;
		$this->_loggingID	= CADB_LOG_ID;
		$this->last = (object) array();
	}

	public static function instance() {
		return self::_instance(__CLASS__);
	}

	public function setLogType($LOG_TYPE, $LOG_ID=null, $LOG_PATH=null) {
		$this->_logType = $LOG_TYPE;
		if($LOG_PATH) $this->_errLogPath = $LOG_PATH;
		if($LOG_ID) $this->_loggingID = $LOG_ID;
	}

	public function redirectPrintLog($message){
		header("Content-Type: text/html; charset=utf-8");

		ob_start();
		if(file_exists(CADB_RESOURCE_PATH."/html/error.html.php")) {
			require_once CADB_RESOURCE_PATH."/html/error.html.php";
		} else {?>
			<div style="border:1px solid #ccc; color:red; padding:15px;">
				<font style="color:green; font-weight:bold">Fix Me Please: </font> <? print $message; ?>
			</div>
		<?}
		$output = ob_get_contents();
		ob_end_flush();

		$this->last = (object) array(
			'callback' => 'redirectPrintLog',
			'type' => 'html',
			'output' => $output,
			'message' => $message,
		);
	}

	public function redirectFileLog($errorMsg)
	{
		if(!$this->_errLogPath) {
			return;
		}

		$day = date("Ymd");
		if($this->_loggingID)
			$logFilePath = $this->_errLogPath."/".$this->_loggingID.".".$day.".log";
		else
			$logFilePath = $this->_errLogPath."/".$day.".log";

		$errorMsg = "[".date(CADB_LOG_DATE_FORMAT)."] ".$errorMsg."\r\n";
		@error_log($errorMsg, 3, $logFilePath);

		$this->last = (object) array(
			'callback' => 'redirectFileLog',
			'type' => 'file',
			'path' => $logFilePath,
			'message' => $errorMsg,
		);
	}


	public function Error($e, $action = 0, $url = NULL)
	{
		if(is_object($e))
			$errorMsg = $e->getFile().":".$e->getLine()." => ".$e->getCode()." : ".$e->getMessage();
		else 
			$errorMsg = $e;

		if($this->_logType == CADB_LOG_TYPE_PRINT) {
			if($action != CADB_ERROR_ACTION_AJAX)
				$this->redirectPrintLog($errorMsg);
		} else if($this->_logType == CADB_LOG_TYPE_FILE) {
			$this->redirectFileLog($errorMsg);
		} else if($this->_logType == CADB_LOG_TYPE_ALL) {
			if($action != CADB_ERROR_ACTION_AJAX)
				$this->redirectPrintLog($errorMsg);
			$this->redirectFileLog($errorMsg);
		} 
		
		if($action == CADB_ERROR_ACTION_URL){
			$this->last->action = 'redirect';
			if(!$url)
				$url = CADB_COMMON_ERROR_PAGE;
			?>
			<script language="javascript">
				location.href = "<?=$url?>";
			</script>
			<?
		}
		if($action == CADB_ERROR_ACTION_AJAX){
			$this->last->action = 'ajax';
			echo CADB_ERROR_AJAX_MSG;
		}

		return;
	}
}


/** usage ******************
//$logger = Logger::getInstance("", LOG_LEVEL_ALL, LOG_TYPE_ALL, ERROR_LOG_PATH, TRACE_LOG_PATH);
$loggingID = basename(getcwd());
$logger = Logger::getInstance($loggingID);
$logger->error("hihi");

try
{
	throw new Exception("exception !! ", 999);	
} catch(Exception $e) {
	$logger->Error($e);
}
****************************/
?>
