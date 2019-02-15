<?php
header('Content-Type: text/html; charset=UTF-8');
date_default_timezone_set('Europe/Madrid');
include("settings.php");
include("codebird.php");
include("simple_html_dom.php");
include("functions.php");
include("tmdb_v3.php");

//Check for settings.php
$required = array('consumer_key', 'consumer_secret', 'access_token', 'access_token_secret', 'user_id');
$error = false;
foreach($required as $field){
	if (empty($$field)){
		$error = true;
	}
}

//Check for last_id.txt
if (file_get_contents("last_id.txt")==""){
	if (!fopen("last_id.txt", 'w')){
		$error = true;
	}
}
if($error!==true){
	//If proxies file is older than 1hour we get new proxies
	if((date("U",filectime("proxies.txt") <= time() - 3600))||(file_get_contents("proxies.txt")=="")){
		get_proxies();
	}
	
	//Codebird initiation
	\Codebird\Codebird::setConsumerKey($consumer_key, $consumer_secret);
	$cb = \Codebird\Codebird::getInstance();
	$cb->setToken($access_token, $access_token_secret);

	//Parse filmaffinity
	list($array_id, $array_title, $array_img, $array_link, $array_vote) = get_info($user_id, $language, $ignore, $film_poster_size, $apikey);
	$array_id = array_reverse($array_id,true);
	$array_title = array_reverse($array_title,true);
	$array_img = array_reverse($array_img,true);
	$array_link = array_reverse($array_link,true);
	$array_vote = array_reverse($array_vote,true);
	$new_last_id = $first_value = reset($array_id);
	
	//Publish updates
	if (!empty($array_id)) {
		$x = 0;
		foreach ($array_id as $i => $id) {
			if ($x == $max_votes) break;
			$status = sprintf($tweet_string, $array_title[$i], $array_vote[$i], $array_link[$i]);
			if (strlen($array_title[$i]) > 60) {
				$title = substr($array_title[$i], 0, 55) . "(...)";
				$status = sprintf($tweet_string, $title, $array_vote[$i], $array_link[$i]);
			}
			if ($film_poster == "no") {
				$params = array('status' => $status);
				$reply = $cb->statuses_update($params);
			}else{
				if ($array_img[$i] == "") {
					mail($mail, $subject, $status);
					$x++;
				} else {
					$params = array('status' => $status, 'media[]' => $array_img[$i]);
					$reply = $cb -> statuses_updateWithMedia($params);
				}
			}
			if ($reply->httpstatus==200) {
				echo "SUCCESS: ".$status . "<br /><img src=\"$array_img[$i]\" /><br><br>";
				$x++;
			}else{
				echo "FAILED: ".$status . "<br /><img src=\"$array_img[$i]\" /><br><br>";
			}
		}
		if ($x > 0) {
			file_put_contents('last_id.txt', $new_last_id);
		}
	}
}else{
	echo "Por favor rellena el archivo settings.php y comprueba que el archivo last_id.txt tiene permisos de escritura<br><br>Please fill the settings.php file and last_id.txt has write permissions";
}
?>