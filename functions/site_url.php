<?php

function site_url($url=''){
	if(strpos($url, 'https://')===0 || strpos($url, 'http://')===0)
		return $url;

	$url = trim($url, '/');

	return BASE_URL.$url;
}