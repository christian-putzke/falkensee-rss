<?php

	date_default_timezone_set('UTC');

	// example of how to use basic selector to retrieve HTML contents
	include 'simple_html_dom.php';
	 
	// get DOM from URL or file
	$url	= "http://www.falkensee.de";
	$html	= file_get_html($url . '/news/');
	$feed	= array();

	foreach($html->find('div#content') as $content)
	{
		$dateIndex = 0;
	    foreach($content->find('h5') as $date)
	    {
		 	$feed[$dateIndex]["date"] = strtotime($date->innertext);
	        $dateIndex ++;
	    }

	    $ulIndex = 0;
	    foreach($content->find('ul') as $ul)
		{
			$aIndex = 0;
			foreach ($ul->find('a') as $a)
			{
				$parts = explode('/', $a->href);

				$feed[$ulIndex]["links"][$aIndex]["id"]				= $parts[3];
				$feed[$ulIndex]["links"][$aIndex]["link"]			= $url . $a->href;
				$feed[$ulIndex]["links"][$aIndex]["title"]			= $a->innertext;
				$feed[$ulIndex]["links"][$aIndex]["description"]	= $a->innertext;

				//$descriptionHtml = file_get_html($feed[$ulIndex]["links"][$aIndex]["link"]);
				//foreach($descriptionHtml->find('div#content') as $descriptionContent)
				//{
				//	foreach ($descriptionContent->find('div') as $descriptionContentDiv)
				//	{
				//		if($descriptionContentDiv->style == 'text-align: left;' || $descriptionContentDiv->id == 'newsImage')
				//		{
				//			$feed[$ulIndex]["links"][$aIndex]["description"] .= $descriptionContent->innertext;
				//		}
				//	}
				//}

				$aIndex ++;
			}
			$ulIndex ++;
		}
	}

	$xml = new XMLWriter();
	$xml->openURI('php://output');
	$xml->setIndent(4);

	$xml->startDocument('1.0'); 

		$xml->startElement('rss');
			$xml->writeAttribute('version', '2.0');
			$xml->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

			$xml->writeElement('title', "Stadt Falkensee News");
			$xml->writeElement('link', "http://falkensee.graviox.de");
			$xml->writeElement('description', "Der Stadt Falkensee RSS News Feed");
			$xml->writeElement('language', "de-DE");
			$xml->writeElement('generator', "www.christian-putzke.de");
			$xml->writeElement('lastBuildDate', date("D, d M Y H:i:s e", time()));
			//$xml->writeElement('sy:updatePeriod', "hourly");
			//$xml->writeElement('sy:updateFrequency', "1");

			$xml->startElement("channel");

				foreach ($feed as $feedDate)
				{
					foreach ($feedDate["links"] as $feedItem)
					{
						$xml->startElement("item");

							$xml->writeElement('title', $feedItem["title"]);
							$xml->writeElement('link', $feedItem["link"]);
							$xml->writeElement('description', $feedItem["description"]);
							$xml->writeElement('guid', $feedItem["id"]);
							$xml->writeElement('pubDate', date("D, d M Y H:i:s e", $feedDate["date"]));

						$xml->endElement();
					}
				}

			$xml->endElement();

		$xml->endElement();

	$xml->endDocument();

	$xml->flush();