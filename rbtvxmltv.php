<?php

$channelname = "Rocket Beans TV";
$chid = "RBTV";
$jsonapiurl = "http://api.rbtv.rodney.io/api/1.0/schedule/schedule_linear.json";
$jsonapiname = "RBTV API";


$jsondata = file_get_contents($jsonapiurl);
$jsonobj = json_decode($jsondata, TRUE);

$imp = new DOMImplementation;

$dtd = $imp->createDocumentType('tv', '', 'xmltv.dtd');

$dom = $imp->createDocument("", "", $dtd);

$dom->version = '1.0';
$dom->encoding = 'ISO-8859-1';

$root = $dom->createElement('tv');

$jsonapi = parse_url($jsonapiurl);
$root->setAttribute("source-info-url", $jsonapi['host']);
$root->setAttribute("source-info-name", $jsonapiname);

$channel = $dom->createElement('channel');
$channel->setAttribute("id", $chid);

$displayname = $dom->createElement('display-name', $channelname);
$channel->appendChild($displayname);

$root->appendChild($channel);

foreach($jsonobj['schedule'] as $schedule){
	$programme = $dom->createElement('programme');
	$programme->setAttribute("start", getdatetime($schedule['timeStart']));
	$programme->setAttribute("stop", getdatetime($schedule['timeEnd']));
	$programme->setAttribute("channel", $chid);

	$title = $dom->createElement("title");
	$title->appendChild($dom->createTextNode(utf8_encode(gettitle($schedule))));
	$programme->appendChild($title);

	$desc = $dom->createElement("desc");
	$desc->appendChild($dom->createTextNode(utf8_encode($schedule['topic'])));
	$programme->appendChild($desc);	

	$root->appendChild($programme);
}

$dom->appendChild($root);

$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
echo $dom->savexml();

function gettitle($schedule) {
	$title = $schedule['title'];
		if ($schedule['type'] != "") {
			$title = $title . " [" . ucwords($schedule['type']) . "]";
	}
	return $title;
}

function getdatetime($datetime) {
	$date = DateTime::createFromFormat(DateTime::ISO8601, $datetime);
	return $date -> format('YmdHis O');
}
?>
