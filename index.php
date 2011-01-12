<?php

/* bit.ly API credentials. Must be valid to use API mode. */
$BITLY_USERNAME = "";
$BITLY_APIKEY = "";

/* End of editable stuff */

if ($_GET["tweet"] != '') {
    $url = renderTwixtPage(stripslashes($_GET["tweet"]));
    apiShortenURL($url, $BITLY_USERNAME, $BITLY_APIKEY);
} else if ($_POST["tweet"] != '') {
    $url = renderTwixtPage(stripslashes($_POST["tweet"]));
    shortenURL($url);
} else {
	echo('<html><head><title>Twixt</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link type="text/css" rel="stylesheet" media="all" href="style.css" /></head><body>');
	echo('	<div id="inner"><div id="message">');
	echo('	<form action="index.php" method="post">');
	echo('	<textarea rows="10" cols="90" name="tweet"></textarea> ');
	echo('	<p align="right"><input type="submit" value="Twixt!"/></p>');
	echo('	<p style="font-size:50%"><a href="http://www.onlydreaming.net/software/twixt">About Twixt</a></p>');
	echo('	</form>');
	echo('	</div></div>');
	echo('</body></html>');
}

function renderTwixtPage($msg) {
    $PRE_MSG = '<html><head><title>Twixt Message</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link type="text/css" rel="stylesheet" media="all" href="style.css" /></head><body><div id="inner"><div id="message"><p>';
	$POST_MSG_1 = '</div><div id="footer">';
	$POST_MSG_2 = '</div></div></body></html>';

	$numchars = strlen($msg);
	$numtweets = ceil($numchars/140);

	$footer = 'This ' . $numchars . '-character message would have taken ' . $numtweets . ' tweets!<br>Your sanity was restored by <a href="http://www.onlydreaming.net/software/twixt">Twixt</a>.';

	$nextfile = getNextNumber() . ".htm";
	$file = fopen($nextfile, 'w');
	fwrite($file, $PRE_MSG);
	fwrite($file, parseLinks(sanitise(substr($msg,0,10000))));
	fwrite($file, $POST_MSG_1);
	fwrite($file, $footer);
	fwrite($file, $POST_MSG_2);
	fclose($file);
    
    return $nextfile;
}

function shortenURL($url) {
    header('Location: http://bit.ly/' . urlencode(substr(curPageURL(), 0, strrpos(curPageURL(),"/")+1) . $url ));
	die();
}

function apiShortenURL($url, $BITLY_USERNAME, $BITLY_APIKEY) {
    header('Location: http://api.bit.ly/v3/shorten?login=' . $BITLY_USERNAME . '&apiKey=' . $BITLY_APIKEY . '&format=txt&longUrl=' . urlencode(substr(curPageURL(), 0, strrpos(curPageURL(),"/")+1) . $url ));
	die();
}

function getNextNumber() {
    $dir = opendir('.');
    $max = 0;
    
    while($file = readdir($dir)) {
        if($file != '.' && $file != '..') {
            if (preg_match("/^\d*/", $file, $matches)) {
                if ($matches[0] > $max) {
                    $max = $matches[0];
                }
            }
        }
    }
    closedir($dir);
    return $max+1;
}

function sanitise($string) {
  $pattern[0] = '/\&/';
  $pattern[1] = '/</';
  $pattern[2] = "/>/";
  $pattern[3] = '/\n/';
  $pattern[4] = '/"/';
  $pattern[5] = "/'/";
  $pattern[6] = "/%/";
  $pattern[7] = '/\(/';
  $pattern[8] = '/\)/';
  $pattern[9] = '/\+/';
  $pattern[10] = '/-/';
  $replacement[0] = '&amp;';
  $replacement[1] = '&lt;';
  $replacement[2] = '&gt;';
  $replacement[3] = '<br>';
  $replacement[4] = '&quot;';
  $replacement[5] = '&#39;';
  $replacement[6] = '&#37;';
  $replacement[7] = '&#40;';
  $replacement[8] = '&#41;';
  $replacement[9] = '&#43;';
  $replacement[10] = '&#45;';
  return preg_replace($pattern, $replacement, $string);
}

// Parses the tweet text, and links up URLs, @names and #tags.
function parseLinks($html) {
	$html = preg_replace('/\\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', "<a href=\"\\0\">[link]</a>", $html);
	$html = preg_replace('/^d\s(\w+)/', 'd <a href="http://www.twitter.com/\1">\1</a>', $html);
	$html = preg_replace('/(^|\s)@(\w+)/', '\1<a href="http://www.twitter.com/\2">@\2</a>', $html);
	$html = preg_replace('/(^|\s)#(\w+)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $html);
	return $html;
}

function curPageURL() {
  $pageURL = 'http';
  if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
  $pageURL .= "://";
  if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } else {
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
  return $pageURL;
}
?>
