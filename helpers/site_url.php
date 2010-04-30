<?php

function site_url($url = '', $version = false){
	if(strpos($url, 'https://')===0 || strpos($url, 'http://')===0)
		return $url;

	if(is_array($url))
		$url = implode('/', $url);

	if($version){
		$path = ROOT.'/public/'.$url;
		if (!is_file($path))
			throw new Exception('File '.$url.' not found.');
		$char = (strpos($url, '?')===false) ? '?' : '&';
		$url .= $char.'v='.filemtime($path);
	}

	$url = trim($url, '/');

	return BASE_URL.$url;
}