<?php

class DefaultGenerator extends BaseGenerator {

	/**
	 * @var array
	 */
	protected $actionIcons = array('Edit' => 'pencil', 'Show' => 'search', 'Delete' => 'trash');
	
	/**
	 * @var array
	 */
	protected $standardActions = array('Show', 'Edit', 'Delete');
	
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

	function getTemplateParams($table_name) {
		$class_name = $this->getModelName($table_name);
		$pk = call_user_func(array($class_name, 'getPrimaryKey'));
		$column_names = call_user_func(array($class_name, 'getColumnNames'));
		return array(
			'table_name' => $table_name,
			'controller_name' => $this->getControllerName($table_name),
			'model_name' => $class_name,
			'column_names' => $column_names,
			'plural' => StringFormat::pluralVariable($table_name),
			'plural_url' => StringFormat::pluralURL($table_name),
			'single' => StringFormat::variable($table_name),
			'single_url' => StringFormat::url($table_name),
			'pk' => $pk,
			'pkMethod' => $pk ? "get$pk" : null,
			'actions' => $this->getActions($table_name),
			'columns' => $this->getColumns($table_name)
		);
	}

	function getActions($table_name) {
		$controller_name = $this->getControllerName($table_name);
		$class_name = $this->getModelName($table_name);
		$plural = StringFormat::pluralVariable($table_name);
		$single = StringFormat::variable($table_name);
		$pk = call_user_func(array($class_name, 'getPrimaryKey'));
		$pkMethod = "get$pk";
		$actions = array();
		if (!$pk) {
			return $actions;
		}

		foreach ($this->standardActions as &$staction) {
			$actions[$staction] = "<?php echo site_url('" . StringFormat::pluralURL($table_name) . '/' . strtolower($staction) . "/' . $" . $single . '->' . $pkMethod . '()) ?>';
		}
			
		$fkeys_to = $this->getForeignKeysToTable($table_name);
		foreach ($fkeys_to as $k => &$r) {
			$from_table = $r->getTableName();
			$from_class_name = $this->getModelName($from_table);
			$from_column = array_shift($r->getLocalColumns());
			$to_column = array_shift($r->getForeignColumns());
			if (@$used_to[$from_table]) {
				unset($fkeys_to[$k]);
				continue;
			}
			$used_to[$from_table] = $from_column;
			$actions[ucwords(StringFormat::titleCase(StringFormat::plural($from_table), ' '))] = "<?php echo site_url('" . StringFormat::pluralURL($from_table) . '/' . $single . "/' . $" . $single . '->' . $pkMethod . '()) ?>';
		}

		return $actions;
	}

	/**
	 * @param string $table_name
	 * @return string
	 */
	function getControllerName($table_name) {
		$controller_name = str_replace(' ', '', ucwords(str_replace('_', ' ', $table_name)));
		$controller_name = StringFormat::plural($controller_name);
		$controller_name = $controller_name . 'Controller';
		return $controller_name;
	}

	function getControllerFileName($table_name) {
		return $this->getControllerName($table_name) . '.php';
	}

}
