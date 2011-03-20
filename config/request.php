<?php

// the browser path to this application.  it should be:
// a full url with http:// and a trailing slash OR
// a subdirectory with leading and trailing slashes
define('BASE_URL', '/');

// directory for public html files that are directly exposed to the web server
define('PUBLIC_DIR', APP_DIR . 'public' . DIRECTORY_SEPARATOR);

// Strip added slashes if needed
if (get_magic_quotes_gpc()) {
	strip_request_slashes();
}