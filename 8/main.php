<?php
$agencies = get_agency_data();
?>
<Gather action="<?php echo SITE_URL; ?>8/main-handler.php" numDigits="<?php echo strlen(count($agencies)); ?>" timeout="10" finishOnKey="#">
	<Say voice="alice">Select the transit agency:</Say>
	<?php
	for($i = 0; $i < count($agencies->agency); $i++) {
		echo "<Say voice=\"alice\">For {$agencies->agency[$i]->name}, press " . ($i+1) . '</Say>';
	}
	?>
</Gather>
<?php
//Return to main menu
include(DOC_ROOT . 'main.php');