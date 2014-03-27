<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
require_once('../includes/' . DB_IMPL . '.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] < 1) {
	echo '<Say voice="alice">That is an invalid vehicle number</Say>';
	echo '<Say voice="alice">Goodbye</Say>';
	echo '<Hangup/>';
	exit('</Response>');
}

$info = get_vehicle_info($_REQUEST['agency'], $_REQUEST['Digits']);

if($info['code'] == 200) {
	echo '<Say voice="alice">Route ' . say_route($info['route_name']) . ' to ' . say_headsign($info['trip']->tripHeadsign) . ' is ' . say_lateness($info['status']->scheduleDeviation);
	if(isset($info['next_stop_name'])) {
		echo ' and will arrive at ' . say_stop($info['next_stop_name']) . ' in ' . say_seconds($info['next_stop_time']);
	} elseif(isset($info['closest_stop_name'])) {
		if($info['closest_stop_time'] == 0) {
			echo ' and is at ' . say_stop($info['closest_stop_name']);
		} elseif($info['closest_stop_time'] < 0 ) {
			echo ' and passed ' . say_stop($info['closest_stop_name']) . ' ' . say_seconds($info['closest_stop_time']) . ' ago';
		} else {
			echo ' and will arrive at ' . say_stop($info['closest_stop_name']) . ' in ' . say_seconds($info['closest_stop_time']);
		}
	}
	echo '</Say>';
} else {
	echo '<Say voice="alice">No information is available for ' . say_digits($_REQUEST['Digits']) . ' right now.</Say>';
}

if(!isset($db))
	$db = new DB();
$db->insert('activity', ['number', 'sid', 'uri', 'agency', 'vehicle'], [@$_REQUEST['From'], @$_REQUEST['CallSid'], $_SERVER['REQUEST_URI'], $_REQUEST['agency'], $_REQUEST['Digits']]);

$inject  = '<Say voice="alice">To look up another vehicle, press 8</Say>';
$inject .= '<Pause length="3" />';

//Return to previous menu
$no_header = true;
include(DOC_ROOT . 'main.php');
?>
</Response>