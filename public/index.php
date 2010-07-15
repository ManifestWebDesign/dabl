<?php
require_once '../config.php';

$route = array_shift(explode('?', @$_GET['url'], 2));
load_controller($route);