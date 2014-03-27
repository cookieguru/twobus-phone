<?php
require_once('includes/configuration.php');
require_once('includes/' . DB_IMPL . '.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] != '2') {
	$no_header = true;
	require('main.php');
	exit('</Response>');
}

echo '<Gather action="' . SITE_URL . 'main-handler.php" numDigits="1" timeout="5">';
$db = new DB();
if($db->insert_ignore('bookmarks', ['number', 'agency', 'stop'], [@$_REQUEST['From'], $_REQUEST['agency'], $_REQUEST['stop']])) {
	echo "<Say voice=\"alice\">{$_REQUEST['stop']} has been saved</Say>";
} else {
	echo "<Say voice=\"alice\">There was a problem saving {$_REQUEST['stop']}</Say>";
}
echo '<Say voice="alice">To access your bookmarked stops, press 3</Say>';
echo '</Gather>';
echo '<Say voice="alice">Goodbye</Say>';
echo '<Hangup/>';
?>
</Response>