<?php
/*
	NationBuilder API Testing Program
	nicole@richiroutreach.com
	01/27/2015
*/

define('ROOT', getcwd());

// OAuth 2 Library
require_once ROOT . '/OAuth2/Client.php';
require_once ROOT . '/OAuth2/GrantType/IGrantType.php';
require_once ROOT . '/OAuth2/GrantType/AuthorizationCode.php';

// Client ID and Secret from Nation Builder
const CLIENT_ID	= 'ID';
const CLIENT_SECRET	= 'SECRET';

// Constants we need to talk to Nation Builder
const REQUEST_ENDPOINT		= "https://slug.nationbuilder.com/api/v1";

// Start a new OAuth2 Client
$client = new OAuth2\Client(CLIENT_ID, CLIENT_SECRET);

// Parse out the token from the response 
$token = "TOKEN";

// Set our token type (ACCESS_TOKEN_BEARER)
$client->setAccessTokenType(1);

// Set our token
$client->setAccessToken($token);


// Set up headers
$header = array(
	'Authorization' => $token,
	'Content-Type' => 'application/json', 
	'Accept' => 'application/json'
	);

$succeeded = 0;

// Create 50 blog posts
for($i = 1; $i <= 50; $i++) {
	// Generate a random number between 0 and 100
	$rand = mt_rand(0, 100);

	// Get content from lorem ipsum depending on what $rand returned
	if($rand < 33) {
		$flip = file_get_contents('http://loripsum.net/api/5/medium/headers/ol/prude');
		$after = file_get_contents('http://loripsum.net/api/5/medium/headers/decorate/prude');
	} else if($rand < 66) {
		$flip = file_get_contents('http://loripsum.net/api/5/medium/headers/bq/decorate/prude');
		$after = file_get_contents('http://loripsum.net/api/5/medium/headers/link/prude');
	} else {
		$flip = file_get_contents('http://loripsum.net/api/5/medium/headers/dl/prude');
		$after = file_get_contents('http://loripsum.net/api/5/medium/headers/link/decorate/prude');
	}

	// Get today's date in unix format
	$end = date('U');

	// Get last years date in unix format
	$start =  date('U', (time() - (52 * 7 * 24 * 60 * 60)));

	// Get a random number between today and last year in unix time stamps
	$int = mt_rand($start, $end);

	// Convert the date to a format NB understands
	$date = date("Y-m-d H:i:s",$int);

	// Generate a random number between 1 and 7
	$rand = mt_rand(1, 7);	

	// Set up some tags
	$tags = array(
		1 => "The North",
		2 => "Kootenays",
		3 => "Okanagan",
		4 => "North Island",
		5 => "South Island",
		6 => "North Shore and Sunshine Coast",
		7 => "Greater Vancouver and Fraser Valley",
		);

	// Get some header text
	$title = file_get_contents('http://loripsum.net/api/1/short/plaintext/prude/headers');

	// Lorem ipsum gives us a few lines, we only want the first one so explode on linebreak
	$title = explode( "\n", $title);

	// Set our array to send to NB
	$params = array(
		"blog_post" => array(
			"name" => $title[0],
			"slug" => slugify($title[0]),
			"status" => "published",
			"content_before_flip" => $flip,
			"content_after_flip" => $after,
			"tags" => $tags[$rand],
			"published_at" => $date,
			"author_id" => 8,
			),
		);

	// Send our request to NB
	$response = $client->fetch(REQUEST_ENDPOINT . "/sites/test/pages/blogs/BLOG ID/posts", json_encode($params), "POST", $header);

	// See if blog post creation was successful
	if($response['code'] == 200) {
		echo "Completed #" . $i;
		$succeeded++;
	} else {
		echo "Failed with:<br><pre>"; print_r($params); echo "</pre>";
	}
}

echo "<h3>Completed: " . $succeeded . " posts</h3>";

/*
$done = false;

while(!$done) {
	$response = $client->fetch(REQUEST_ENDPOINT . "/sites/test/pages/blogs/7/posts");	

	if($response['result']['total'] == 0) {
		$done = true;
	}

	foreach($response['result']['results'] as $post) {
		$response = $client->fetch(REQUEST_ENDPOINT . "/sites/test/pages/blogs/7/posts/" . $post['id'], NULL, 'DELETE');	
	}
}

*/
function slugify($text) { 
	// replace non letter or digits by -
	$text = preg_replace('~[^\\pL\d]+~u', '-', $text);

	// trim
	$text = trim($text, '-');

	// transliterate
	$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

	// lowercase
	$text = strtolower($text);

	// remove unwanted characters
	$text = preg_replace('~[^-\w]+~', '', $text);

	if (empty($text)) {
		return 'n-a';
	}

	return $text;
}
