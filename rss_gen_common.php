<?php

$RSSProperties;

Start();

class RSSItem
{
	public $Title;
	public $Link;
	public $Description;
	public $Time;
}

function Start()
{
	global $RSSProperties, $Language, $RootPath;
	
	$RSSProperties = array("Title", "Link", "Description", "Time");
	
	$mainUrl = "https://customsolvers.com/" . $Language . "/";
	$path = $RootPath . $Language . "/" . "rss/"; 
	$level = 1;
	$mainSpace = AddIndent($level);

	$rss = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . PHP_EOL;
	$rss .= "<rss xmlns:atom=\"http://www.w3.org/2005/Atom\" version=\"2.0\">" . PHP_EOL;
	$rss .= $mainSpace . "<channel>" . PHP_EOL;

	$level++;
	$rss .= GetChannelDescription($level, $mainUrl . "rss/", $Language);	
	$rss .= GetRawData($level, $mainUrl, $path . "inputs.txt");
	$rss .= $mainSpace . "</channel>" . PHP_EOL . "</rss>";

	file_put_contents($path . "rss.xml", $rss);
}

function GetRawData($level, $mainUrl, $filePath)
{
	$output = "";
	
	$file = fopen($filePath, "r");
	if ($file) 
	{
		global $RSSProperties;
		$i_max = count($RSSProperties) - 1;	
		$item = new RSSItem();
		$i = -1;
		
		while (($line = fgets($file)) !== false) 
		{
			$line2 = trim($line);
			$length = strlen($line2);
			if ($length < 1 || ($length >= 3 && substr($line2, 0, 3) == "---"))
			{
				continue;
			}
			
			$i++;
			if ($i > $i_max)
			{
				$output .= PrintItem($item, $level);
				$item = new RSSItem();
				$i = 0;
			}

			$property = $RSSProperties[$i];
			$item->$property = $line2;
		}
		
		fclose($file);
		
		if (!is_null($item->Title)) $output .= PrintItem($item, $level);
	}
	
	return $output;
}

function PrintItem($item, $level)
{
	$space = AddIndent($level);
	$output = $space . GetNodeInternal("item", true, true);	

	$level++;
	$output .= GetNode($level, $item->Title, "title");
	$output .= GetNode($level, $item->Link, "link");
	$output .= GetGuid($level, $item->Link);
	$output .= GetNode($level, $item->Description, "description");	
	$output .= GetNode($level, $item->Time, "pubDate");	

	return $output . $space . GetNodeInternal("item", false);
}

function GetGuid($level, $url)
{
	$output = AddIndent($level) . GetNodeInternal("guid isPermaLink=\"false\"");
	$output .= $url;	
	$output .= GetNodeInternal("guid", false);	
	
	return $output;
}

function GetChannelDescription($level, $url, $language)
{
	$output = GetNode($level, GetInfo("channel_title", $language), "title");
	$output .= AddIndent($level) . "<atom:link rel=\"self\" href=\"" . $url . "\" type=\"application/rss+xml\"/>" . PHP_EOL;
	$output .= GetNode($level, $url, "link");
	$output .= GetNode($level, GetInfo("channel_desc", $language), "description");
	
	return $output;
}

function GetNode($level, $contents, $name)
{
	if ($name == "description") $contents = "<![CDATA[" . $contents . "]]>";
	
	return
	(
		AddIndent($level) . GetNodeInternal($name) . $contents . GetNodeInternal($name, false)
	);
}

function GetNodeInternal($name, $isOpenning = true, $newLine = false)
{
	return "<" . ($isOpenning ? "" : "/") . $name . ">" . 
	(
		!$isOpenning || $newLine ? PHP_EOL : ""
	);
}

function AddIndent($level)
{
	$output = "";
	$spaces = $level * 3;
	$count = 0;
	while ($count < $spaces)
	{
		$count++;
		$output .= " ";
	}
	
	return $output;
}

function GetInfo($id, $language)
{
	$output = "";
	
	if ($id == "channel_title")
	{
		$output = ($language == "en" ? "RSS feed of Custom Solvers 2.0" : "Fuente RSS de Custom Solvers 2.0");
	}
	else if ($id == "channel_desc")
	{
		$output =
		(
			$language == "en" ?
			"Relevant news about my public online activity like customsolvers.com/varocarbas.com or open source projects." :
			"Noticias relevantes acerca de mi actividad pública online como customsolvers.com/varocarbas.com o proyectos de código abierto."
		);
	}
	
	return $output;
}

?>