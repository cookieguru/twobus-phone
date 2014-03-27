<?php
require_once('includes/configuration.php');
require_once('includes/' . DB_IMPL . '.php');
header('Content-type: text/xml');
if(!isset($no_header)) {
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<Response>';
}
if(!isset($_REQUEST['CallStatus']))
	$_REQUEST['CallStatus'] = 'in-progress';

if(!isset($db))
	$db = new DB();
$db->insert_ignore('phones', ['number', 'city', 'state', 'zip', 'country'], [@$_REQUEST['From'], @$_REQUEST['FromCity'], @$_REQUEST['FromState'], @$_REQUEST['FromZip'], @$_REQUEST['FromCountry']]);

?>
	<Gather action="<?php echo SITE_URL; ?>main-handler.php" numDigits="1" timeout="10">
		<?php if(isset($inject))
			echo $inject;
		?>
		<Say voice="alice">Welcome to Two Bus Puget Sound</Say>
		<Say voice="alice">To hear arrivals by stop number, press 1</Say>
		<Say voice="alice">For help finding your stop number, press 2</Say>
		<Say voice="alice">To access your bookmarked stops, press 3</Say>
		<!--Say voice="alice">To manage your bookmarked stops, press 4</Say-->
		<Say voice="alice">To check your most recent stop, press 5</Say>
		<Say voice="alice">To search for a stop by route number, press 6</Say>
		<Say voice="alice">To hear information on a specific transit vehicle, press 8</Say>
	</Gather>
	<?php if($_REQUEST['CallStatus'] != 'in-progress') { ?>
		<Say voice="alice">Sorry, I didn't get your response.</Say>
		<Redirect>main.php</Redirect>
	<?php } else { ?>
		<Say voice="alice">Goodbye!</Say>
		<Hangup/>
	<?php } ?>
<?php if(!isset($no_header)) {
	echo '</Response>';
}