<?php

function redirect($url = ''){
	header('Location: '.site_url($url));
	die;
}