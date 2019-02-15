<?php
header('Content-Type: text/html; charset=UTF-8');

function get_proxies(){
	$url = "http://spys.me/proxy.txt";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	$output = curl_exec($ch);
	curl_close($ch);
	if ($output){
		$lines = explode(PHP_EOL, $output);
		if ($lines){
			$i = 0;
			$top = count($lines);
			foreach ($lines as $line){
				if (($i>3)&&($i<($top-2))){
					$ip_array = explode(" ",$line);
					$ips[] = reset($ip_array);
				}else{
				}
				$i++;
			}
			if (count($ips) > 1){
				file_put_contents("proxies.txt",json_encode($ips));
			}
		}else{
			return false;
		}
	}
}

function file_get_contents_curl($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function curl_get_contents($url) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function get_proxy(){
	$ips = json_decode(file_get_contents("proxies.txt"),true);
	$rand = mt_rand(0,count($ips)-1);
	return $ips[$rand];
}

function get_html($url){
	for ($i=1; $i <= 10; $i++) {
		$proxy = get_proxy();
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_PROXY, $proxy);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("REMOTE_ADDR: $proxy", "HTTP_X_FORWARDED_FOR: $proxy","Cache-Control: no-cache", "HTTP_X_REAL_IP: $proxy"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if (stripos($output,"Filmaffinity")){
			break;
		}
	}
	if ($output){
		return $output;
	}else{
		return false;
	}
}

function get_info($user_id, $language = "es", $ignore, $film_poster_size = "full", $apikey="") {
	$output = get_html("https://www.filmaffinity.com/" . $language . "/userratings.php?user_id=" . $user_id);
	if ($output){
		$html = str_get_html($output);
		$last_id = @file_get_contents('last_id.txt');
		$html_search = 'div[class=user-ratings-movie-item]';
		$img_search = 'div[class=mc-poster] img';
		$vote_search = 'div[class=ur-mr-rat]';
		
		foreach ($html->find($html_search) as $films) {
			$film_id = $films -> find("div[class=movie-card]",0)->getAttribute("data-movie-id");
			if (!in_array($film_id, $ignore)) {
				$array_id[] = $film_id;
			}
			foreach ($films->find('div[class=mc-title]') as $title) {
				if ($title -> plaintext != "") {
					$film_title = trim($title -> plaintext);
					$film_title = preg_replace('!\s+!', ' ', $film_title);
					$array_title[] = $film_title;
				}
			}
			if ($apikey!=""){
				$tit = substr($film_title, 0, -7);
				$year = substr($film_title, -5, -1);
				$tmdb_V3 = new TMDBv3($apikey);
				$searchTitle = $tmdb_V3->searchMovie($tit,$language,$year);
				$sizes = array("small"=>"w100","main"=>"w500","large"=>"w800","full"=>"original");
				$film_poster_size = $sizes[$film_poster_size];				
				$film_img = "https://image.tmdb.org/t/p/w".$film_poster_size."/".$searchTitle["results"][0]["poster_path"];
				if ($searchTitle["results"][0]["poster_path"] == "") $film_img = "";
				$array_img[] = $film_img;
			}else{
				if (($film_poster_size == "small") || ($film_poster_size == "full")) {
					foreach ($films->find($img_search) as $image) {
						$film_img = $image -> src;
						if ($film_poster_size == "full")
							$film_img = str_replace("small", "full", $film_img);
						$array_img[] = $film_img;
					}
				}else{
					$html_img = file_get_html("http://www.filmaffinity.com/" . $language . "/film" . $film_id . ".html");
					foreach ($html_img->find('div[id=movie-main-image-container] a') as $image) {
						$film_img = $image -> href;
						if ($film_poster_size == "main")
							$film_img = str_replace("large", "main", $film_img);
						$array_img[] = $film_img;
					}
				}
			}			
			$film_link = " http://www.filmaffinity.com/" . $language . "/film" . $film_id . ".html";
			$array_link[] = $film_link;
		}

		foreach ($html->find($vote_search) as $vote) {
			$film_vote = $vote -> plaintext;
			$film_vote = str_replace(' ', '', $film_vote);
			$array_vote[] = trim($film_vote);
		}

		$key = array_search($last_id, $array_id);
		if ($key){
			foreach ($array_id as $i => $value) {
				if ($i >= $key) {
					unset($array_id[$i]);
					unset($array_title[$i]);
					unset($array_img[$i]);
					unset($array_link[$i]);
					unset($array_vote[$i]);
				}
			}
		}
		return array($array_id, $array_title, $array_img, $array_link, $array_vote);
	}else{
		return false;
	}
}
?>