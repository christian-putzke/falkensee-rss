<?php

	date_default_timezone_set('UTC');

	require "vendor/autoload.php";
	use PHPHtmlParser\Dom;
	 
	$url = 'https://www.falkensee.de';

	$dom = new Dom;
	$dom->loadFromUrl($url);

	$content = $dom->find('#content')[0];

	$newsIndex = 0;
	$feed;
	
	foreach($content->find('p.vorschau') as $news)
	{
		$newsUrl = $url . $news->find('a')->href;
		$newsUrlParts = explode('/', $newsUrl);

		$newsDom = new Dom;
		$newsDom->loadFromUrl($newsUrl);

		$newsContent = $newsDom->find('#content');
		$newsDate = str_replace('Falkensee, den ', null, $newsContent->find('.news-date-publicized')->innerHtml);

		$feed[$newsIndex]['id'] = $newsUrlParts[5];
		$feed[$newsIndex]['link'] = $newsUrl;
		$feed[$newsIndex]['title'] = $newsContent->find('h1')->innerHtml;
		$feed[$newsIndex]['description'] = $newsContent->find('.newscontent')->innerHtml;
		$feed[$newsIndex]['date'] = strtotime($newsDate);

		$newsIndex ++;
	}

	$xml = new XMLWriter();
	$xml->openURI('php://output');
	$xml->setIndent(4);

	$xml->startDocument('1.0'); 

		$xml->startElement('rss');
			$xml->writeAttribute('version', '2.0');
			$xml->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

			$xml->writeElement('title', "Stadt Falkensee News");
			$xml->writeElement('link', "https://falkensee.graviox.de");
			$xml->writeElement('description', "Der Stadt Falkensee RSS News Feed");
			$xml->writeElement('language', "de-DE");
			$xml->writeElement('generator', "www.christian-putzke.de");
			$xml->writeElement('lastBuildDate', date("D, d M Y H:i:s e", time()));
			//$xml->writeElement('sy:updatePeriod', "hourly");
			//$xml->writeElement('sy:updateFrequency', "1");

			$xml->startElement("channel");

				foreach ($feed as $feedItem)
				{
					$xml->startElement("item");

						$xml->writeElement('title', $feedItem["title"]);
						$xml->writeElement('link', $feedItem["link"]);
						$xml->writeElement('description', $feedItem["description"]);
						$xml->writeElement('guid', $feedItem["id"]);
						$xml->writeElement('pubDate', date("D, d M Y H:i:s e", $feedItem["date"]));

					$xml->endElement();
				}

			$xml->endElement();

		$xml->endElement();

	$xml->endDocument();

	$xml->flush();