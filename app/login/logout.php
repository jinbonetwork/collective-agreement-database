<?php
namespace CADB\App\login;

\CADB\Lib\importLibrary('auth');

$IV = array(
	'GET' => array(
		'requestURI' => array('string', 'default' => null)
	),
	'POST' => array(
		'requestURI' => array('string', 'default' => null)
	)
);

class logout extends \CADB\Controller {

	public function process() {
		\CADB\Log::accessLog('logout');
		\CADB\Lib\logout();

		if($_GET['requestURI']) {
			\CADB\Lib\RedirectURL(rawurldecode($_GET['requestURI']));
		} else {
			\CADB\Lib\RedirectURL(\CADB\Lib\base_uri());
		}
	}
}
?>
