<Gather action="<?php echo SITE_URL; ?>2/main-handler.php" numDigits="1" timeout="5">
	<Say voice="alice">Your stop number is typically located on the stop post or on the posted schedule. To enter the stop number, press 1.</Say>
	<Say voice="alice">If your stop is missing its posted schedule, or if you are not at the stop. you can search for stops by bus route. To search for a stop by route number, press 2</Say>
</Gather>
<Pause length="5" />
<?php
//Return to main menu
include(DOC_ROOT . 'main.php');