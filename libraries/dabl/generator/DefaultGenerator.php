<?php

class DefaultGenerator extends BaseGenerator {

	/**
	 * @var array
	 */
	protected $actionIcons = array(
		'Edit' => 'pencil',
		'Show' => 'search',
		'Delete' => 'trash'
	);

	/**
	 * @var array
	 */
	protected $standardActions = array(
		'Show',
		'Edit',
		'Delete'
	);

	/**
	 * @var array
	 */
	protected $viewTemplates = array(
		'edit.php' => '/templates/edit.php',
		'index.php' => '/templates/index.php',
		'grid.php' => '/templates/grid.php',
		'show.php' => '/templates/show.php'
	);

	/**
	 * @var string
	 */
	protected $controllerTemplate = '/templates/controller.php';

	function getActions($table_name) {
		$single = StringFormat::variable($table_name);
		$pks = $this->getPrimaryKeys($table_name);

		if (count($pks) === 1) {
			$pk = $pks[0];
		} else {
			$pk = null;
		}

		$actions = array();
		if (!$pk) {
			return $actions;
		}

		$pk_method = StringFormat::classMethod('get' . StringFormat::titleCase($pk->getName()));

		foreach ($this->standardActions as &$staction) {
			$actions[$staction] = "<?php echo site_url('" . StringFormat::pluralURL($table_name) . '/' . strtolower($staction) . "/' . $" . $single . '->' . $pk_method . '()) ?>';
		}

		$fkeys_to = $this->getForeignKeysToTable($table_name);
		foreach ($fkeys_to as $k => &$r) {
			$from_table = $r->getTableName();
			$local_columns = $r->getLocalColumns();
			$from_column = array_shift($local_columns);
			if (@$used_to[$from_table]) {
				unset($fkeys_to[$k]);
				continue;
			}
			$used_to[$from_table] = $from_column;
			$actions[ucwords(StringFormat::titleCase(StringFormat::plural($from_table), ' '))] = "<?php echo site_url('" . StringFormat::pluralURL($from_table) . "?$from_column=' . $" . $single . '->' . $pk_method . '()) ?>';
		}

		return $actions;
	}

}