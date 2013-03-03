<?php
/** Twixt v1.1 - 3rd March 2013
    a pastebin for Twitter
    by Ian Renton
    Freely released into the public domain without licence.
    No warranty, etc etc.
    Free server at http://twixt.successwhale.com
    Code at https://github.com/tsuki/twixt
    Homepage at http://software.ianrenton.com/twixt
    Share and enjoy. */

// If we have a GET, we're being used via the API (http://blah/?tweet=xyz)
if ($_GET["tweet"] != '') {
    $url = renderTwixtPage(stripslashes($_GET["tweet"]));
    apiShortenURL($url);
}
// If we have a POST, someone used the web page frontend
else if ($_POST["tweet"] != '') {
    $url = renderTwixtPage(stripslashes($_POST["tweet"]));
    shortenURL($url);
}
// If we have neither, render the web page frontent
else {
	echo('<html><head><title>Twixt</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link type="text/css" rel="stylesheet" media="all" href="style.css" /></head><body>');
	echo('	<div id="inner"><div id="message">');
	echo('	<form action="index.php" method="post">');
	echo('	<textarea rows="10" cols="90" name="tweet"></textarea> ');
	echo('	<div id="button"><input type="submit" value="Twixt!"/></div>');
	echo('	</form>');
	echo('	</div><div id="footer"><a href="http://software.ianrenton.com/twixt">About Twixt</a></div>');
	echo('	</div>');
	echo('</body></html>');
}

// Render a Twixted message for saving to a file
function renderTwixtPage($msg) {
    $PRE_MSG = '<html><head><title>Twixt Message</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><link type="text/css" rel="stylesheet" media="all" href="style.css" /></head><body><div id="inner"><div id="message"><p>';
	$POST_MSG_1 = '</div><div id="footer">';
	$POST_MSG_2 = '</div></div></body></html>';

	$numchars = strlen($msg);
	$numtweets = ceil($numchars/140);

	$footer = 'This ' . $numchars . '-character message would have taken ' . $numtweets . ' tweets!<br>Your sanity was restored by <a href="http://software.ianrenton.com/twixt">Twixt</a>.';

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

// Shorten with is.gd, returning the web page that lets the user copy-paste the result
function shortenURL($url) {
    header('Location: http://is.gd/create.php?url=' . urlencode(substr(curPageURL(), 0, strrpos(curPageURL(),"/")+1)) . $url );
	die();
}

// Shorten with is.gd, returning just the URL on its own to push to a service using Twixt's API
function apiShortenURL($url) {
    header('Location: http://is.gd/create.php?format=simple&url=' . urlencode(substr(curPageURL(), 0, strrpos(curPageURL(),"/")+1) . $url ));
	die();
}

// Gets the next available file number. This is pretty horrible and collisions are quite possible
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
	$html = preg_replace('/(^|\W)@(\w+)/', '\1<a href="http://www.twitter.com/\2">@\2</a>', $html);
	$html = preg_replace('/(^|[^\&\w])#(\w+)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $html);
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
