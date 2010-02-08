<?php

class IndexController extends ApplicationController {

	function index(){

		echo "<pre>";

		$q = new Query('post');
		$q->add('Title', '%fun%', Query::LIKE);
		$q->add('DateTime', array('0000-00-00', '2011-01-01'), Query::BETWEEN);

		$q2 = new Query('author');
		$q2->add('FirstName', 'Courtney');
		$q2->addColumn('AuthorID');
		$q->add('AuthorID', $q2);
		echo $q."<br /><br />";

		echo Post::doCount($q)."<br /><br />";
		
		print_r(Post::doSelect($q));
	}

}