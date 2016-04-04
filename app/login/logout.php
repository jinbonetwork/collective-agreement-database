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
		logout();

		if($_GET['requestURI']) {
			\CADB\Lib\RedirectURL(rawurldecode($_GET['requestURI']));
		} else {
			\CADB\Lib\RedirectURL(base_uri());
		}
	}
}
?>