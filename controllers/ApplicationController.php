<?php

class ApplicationController extends BaseController {

	function __construct(){
		$pageObject = new Page;
		$pageObject->setTitle("Oregon, Washington Vaccum Store | ".STORE_NAME."");
		$pageObject->setMeta("description", "Stark's Vacuums has sold new and used vaccums and appliances for over 77 years.  Stark's has multiple locations in Oregon and Washington.");
		$pageObject->setMeta("keywords", "vacuums, vacuum, store, buy, oregon, washington, portland, hillsboro, vancouver, bend");
		$this->pageObject = $pageObject;
	}

}