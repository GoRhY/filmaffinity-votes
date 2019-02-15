<?php
header('Content-Type: text/html; charset=UTF-8');

//LANGUAGE (es/en)
$language = 'es';

//TWEET WITH FILM POSTER (yes/no -> will use twitter cards)
$film_poster = 'yes';

//FILM POSTER SIZE (small=49×71px/full=100x144px/main/large) (main and large have variable size and take more steps/time to get the image)
$film_poster_size = 'full';

//TWITTER CREDENTIALS
$consumer_key = '';
$consumer_secret = '';
$access_token = '';
$access_token_secret = '';

//FILMAFFINITY ID
$user_id = ''; //Log in filmaffinity.com and get it from your profile URL: https://www.filmaffinity.com/es/userratings.php?user_id=XXXXX

//FILMS ID TO IGNORE, SEPARATED BY COMMA
$ignore = array('');

//TWEET STRING
$tweet_string = 'He votado %s con un %d - %s #FilmAffinity'; //Film name + vote + URL

//THEMOVIEDB APIKEY
$apikey = ""; //Get yours at: https://www.themoviedb.org/account/signup If no apikey is provided, the poster image will be downloaded from FA

//MAX VOTES PER EXECUTION
$max_votes = 1;

//MAIL FOR GETTING FAILURES
$mail = "";
$subject = "Voto pendiente FA";
?>