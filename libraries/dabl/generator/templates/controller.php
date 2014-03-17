<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	/**
	 * Returns all <?php echo $model_name ?> records matching the query. Examples:
	 * GET /<?php echo $plural_url ?>?column=value&order_by=column&dir=DESC&limit=20&page=2&count_only
	 * GET /rest/<?php echo $plural_url ?>.json&limit=5
	 *
	 * @return <?php echo $model_name ?>[]
	 */
	function index() {
		$q = <?php echo $model_name ?>::getQuery(@$_GET);

		// paginate
		$limit = empty($_REQUEST['limit']) ? 25 : $_REQUEST['limit'];
		$page = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
		$class = '<?php echo $model_name ?>';
		$method = 'doSelectIterator';
		$this['pager'] = new QueryPager($q, $limit, $page, $class, $method);

		if (isset($_GET['count_only'])) {
			return $this['pager'];
		}
		return $this['<?php echo $plural ?>'] = $this['pager']->fetchPage();
	}

	/**
	 * Form to create or edit a <?php echo $model_name ?>. Example:
	 * GET /<?php echo $plural_url ?>/edit/1
	 *
	 * @return <?php echo $model_name ?>

	 */
	function edit(<?php if ($pk): ?>$<?php echo $pk_var ?> = null<?php endif ?>) {
		return $this->get<?php echo $model_name ?>(<?php if ($pk): ?>$<?php echo $pk_var ?><?php endif ?>)->fromArray(@$_GET);
	}

	/**
	 * Saves a <?php echo $model_name ?>. Examples:
	 * POST /<?php echo $plural_url ?>/save/1
	 * POST /rest/<?php echo $plural_url ?>/.json
	 * PUT /rest/<?php echo $plural_url ?>/1.json
	 */
	function save(<?php if ($pk): ?>$<?php echo $pk_var ?> = null<?php endif ?>) {
		$<?php echo $single ?> = $this->get<?php echo $model_name ?>(<?php if ($pk): ?>$<?php echo $pk_var ?><?php endif ?>);

		try {
			$<?php echo $single ?>->fromArray($_REQUEST);
			if ($<?php echo $single ?>->validate()) {
				$<?php echo $single ?>->save();
				$this->flash['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> saved';
				$this-><?php if ($pk): ?>redirect('<?php echo $plural_url ?>/show/' . $<?php echo $single ?>-><?php echo $pk_method ?>());<?php else: ?>redirect('<?php echo $plural_url ?>');<?php endif ?>

			}
			$this->flash['errors'] = $<?php echo $single ?>->getValidationErrors();
		} catch (Exception $e) {
			$this->flash['errors'][] = $e->getMessage();
		}

		$this->redirect('<?php echo $plural_url ?>/edit/'<?php if ($pk): ?> . $<?php echo $single ?>-><?php echo $pk_method ?>()<?php endif ?> . '?' . http_build_query($_REQUEST));
	}
<?php if ($pk): ?>

	/**
	 * Returns the <?php echo $model_name ?> with the <?php echo $pk_var ?>. Examples:
	 * GET /<?php echo $plural_url ?>/show/1
	 * GET /rest/<?php echo $plural_url ?>/1.json
	 *
	 * @return <?php echo $model_name ?>

	 */
	function show($<?php echo $pk_var ?> = null) {
		return $this->get<?php echo $model_name ?>(<?php if ($pk): ?>$<?php echo $pk_var ?><?php endif ?>);
	}

	/**
	 * Deletes the <?php echo $model_name ?> with the <?php echo $pk_var ?>. Examples:
	 * GET /<?php echo $plural_url ?>/delete/1
	 * DELETE /rest/<?php echo $plural_url ?>/1.json
	 */
	function delete($<?php echo $pk_var ?> = null) {
		$<?php echo $single ?> = $this->get<?php echo $model_name ?>($<?php echo $pk_var ?>);

		try {
			if (null !== $<?php echo $single ?> && $<?php echo $single ?>->delete()) {
				$this['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> deleted';
			} else {
				$this['errors'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> could not be deleted';
			}
		} catch (Exception $e) {
			$this['errors'][] = $e->getMessage();
		}

		if ($this->outputFormat === 'html') {
			$this->flash['errors'] = @$this['errors'];
			$this->flash['messages'] = @$this['messages'];
			$this->redirect('<?php echo $plural_url ?>');
		}
	}
<?php endif ?>

	/**
	 * @return <?php echo $model_name ?>

	 */
	private function get<?php echo $model_name ?>(<?php if ($pk): ?>$<?php echo $pk_var ?> = null<?php endif ?>) {
<?php if ($pk): ?>
		// look for id in param or in $_REQUEST array
		if (null === $<?php echo $pk_var ?> && isset($_REQUEST[<?php echo $model_name ?>::getPrimaryKey()])) {
			$<?php echo $pk_var ?> = $_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		}

		if ('' === $<?php echo $pk_var ?> || null === $<?php echo $pk_var ?>) {
			// if no primary key provided, create new <?php echo $model_name ?>

			$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
		} else {
			// if primary key provided, retrieve the record from the db
			$this['<?php echo $single ?>'] = <?php echo $model_name ?>::retrieveByPK($<?php echo $pk_var ?>);
		}
		return $this['<?php echo $single ?>'];
<?php else: ?>		return $this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
<?php endif ?>
	}

}