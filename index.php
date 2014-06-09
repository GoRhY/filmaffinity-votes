<?
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('Europe/Madrid');

include ("settings.php");
include ("codebird.php");
include ("simple_html_dom.php");
include ("functions.php");

\Codebird\Codebird::setConsumerKey($consumer_key, $consumer_secret);
$cb = \Codebird\Codebird::getInstance();
$cb -> setToken($access_token, $access_token_secret);

list($array_id, $array_title, $array_img, $array_link, $array_vote) = get_info($user_id, $language, $ignore, $film_poster_size);

if (!empty($array_id)) {
	foreach ($array_id as $i => $id) {
		$status = sprintf($tweet_string, $array_title[$i], $array_vote[$i], $array_link[$i]);
		if (strlen($array_title[$i]) > 60){
			$title = substr($array_title[$i],0,55)."(...)";
			$status = sprintf($tweet_string, $title, $array_vote[$i], $array_link[$i]);
		}
		if ($film_poster == "no") {
			$params = array('status' => $status);
			$reply = $cb -> statuses_update($params);
		} else {
			$params = array('status' => $status, 'media[]' => file_get_contents($array_img[$i]));
			$reply = $cb -> statuses_updateWithMedia($params);
		}
		echo $status . "<br /><img src=\"$array_img[$i]\" />";
	}
	file_put_contents('last_id.txt', $array_id[0]);
}
?>
