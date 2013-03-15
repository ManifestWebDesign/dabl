<?php

/**
 * @link https://github.com/ManifestWebDesign/DABL
 * @link http://manifestwebdesign.com/redmine/projects/dabl
 * @author Manifest Web Design
 * @license    MIT License
 */

function page_moved($redirect_to = ""){
	header($_SERVER["SERVER_PROTOCOL"]." 301 Moved Permanently");
	redirect($redirect_to);
}