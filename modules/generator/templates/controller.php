<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index($page = 1) {
		$page_string = site_url('<?php echo $plural_url ?>/index/$page_num');

		$q = new Query(<?php echo $model_name ?>::getTableName());
		if (isset($_REQUEST['SortBy'])) {
			$q->order($_REQUEST['SortBy'], isset($_REQUEST['Dir']) ? Query::DESC : Query::ASC);
			$page_string .= '?SortBy=' . $_REQUEST['SortBy'];
			if (isset($_REQUEST['Dir'])) {
				$page_string .= '&Dir=' . $_REQUEST['Dir'];
			}
		}

		$qp = new QueryPager($q, 25, $page, '<?php echo $model_name ?>');
		$this['<?php echo $plural ?>'] = $qp->fetchPage();
		$this['page'] = '<?php echo StringFormat::titleCase($plural, ' ') ?>';
		$this['page_string'] = $page_string;
		$this['pager'] = $qp;
	}

	function save(<? if(@$pkMethod): ?>$<?php echo $single ?>_id = null<? endif ?>) {
		$<?php echo $single ?> = $this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$<?php echo $single ?>_id<? endif ?>);
		
		try {
			$<?php echo $single ?>->fromArray($_REQUEST);
			if ($<?php echo $single ?>->validate()) {
				$<?php echo $single ?>->save();
				$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> saved';
				$this-><? if(@$pkMethod): ?>redirect('<?php echo $plural_url ?>/show/' . $<?php echo $single ?>-><?php echo $pkMethod ?>());<? else: ?>redirect('<?php echo $plural_url ?>');<? endif ?>

			}
			$this->persistant['errors'] = $<?php echo $single ?>->getValidationErrors();
		} catch (Exception $e) {
			$this->persistant['errors'][] = $e->getMessage();
		}
		
		$this->persistant['<?php echo $single ?>'] = $<?php echo $single ?>;
		$this->redirect('<?php echo $plural_url ?>/edit/'<? if(@$pkMethod): ?> . $<?php echo $single ?>-><?php echo $pkMethod ?>()<? endif ?>);
	}

<? if(@$pkMethod): ?>	function delete($<?php echo $single ?>_id = null) {
		try {
			if (null !== $this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$<?php echo $single ?>_id<? endif ?>) && $this['<?php echo $single ?>']->delete()) {
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
		$this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$<?php echo $single ?>_id<? endif ?>);
	}

<? endif ?>	function edit(<? if(@$pkMethod): ?>$<?php echo $single ?>_id = null<? endif ?>) {
		$this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$<?php echo $single ?>_id<? endif ?>)->fromArray(@$_REQUEST);
	}

<?php
		foreach($this->getForeignKeysFromTable($table_name) as $r){
			$to_table = $r->getForeignTableName();
			$to_class_name = $this->getModelName($to_table);
			$from_column = array_shift($r->getLocalColumns());
			$fk_single = str_replace(array('_', '-'), '', strtolower($to_table));
			if(@$used_from[$to_table]) continue;
			$used_from[$to_table] = $from_column;
?>
	function <?php echo $fk_single ?>($<?php echo $fk_single ?>_id = null) {
		$<?php echo $fk_single ?>_id = $<?php echo $fk_single ?>_id ? $<?php echo $fk_single ?>_id : @$_REQUEST[<?php echo $to_class_name ?>::getPrimaryKey()];
		$<?php echo $fk_single ?> = $<?php echo $fk_single ?>_id ? <?php echo $to_class_name ?>::retrieveByPK($<?php echo $fk_single ?>_id) : new <?php echo $to_class_name ?>;

		$this['<?php echo $plural ?>'] = $<?php echo $fk_single ?>->get<?php echo $model_name ?>s();
		$this['<?php echo $fk_single ?>'] = $<?php echo $fk_single ?>;
		$this['page'] = '<?php echo StringFormat::titleCase($plural, ' ') ?> for <?php echo $fk_single ?>';
	}

<?php
		}
?>
	/**
	 * @return <?php echo $model_name ?>

	 */
	private function _get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$<?php echo $single ?>_id = null<? endif ?>) {
		if (isset($this['<?php echo $single ?>'])) {
			// if <?php echo $single ?> has already been set manually, don't mess with it
			return $this['<?php echo $single ?>'];
		}
		
<? if(@$pkMethod): ?>
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
<? else: ?>		$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
<? endif ?>
		return $this['<?php echo $single ?>'];
	}
	
}
