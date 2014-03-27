<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] < 1) {
	echo '<Say voice="alice">That is an invalid route number.</Say>';
	echo '<Say voice="alice">Goodbye</Say>';
	echo '<Hangup/>';
	exit('</Response>');
}

if(isset($_REQUEST['route'])) {
	$routes = get_route($_REQUEST['route']);
	$temp = new StdClass();
	$temp->agency = $routes->agency[$_REQUEST['Digits'] - 1];
	$routes = $temp;
} else {
	$routes = get_route($_REQUEST['Digits']);
}

if(count($routes) == 0) {
	echo '<Say voice="alice">That is not a valid route number</Say>';
	echo '<Pause length="1" />';
} elseif(count($routes) > 1) {
	echo '<Gather action="' . SITE_URL . "6/main-handler.php?route={$_REQUEST['Digits']}\">";
	echo '<Say voice="alice">There are multiple routes for ' . say_route($_REQUEST['Digits']) . '</Say>';
	for($i = 0; $i < count($routes); $i++) {
		echo '<Say voice="alice">For ' . say_agency($routes->agency[$i]->name) . ' press ' . ($i+1) . '</Say>';
	}
	echo '</Gather>';
	echo '<Pause length="5" />';
	echo '<Say voice="alice">To look up a different route</Say>';
} else {
	$directions = get_route_stops((string)$routes->agency[0]->attributes()->id, $_REQUEST['route']);
	echo "<Say voice=\"alice\">Route {$_REQUEST['route']} {$directions->attributes()->name}</Say>";

	if(!isset($directions->Direction) || empty($directions->Direction)) {
		echo '<Say voice="alice">Does not have enough information.  Please use the web interface</Say>';
	} else {
		echo '<Gather action="' . SITE_URL . "6/direction-handler.php?agency={$routes->agency[0]->attributes()->id}&amp;route={$_REQUEST['route']}\" numDigits=\"" . strlen(count($directions->Direction)) . '">';
		for($i = 0; $i < count($directions->Direction); $i++) {
			$destination = trim(say_headsign($directions->Direction[$i]->attributes()->name));
			echo '<Say voice="alice">For ' . (strtolower($destination) == 'inbound' && strtolower($destination) == 'outbound' ? NULL : 'travel to ') . "$destination press " . ($i+1) . '</Say>';
		}
		echo '</Gather>';
	}
	echo '<Say voice="alice">To look up a different route</Say>';
}


//Return to previous menu
$no_header = true;
include('main.php');
?>
</Response>