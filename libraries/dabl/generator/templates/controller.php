<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index() {
		$q = <?php echo $model_name ?>::getQuery($_REQUEST);

		// paginate
		$qp = new QueryPager($q, !empty($_REQUEST['limit']) ? $_REQUEST['limit'] : 25, @$_REQUEST['page']);

		$this['pager'] = $qp;
		return $this['<?php echo $plural ?>'] = $qp->fetchPage();
	}

	function edit(<?php if (@$pk_method): ?>$<?php echo $single ?>_id = null<?php endif ?>) {
		return $this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>)->fromArray(@$_REQUEST);
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
<?php if (@$pk_method): ?>

	function show($<?php echo $single ?>_id = null) {
		return $this->_get<?php echo $model_name ?>(<?php if(@$pk_method): ?>$<?php echo $single ?>_id<?php endif ?>);
	}

	function delete($<?php echo $single ?>_id = null) {
		$<?php echo $single ?> = $this->_get<?php echo $model_name ?>($<?php echo $single ?>_id);

		try {
			if (null !== $<?php echo $single ?> && $<?php echo $single ?>->delete()) {
				$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> deleted';
			} else {
				$this->persistant['errors'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> could not be deleted';
			}
		} catch (Exception $e) {
			$this->persistant['errors'][] = $e->getMessage();
		}

		$this->redirect('<?php echo $plural_url ?>');
	}
<?php endif ?>

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
		return $this['<?php echo $single ?>'];
<?php else: ?>		return $this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
<?php endif ?>
	}

}