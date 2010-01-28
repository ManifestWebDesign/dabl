<?php

function page_moved($redirect_to = ""){
	header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
	redirect($redirect_to);
}