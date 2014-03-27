<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
require_once('../includes/' . DB_IMPL . '.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<Response>';

$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
if($_REQUEST['Digits'] < 1 && !isset($_REQUEST['agency'])) {
	echo '<Say voice="alice">That is an invalid route number.</Say>';
	echo '<Say voice="alice">Goodbye</Say>';
	echo '<Hangup/>';
	exit('</Response>');
}

if(isset($_REQUEST['stop'])) {
	$stops = get_stop($_REQUEST['stop']);
	$temp = new StdClass();
	if(isset($_REQUEST['agency'])) {
		$temp->agency = $stops->xpath("agency[@id='{$_REQUEST['agency']}']");
		$_REQUEST['Digits'] = $_GET['stop'];
	} else {
		if(count($stops) < $_REQUEST['Digits'] - 1 || $_REQUEST['Digits'] == 0) {
			echo '<Say voice="alice">That is an invalid selection</Say>';
			echo '<Redirect>' . "1/main-handler.php?Digits={$_REQUEST['stop']}</Redirect>";
			exit('</Response>');
		}
		$temp->agency = $stops->agency[$_REQUEST['Digits'] - 1];
	}
	$stops = $temp;
	$do_not_say_stop = true;
} else {
	$stops = get_stop($_REQUEST['Digits']);
}

if(count($stops) == 0) {
	echo '<Say voice="alice">That is not a valid stop number</Say>';
	echo '<Pause length="1" />';
} elseif(count($stops) > 1) {
	echo '<Gather action="' . SITE_URL . "1/main-handler.php?stop={$_REQUEST['Digits']}\">";
	echo '<Say voice="alice">There are multiple stops for ' . say_digits($_REQUEST['Digits']) . '</Say>';
	for($i = 0; $i < count($stops); $i++) {
		echo '<Say voice="alice">For ' . say_stop($stops->agency[$i]->name) . ' press ' . ($i+1) . '</Say>';
	}
	echo '</Gather>';
	echo '<Pause length="5" />';
	echo '<Say voice="alice">To look up a different stop</Say>';
} else {
	if(!isset($do_not_say_stop))
		echo '<Say voice="alice">' . say_stop($stops->agency[0]->name) . '</Say>';

	$agency_id = (string)$stops->agency[0]->attributes()->id;
	$stop_number = isset($_REQUEST['stop']) ? $_REQUEST['stop'] : $_REQUEST['Digits'];
	$routes = get_stop_arrivals($agency_id, $stop_number);
	if(empty($routes)) {
		echo '<Say voice="alice">There are no arrivals in the next ' . FUTURE_MINUTES . ' minutes</Say>';
	} else {
		foreach($routes as $route) {
			echo '<Say voice="alice">Route ' . say_route($route['route_name']) . ' to ' . say_headsign($route['headsign']) . ' ';

			$arrival_text = [];
			foreach($route['arrivals'] as $arrival) {
				$text = '';
				if($arrival['predicted']) {
					$text .= $arrival['prediction'] < 0 ? ' departed ' : ' is arriving ';
				} else {
					$text .= $arrival['prediction'] < 0 ? ' was scheduled to depart ' : ' is scheduled to arrive ';
				}
				$text .= $arrival['prediction'] == 0 ? 'now' : ($arrival['prediction'] > 0 ? 'in ' : NULL) . say_seconds($arrival['prediction']);
				$text .= $arrival['prediction'] < 0 ? ' ago ' : NULL;
				$arrival_text[] = $text;
			}
			echo implode(', and ', $arrival_text);

			echo '</Say>';
		}
		echo '<Pause length="1" />';
	}
	echo '<Gather action="' . SITE_URL . "add-bookmark.php?agency={$agency_id}&amp;stop={$stop_number}\">";
	echo '<Say voice="alice">To bookmark this stop, press 2</Say>';
	echo '<Say voice="alice">To return to the main menu, press star</Say>';
	echo '<Pause length="1" />';
	echo '</Gather>';
	echo '<Say voice="alice">To look up a different stop</Say>';

	if(!isset($db))
		$db = new DB();
	$db->insert('activity', ['number', 'sid', 'uri', 'agency', 'stop'], [@$_REQUEST['From'], @$_REQUEST['CallSid'], $_SERVER['REQUEST_URI'], $agency_id, $stop_number]);
}

//Return to previous menu
$no_header = true;
include('main.php');
?>
</Response>