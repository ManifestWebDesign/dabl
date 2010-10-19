<?php

require_once '../config.php';

$route = @$_GET['url'];
load_controller($route);