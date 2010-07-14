<?php

// create a new Query object
$q = new Query('table');

// add a calculated column
$q->addColumn('count(0)');

// retrieve the count of all records from `table`
$q->doSelect();
