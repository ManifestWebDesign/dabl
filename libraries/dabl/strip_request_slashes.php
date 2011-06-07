<?php

function stripslashes_array($array) {
	return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
}

function strip_request_slashes() {
    $_COOKIE = stripslashes_array($_COOKIE);
    $_GET = stripslashes_array($_GET);
    $_POST = stripslashes_array($_POST);
    $_REQUEST = stripslashes_array($_REQUEST);
}