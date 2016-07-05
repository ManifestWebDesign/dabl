<?php

//use Dabl\Query\DBManager;

// for backwards compatibility and convenience
class DBManager extends Dabl\Query\DBManager {}
class Model extends Dabl\Orm\Model {}
class Query extends Dabl\Query\Query {}
class Condition extends Dabl\Query\Condition {}
class QueryPager extends Dabl\Query\QueryPager {}

$db_connections = parse_ini_file(CONFIG_DIR . 'connections.ini', true);

// connect to database(s)
foreach ($db_connections as $connection_name => $db_params) {
	DBManager::addConnection($connection_name, $db_params);
}

// timestamp to use for Created and Updated column values
define('CURRENT_TIMESTAMP', time());
define('MODELS_DIR', APP_DIR . 'models/');
define('MODELS_BASE_DIR', MODELS_DIR . 'base/');
define('MODELS_QUERY_DIR', MODELS_DIR . 'query/');
define('MODELS_BASE_QUERY_DIR', MODELS_DIR . 'query/base/');