<?php
$browser = new Browser();
if( $browser->getBrowser() == Browser::BROWSER_IE && $browser->getVersion() <= 9 ) {
	importResource("bootstrap");
} else {
	importResource("react-redux");
	importResource("react-bootstrap");
}
?>
