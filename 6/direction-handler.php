<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] < 1) {
	echo '<Say voice="alice">That is an invalid direction.</Say>';
	echo '<Say voice="alice">Goodbye</Say>';
	echo '<Hangup/>';
	exit('</Response>');
}


$directions = get_route_stops($_REQUEST['agency_route']);

if(!isset($directions->Direction[$_REQUEST['Digits']-1])) {
	echo "<Say voice=\"alice\">{$_REQUEST['Digits']} is not a valid direction.</Say>";
	echo '<Redirect>' . SITE_URL . "6/direction-handler.php?agency_route={$_REQUEST['agency_route']}</Redirect>";
	exit('</Response>');
}

$max_length = strlen(count($directions->Direction[$_REQUEST['Digits']-1]) + 10);
echo '<Gather action="' . SITE_URL . "6/direction-index-handler.php?agency_route={$_REQUEST['agency_route']}&amp;direction={$_REQUEST['Digits']}\" numDigits=\"$max_length\">";
$i = 10;
foreach($directions->Direction[$_REQUEST['Digits']-1] as $stop) {
	echo '<Say voice="alice">For ' . say_stop($stop) . ' press ' . say_digits(str_pad($i, $max_length, '#')) . '</Say>';
	$i++;
}
echo '</Gather>';

echo '<Pause length="3" />';
echo '<Say voice="alice">To look up a different route</Say>';


//Return to previous menu
$no_header = true;
include('main.php');
?>
</Response>