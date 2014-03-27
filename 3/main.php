<?php
require_once('includes/' . DB_IMPL . '.php');

$db = new DB();
$bookmarks = $db->get('bookmarks', ['agency', 'stop'], ['number' => @$_REQUEST['From']]);

//Preen invalid entries first
$processed_bookmarks = [];
if(!empty($bookmarks)) {
	foreach($bookmarks as &$bookmark) {
		$stops = get_stop($bookmark->stop);
		$stop = $stops->xpath("agency[@id='{$bookmark->agency}']");
		if(empty($stop)) {
			$db->delete('bookmarks', ['number' => @$_REQUEST['From'], 'agency' => $bookmark->agency, 'stop' => $bookmark->stop]);
		} else {
			$processed_bookmarks[] = $stop[0]->name;
		}
	}
}

if(empty($processed_bookmarks)) {
	echo '<Say voice="alice">You do not have any bookmarked stops</Say>';
	echo '<Pause length="1" />';
} else {
	$max_digits = strlen(count($processed_bookmarks));
	echo '<Gather action="' . SITE_URL . "3/main-handler.php\" numDigits=\"$max_digits\" timeout=\"5\">";
	$count = 1;
	foreach($processed_bookmarks as $bookmark) {
		echo '<Say voice="alice">For ' . say_stop($bookmark) . ' press ' . say_digits(str_pad($count++, $max_digits, 0, STR_PAD_LEFT)) . '</Say>';
	}
	echo '</Gather>';
}

//Return to main menu
include(DOC_ROOT . 'main.php');