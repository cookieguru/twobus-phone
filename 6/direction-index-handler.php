<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] < 1) {
	echo '<Say voice="alice">That is an invalid selection.</Say>';
	echo '<Say voice="alice">Goodbye</Say>';
	echo '<Hangup/>';
	exit('</Response>');
}

$directions = get_route_stops($_REQUEST['agency'], $_REQUEST['route']);

if(isset($directions->Direction[$_REQUEST['direction'] - 1]->stop[$_REQUEST['Digits']-10])) {
	$stop = (explode('_', $directions->Direction[$_REQUEST['direction'] - 1]->stop[$_REQUEST['Digits']-10]->attributes()->id));
	echo '<Redirect>' . SITE_URL . "1/main-handler.php?agency={$stop[0]}&amp;stop={$stop[1]}</Redirect>";
} else {
	echo "<Say voice=\"alice\">{$_REQUEST['Digits']} is not a valid selection.</Say>";
	echo '<Redirect>' . SITE_URL . "6/direction-handler.php?agency={$_REQUEST['agency']}&amp;route={$_REQUEST['route']}&amp;Digits={$_REQUEST['Digits']}</Redirect>";
}

?>
</Response>