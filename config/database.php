<?php

//use Dabl\Query\DBManager;

// for backwards compatibility and convenience
/**
 * Class DBManager
 * @deprecated Use Dabl\Query\DBManager
 */
class DBManager extends Dabl\Query\DBManager {}

/**
 * Class Model
 * @deprecated use Dabl\Orm\Model
 */
class Model extends Dabl\Orm\Model {}

/**
 * Class Query
 * @deprecated use Dabl\Query\Query
 */
class Query extends Dabl\Query\Query {}

/**
 * Class Condition
 * @deprecated use Dabl\Query\Condition
 */
class Condition extends Dabl\Query\Condition {}

/**
 * Class QueryPager
 * @deprecated use Dabl\Query\QueryPager
 */
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