<?php
include('simple_html_dom.php');

function search($html, $count, $plugin) {
	foreach ($html->find('tr[class="default"]') as $element) {
		$title = $element->find('a[title]', -1)->innertext; // title
		$download = $element->find('td', 2)->find('a', 1)->href; // download
		if (strpos($element->find('td', -5)->innertext, 'GiB')) {
			$multiple = 1073741824;
		} else {
			$multiple = 1048576;
		}
		$size = ((float)substr($element->find('td', -5)->innertext, 0, -4)) * $multiple;
		$datetime = ($element->find('td[data-timestamp]', 0)->innertext).":00"; // datetime
		$page = "https://nyaa.si".$element->find('a[title]', -1)->href; // page
		$hash = md5($title);
		$seeds = $element->find('td', -3)->innertext; // seeds
		$leechs = $element->find('td', -2)->innertext; // leechs
		$category = $element->find('a[title]', 0)->title; // category
		$plugin->addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leechs, $category);
		$count++;
	}
	if (count($html->find('ul[class="pagination"]')) === 1) {
        if (count($html->find('ul[class="pagination"]', 0)->find('li[class="next disabled"]')) === 0) {
            $html = file_get_html("https://nyaa.si".$html->find('li[class="next"]', 0)->find('a', 0)->href);
            search($html, $count, $plugin);
        } else {
            return $count;
        }
    } else {
		return $count;
    }
}

class SynoDLMSearchOhys {
	public function __construct() {
	}

	public function prepare($curl, $query) {
		$url = "https://nyaa.si/user/ohys?f=0&c=0_0&q=".urlencode($query);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($curl);
		return $response;
	}

	public function parse($plugin, $response) {
		$html = str_get_html($response);
		return search($html, 0, $plugin);
	}
}
?>
