<?php

function redirect($url = '', $die = true){
	header('Location: '.site_url($url));
	if($die)
		die();
	header('Content-Length: 0',true);
	header('Content-Type: text/html',true);
	header('Connection: close');
	flush();
	session_write_close();
	set_time_limit(0);
}