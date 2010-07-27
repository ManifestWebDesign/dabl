<?php

//New query
$q = new Query; $q->add('Column','Value');

//Limit results per page
$limit = 50;

//Specify the current page
$page = 2;

//Create instance of pager, provide the name of the DABL class
$pager = new QueryPager($q, $limit, $page, 'Inspection');

//Retrieve an array of Objects from the DABL class for that page
$inspections = $pager->fetchPage();
