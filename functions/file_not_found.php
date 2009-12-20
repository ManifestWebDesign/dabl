<?php

/**
 * @param string $file
 */
function file_not_found($file){
	if(!headers_sent())
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

	throw new Exception("$file not found");
}