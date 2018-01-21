<?php

try
{
	if (file_exists('rss.xml'))
	{
		Header('Content-type: text/xml');
		$dom = new DOMDocument('1.0');
		$dom->load('rss.xml');
		echo $dom->saveXML();	
	}
}
catch (Exception $e){ }

?>