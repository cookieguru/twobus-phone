<?php
require_once('includes/' . DB_IMPL . '.php');

if(!isset($db))
	$db = new DB();

$recent = $db->get_one('activity', ['agency', 'stop'], ['number' => @$_REQUEST['From']], ['time' => 'DESC']);
if(empty($recent)) {
	echo '<Say voice="alice">You have no previous stop to check</Say>';
} else {
	echo '<Redirect>' . SITE_URL . "1/main-handler.php?agency={$recent->agency}&amp;stop={$recent->stop}</Redirect>";
	exit('</Response>');
}

//Return to main menu
$no_header = true;
include(DOC_ROOT . 'main.php');