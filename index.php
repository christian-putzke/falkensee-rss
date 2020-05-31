<?php

	date_default_timezone_set('UTC');
	libxml_use_internal_errors(true);
	 
	$baseUrl = 'https://www.falkensee.de';
	$url = $baseUrl . '/news/index.php?rubrik=1';

	$doc = new DOMDocument();
	$doc->loadHTMLFile($url);

	$links = $doc->getElementsByTagName('a');
	$newsIndex = 0;
	$feed;

	foreach ($links as $link) {
		
		if ($link->nodeValue != "mehr") {
			continue;
		}

		$newsUrl = $baseUrl . $link->getAttribute('href');
		$newsUrlParts = explode('/', $newsUrl);

		$newsDoc = new DOMDocument();
		$newsDoc->loadHTMLFile($newsUrl);

		$newsContent = "";
		$newsDescription = "";
		$newsDate = 0;

		$containers = $newsDoc->getElementsByTagName('div');
		foreach ($containers as $container) {
			$containerClass = $container->getAttribute('class');

			if ($containerClass == "news-date-publicized") {
				$newsDate = strtotime(str_replace(['Falkensee, den ', " "], null, $container->nodeValue));
			}

			if ($containerClass == "newscontent") {
				$newsContentReplace = ["\n","\t","<![CDATA[","]]","https://layout.verwaltungsportal.de/global/interaktiv/buttons/overlay_search_white_v2.png"];
				$newsContent = str_replace($newsContentReplace, "", $newsDoc->saveXML($container));
			}
		}

		$feed[$newsIndex]['id'] = $newsUrlParts[5];
		$feed[$newsIndex]['link'] = $newsUrl;
		$feed[$newsIndex]['title'] = $newsDoc->getElementsByTagName('h1')[0]->nodeValue;
		$feed[$newsIndex]['content'] = $newsContent;
		$feed[$newsIndex]['date'] = $newsDate;

		$newsIndex ++;
	}


	// build the XML RSS2.0 document which will be rendered in the end
	$xml = new XMLWriter();
	$xml->openURI('php://output');
	$xml->setIndent(4);

	$xml->startDocument('1.0', 'UTF-8'); 

		$xml->startElement('rss');
			$xml->writeAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
			$xml->writeAttribute('xmlns:slash', 'http://purl.org/rss/1.0/modules/slash/');
			$xml->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
			$xml->writeAttribute('version', '2.0');

			$xml->startElement("channel");
				$xml->writeElement('title', "Stadt Falkensee News");
				$xml->writeElement('description', "Der Stadt Falkensee RSS News Feed");
				$xml->writeElement('link', "https://www.falkensee.de");
				$xml->writeElement('atom:link', "https://falkensee.graviox.de");
				$xml->writeElement('lastBuildDate', date("D, d M Y H:i:s e", time()));
				$xml->writeElement('language', "de");

				foreach ($feed as $feedItem)
				{
					$xml->startElement("item");
						$xml->writeElement('title', $feedItem["title"]);
						$xml->writeElement('link', $feedItem["link"]);
						$xml->writeElement('pubDate', date("D, d M Y H:i:s e", $feedItem["date"]));
						$xml->writeElement('guid', $feedItem["id"]);
						$xml->startElement("content:encoded");
							$xml->writeCData($feedItem["content"]);
						$xml->endElement();
					$xml->endElement();
				}
			$xml->endElement();

		$xml->endElement();

	$xml->endDocument();

	$xml->flush();
