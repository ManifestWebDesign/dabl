<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index() {
		$table_name = <?php echo $model_name ?>::getTableName();
		$q = new Query($table_name);

		// filters
		foreach (<?php echo $model_name ?>::getColumnNames() as $column_name) {
			$full_column_name = $table_name . '.' . $column_name;
			if (isset($_REQUEST[$column_name])) {
				$value = $_REQUEST[$column_name];
			} elseif (!empty($_REQUEST[$full_column_name])) {
				$value = $_REQUEST[$full_column_name];
			} else {
				continue;
			}
			$q->add($full_column_name, $value);
		}

		// sort
		if (isset($_REQUEST['SortBy'])) {
			$q->orderBy($_REQUEST['SortBy'], isset($_REQUEST['Dir']) ? Query::DESC : Query::ASC);
		}

		// paginate
		$qp = new QueryPager($q, 25, @$_REQUEST['page'], '<?php echo $model_name ?>');

		$this['<?php echo $plural ?>'] = $qp->fetchPage();
		$this['pager'] = $qp;
	}

	function save(<?php if(@$pk_method): ?>$<?php echo $single ?>_id = null<?php endif ?>) {
		$<?php echo $single ?> = $this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>);

		try {
			$<?php echo $single ?>->fromArray($_REQUEST);
			if ($<?php echo $single ?>->validate()) {
				$<?php echo $single ?>->save();
				$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> saved';
				$this-><?php if(@$pk_method): ?>redirect('<?php echo $plural_url ?>/show/' . $<?php echo $single ?>-><?php echo $pk_method ?>());<?php else: ?>redirect('<?php echo $plural_url ?>');<?php endif ?>

			}
			$this->persistant['errors'] = $<?php echo $single ?>->getValidationErrors();
		} catch (Exception $e) {
			$this->persistant['errors'][] = $e->getMessage();
		}

		$this->redirect('<?php echo $plural_url ?>/edit/'<?php if(@$pk_method): ?> . $<?php echo $single ?>-><?php echo $pk_method ?>()<?php endif ?> . '?' . http_build_query($_REQUEST));
	}

<?php if (@$pk_method): ?>	function delete($<?php echo $single ?>_id = null) {
		try {
			if (null !== $this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>) && $this['<?php echo $single ?>']->delete()) {
				$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> deleted';
			} else {
				$this->persistant['errors'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> could not be deleted';
			}
		} catch (Exception $e) {
			$this->persistant['errors'][] = $e->getMessage();
		}

		$this->redirect('<?php echo $plural_url ?>');
	}

	function show($<?php echo $single ?>_id = null) {
		$this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>);
	}

<?php endif ?>	function edit(<?php if (@$pk_method): ?>$<?php echo $single ?>_id = null<?php endif ?>) {
		$this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>)->fromArray(@$_REQUEST);
	}

	/**
	 * @return <?php echo $model_name ?>

	 */
	private function _get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id = null<?php endif ?>) {
<?php if (@$pk_method): ?>
		// look for id in param or in $_REQUEST array
		if (null === $<?php echo $single ?>_id && isset($_REQUEST[<?php echo $model_name ?>::getPrimaryKey()])) {
			$<?php echo $single ?>_id = $_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		}

		if ('' === $<?php echo $single ?>_id || null === $<?php echo $single ?>_id) {
			// if no primary key found, create new <?php echo $model_name ?>

			$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
		} else {
			// if primary key found, retrieve the record from the db
			$this['<?php echo $single ?>'] = <?php echo $model_name ?>::retrieveByPK($<?php echo $single ?>_id);
		}
<?php else: ?>		$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
<?php endif ?>
		return $this['<?php echo $single ?>'];
	}

}