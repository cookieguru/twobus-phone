<Gather action="<?php echo SITE_URL; ?>6/main-handler.php" numDigits="6" timeout="10" finishOnKey="#">
	<Say voice="alice">Enter the route number, followed by the pound key</Say>
</Gather>
<?php
//Return to main menu
include(DOC_ROOT . 'main.php');