<?
function get_info($user_id, $language = "es", $ignore, $film_poster_size = "full") {
	$html = file_get_html("http://www.filmaffinity.com/" . $language . "/userratings.php?user_id=" . $user_id);
	$last_id = @file_get_contents('last_id.txt');

	if ($language == "en") {
		$html_search = 'div[class=movie-card-7 movie-card]';
		$img_search = 'a img';
		$vote_search = 'td[width=50] span[class=wrat]';
	} else {
		$html_search = 'div[class=movie-card movie-card-7]';
		$img_search = 'div[class=mc-poster] img';
		$vote_search = 'div[style=color:#666666;font-size:28px;font-weight:bold;]';
	}

	foreach ($html->find($html_search) as $films) {
		$film_id = $films -> getAttribute("data-movie-id");

		if (!in_array($film_id, $ignore)) {
			$array_id[] = $film_id;
		}

		if ($language == "en") {
			foreach ($films->find('td[valign=top]',2) as $title) {
				if ($title -> plaintext != "") {
					$film_title = trim($title -> plaintext);
					$film_title = str_replace('          ', ' ', $film_title);
					$array_title[] = $film_title;
				}
			}
		} else {
			foreach ($films->find('div[class=mc-title]') as $title) {
				if ($title -> plaintext != "") {
					$film_title = trim($title -> plaintext);
					$film_title = str_replace('           ', '', $film_title);
					$array_title[] = $film_title;
				}
			}
		}
		if (($film_poster_size == "small") || ($film_poster_size == "full")) {
			foreach ($films->find($img_search) as $image) {
				$film_img = $image -> src;
				if ($film_poster_size == "full") $film_img = str_replace("small", "full", $film_img);
				$array_img[] = $film_img;
			}
		}else{
			$html_img = file_get_html("http://www.filmaffinity.com/" . $language . "/film" . $film_id.".html");
			foreach ($html_img->find('div[id=movie-main-image-container] a') as $image) {
				$film_img = $image -> href;
				if ($film_poster_size == "main") $film_img = str_replace("large", "main", $film_img);
				$array_img[] = $film_img;
			}
		}

		$film_link = " http://www.filmaffinity.com/" . $language . "/" . $film_id;
		$array_link[] = $film_link;

	}
	foreach ($html->find($vote_search) as $vote) {
		$film_vote = $vote -> plaintext;
		$film_vote = str_replace(' ', '', $film_vote);
		$array_vote[] = trim($film_vote);
	}

	$key = array_search($last_id, $array_id);
	foreach ($array_id as $i => $value) {
		if ($i >= $key) {
			unset($array_id[$i]);
			unset($array_title[$i]);
			unset($array_img[$i]);
			unset($array_link[$i]);
			unset($array_vote[$i]);
		}
	}

	return array($array_id, $array_title, $array_img, $array_link, $array_vote);
}
?>