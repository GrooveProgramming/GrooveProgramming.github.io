<?php

	// Copyright (C) 2014-2019 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Set feed title
	if (isset($_GET['title']) and !empty($_GET['title'])) {
		$title = $_GET['title'];
	} else {
		$title = $domain . ': ' . basename($dir);
	}

	// Read directory contents if conditions met
	if (file_exists($dir) and !empty($_GET['rss'])) {
		$files = array();
		$ac = 0;

		// Read comment files into array; convert date into UNIX timestamp
		foreach (glob($dir . '/*.xml', GLOB_NOSORT) as $file) {
			$files[$ac] = simplexml_load_file($file);
			$files[$ac]['date'] = strtotime(str_replace(array('- ', 'am', 'pm'), array('', ' AM PST', ' PM PST'), $files[$ac]->date));
			$files[$ac]['file'] = $file;

			if (!preg_match('/-/', basename($file, '.xml'))) {
				$cmt_count++;
			}

			$total_count++;
			$ac++;
		}

		// Sort by comment creation date
		usort($files, function ($a, $b) {
			return ($b['date'] - $a['date']);
		});

		foreach ($files as $rss_cmt) {
			static $rss_feed = '';

			// Set feed title
			if (!empty($_GET['title'])) {
				$title = $_GET['title'];
			} else {
				$title = str_replace('www.', '', $domain) . ': ' . basename($dir);
			}

			// Error handling
			if ($rss_cmt !== false) {
				$data_search = array('&', '<br>\n', '\n', '\r', '    ');
				$data_replace = array('&amp;', ' ', ' ', ' ', ' ');

				$permalink = 'c' . str_replace('-', 'r', basename($rss_cmt['file'], '.xml'));
				$rss_feed .= "\t\t" . '<item>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<title>' . str_replace('@identica', '', htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->name)))) . ' : ';
				$rss_feed .= ((strlen($rss_cmt->body) > 40) ? htmlspecialchars(strip_tags(html_entity_decode(substr(str_replace(array('\n', '\r'), ' ', $rss_cmt->body), 0, 40)))) . '...' : str_replace(array('\n', '\r'), ' ', htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->body))))) . '</title>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<nickname>' . str_replace('@identica', '', htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->name)))) . '</nickname>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<description>' . str_replace(array('\n', '\r'), '', htmlspecialchars(strip_tags($rss_cmt->body, '<br><a><b><i><u><s><blockquote><img>'))) . '</description>' . PHP_EOL;

				// Add avatar URLs to feed
				$rss_avatar = get_user_avatar((!empty($rss_cmt->email)) ? md5(strtolower(trim(encrypt($rss_cmt->email)))) : '');

				$rss_feed .= "\t\t\t" . '<avatar>' . $rss_avatar . '</avatar>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<likes>' . $rss_cmt['likes'] . '</likes>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<pubDate>' . date('D, d M Y H:i:s O', strtotime(str_replace(' - ', ' ', $rss_cmt->date))) . '</pubDate>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<guid>' . $_GET['rss'] . '#' . $permalink . '</guid>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<link>' . $_GET['rss'] . '#' . $permalink . '</link>' . PHP_EOL;
				$rss_feed .= "\t\t" . '</item>' . PHP_EOL;
			} else {
				$total_count--;
			}
		}
	} else {
		$rss_feed = "\t\t" . '<item>' . PHP_EOL;
		$rss_feed .= "\t\t\t" . '<title>Error</title>' . PHP_EOL;
		$rss_feed .= "\t\t\t" . '<description>Please choose a comment thread via page URL.</description>' . PHP_EOL;
		$rss_feed .= "\t\t" . '</item>' . PHP_EOL;
	}

	header('Content-Type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . PHP_EOL;
	echo "\t" . '<channel>' . PHP_EOL;
	echo "\t\t" . '<title>' . $title . '</title>' . PHP_EOL;
	echo "\t\t" . '<link>' . $_GET['rss'] . '</link>' . PHP_EOL;
	echo "\t\t" . '<description>' . $text['showing_cmts'] . ' ' . ($total_count - 1) . ' Comments</description>' . PHP_EOL;
	echo "\t\t" . '<atom:link href="http://' . $domain . $_SERVER['PHP_SELF'] . '?rss=' . str_replace(array("?", "&"), array("%3F", "&amp;"), $_GET['rss']) . '&amp;title=' . $title . '" rel="self"></atom:link>' . PHP_EOL;
	echo "\t\t" . '<language>en-us</language>' . PHP_EOL;
	echo "\t\t" . '<ttl>40</ttl>' . PHP_EOL;
	echo iconv('UTF-8', 'UTF-8//IGNORE', $rss_feed);
	echo "\t" . '</channel>' . PHP_EOL;
	exit('</rss>');

?>
