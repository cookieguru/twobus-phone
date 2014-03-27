<?php
require_once('../includes/configuration.php');
require_once('../includes/' . DB_IMPL . '.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Response>
<?php
$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;

$db = new DB();
$bookmarks = $db->get('bookmarks', ['agency', 'stop'], ['number' => @$_REQUEST['From']]);

if(empty($bookmarks)) {
	//Return to main menu
	$no_header = true;
	include(DOC_ROOT . 'main.php');
} else {
	if(isset($bookmarks[$_REQUEST['Digits'] - 1])) {
		echo '<Redirect>' . SITE_URL . "1/main-handler.php?agency={$bookmarks[$_REQUEST['Digits'] - 1]->agency}&amp;stop={$bookmarks[$_REQUEST['Digits'] - 1]->stop}</Redirect>";
	} else {
		echo '<Say voice="alice">That is not a valid selection</Say>';
		echo '<Redirect>' . SITE_URL . 'main-handler.php?Digits=3</Redirect>';
	}
}
?>
</Response>