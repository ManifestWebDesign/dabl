<?php

//New query with table name $q = new Query('Client');
$q->add('Column','Value');

//Limit results per page
$limit = 50;

//Specify the current page
$page = 3;

//Create instance of pager
$pager = new QueryPager($q, $limit, $page);

//Retrieve PDOStatement with results for that page
$resultSet = $pager->fetchPage();
