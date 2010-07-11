<?php
require_once '../config.php';

echo 'Whole table with modified fetch(PDO::FETCH_ASSOC) and MySQL native driver PDO<br />';

$start = microtime(true);
$localities = Locality::getAll();
$time = microtime(true) - $start;

echo count($localities).' localities<br />';
echo round($time, 5).' seconds<br />';
echo memory_get_peak_usage(true).' bytes<br />';

var_dump($localities[0]);

die;

$route = array_shift(explode('?', @$_GET['url'], 2));
load_controller($route);

