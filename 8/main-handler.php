<?php
require_once('../includes/configuration.php');
require_once('../includes/functions.php');
header('Content-type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';

$agencies = get_agency_data();
$_REQUEST['Digits'] = isset($_REQUEST['Digits']) ? (int)$_REQUEST['Digits'] : -1;
?>
<Response>
	<?php if(!isset($agencies->agency[$_REQUEST['Digits'] -1])) {
		echo '<Say voice="alice">That is not a valid selection</Say>';
		$no_header = true;
		include('main.php');
	} else {
		?>
		<Gather action="<?php echo SITE_URL; ?>8/agency-handler.php?agency=<?php echo $agencies->agency[$_REQUEST['Digits'] -1]->attributes()->id; ?>" timeout="5" finishOnKey="#">
			<Say voice="alice">Enter the vehicle number, followed by the pound key</Say>
		</Gather>
	<?php } ?>
</Response>