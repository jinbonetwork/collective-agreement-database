<?php
namespace CADB\App\login;

importLibrary('auth');

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
			RedirectURL(rawurldecode($_GET['requestURI']));
		} else {
			RedirectURL(base_uri());
		}
	}
}
?>
