<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<Response>
	<?php
	$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;

	//2 goes to search by stop which is 6 in the main menu
	if($_REQUEST['Digits'] == 2)
		$_REQUEST['Digits'] = 6;

	if(is_file("../{$_REQUEST['Digits']}/main.php")) {
		$no_header = true;
		include("../{$_REQUEST['Digits']}/main.php");
	} else {
		echo "<Say voice=\"alice\">{$_REQUEST['Digits']} is not a valid option</Say>";
		include('main.php');
	}
	?>
</Response>