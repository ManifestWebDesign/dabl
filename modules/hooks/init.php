<?php

$module_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $module_root . 'Hook.php';

function my_first_hook() {
	echo 'my_first_hook <br />';
}

function my_second_hook() {
	echo 'my_second_hook <br />';
}

class HookHolder {
	static function myThirdHook(){
		echo 'HookHolder::myThirdHook <br />';
	}
}

Hook::add('config_loaded', array('HookHolder', 'myThirdHook'));
Hook::add('config_loaded', 'my_second_hook', 2);
Hook::add('config_loaded', 'my_first_hook', 1);