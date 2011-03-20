<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index($page = 1) {
		$pageString = site_url('<?php echo $plural_url ?>/index/$page_num');

		$q = new Query(<?php echo $model_name ?>::getTableName());
		if (isset($_REQUEST['SortBy'])) {
			$q->order($_REQUEST['SortBy'], isset($_REQUEST['Dir']) ? Query::DESC : Query::ASC);
			$pageString .= '?SortBy=' . $_REQUEST['SortBy'];
			if (isset($_REQUEST['Dir'])) {
				$pageString .= '&Dir=' . $_REQUEST['Dir'];
			}
		}

		$qp = new QueryPager($q, 25, $page, '<?php echo $model_name ?>');
		$this['<?php echo $plural ?>'] = $qp->fetchPage();
		$this['page'] = '<?php echo StringFormat::titleCase($plural, ' ') ?>';
		$this['pageString'] = $pageString;
		$this['pager'] = $qp;
	}

	function save(<? if(@$pkMethod): ?>$id = null<? endif ?>) {
		$this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$id<? endif ?>)->fromArray($_REQUEST);

		if ($this['<?php echo $single ?>']->validate()) {
			$this['<?php echo $single ?>']->save();
			$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> saved';
			<? if(@$pkMethod): ?>redirect('<?php echo $plural_url ?>/show/' . $this['<?php echo $single ?>']-><?php echo $pkMethod ?>());<? else: ?>redirect('<?php echo $plural_url ?>');<? endif ?>

		}
		
		$this->persistant['errors'] = $this['<?php echo $single ?>']->getValidationErrors();
		$this->persistant['<?php echo $single ?>'] = $this['<?php echo $single ?>'];
		redirect('<?php echo $plural_url ?>/edit/'<? if(@$pkMethod): ?> . $this['<?php echo $single ?>']-><?php echo $pkMethod ?>()<? endif ?>);
	}

<? if(@$pkMethod): ?>	function delete($id = null) {
		if (null !== $this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$id<? endif ?>) && $this['<?php echo $single ?>']->delete()) {
			$this->persistant['messages'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> deleted';
		} else {
			$this->persistant['errors'][] = '<?php echo StringFormat::titleCase($single, ' ') ?> could not be deleted';
		}

		redirect('<?php echo $plural_url ?>');
	}

	function show($id = null) {
		$this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$id<? endif ?>);
	}

<? endif ?>	function edit(<? if(@$pkMethod): ?>$id = null<? endif ?>) {
		$this->_get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$id<? endif ?>)->fromArray(@$_REQUEST);
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
	function <?php echo $fk_single ?>($id = null) {
		$id = $id ? $id : @$_REQUEST[<?php echo $to_class_name ?>::getPrimaryKey()];
		$<?php echo $fk_single ?> = $id ? <?php echo $to_class_name ?>::retrieveByPK($id) : new <?php echo $to_class_name ?>;

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
	private function _get<?php echo $model_name ?>(<? if(@$pkMethod): ?>$id = null<? endif ?>) {
		if (isset($this['<?php echo $single ?>']) && null !== $this['<?php echo $single ?>']) {
			// if <?php echo $single ?> has already been set manually, don't mess with it
			return $this['<?php echo $single ?>'];
		}
		
<? if(@$pkMethod): ?>
		// look for id in param or in $_REQUEST array
		if (null === $id && isset($_REQUEST[<?php echo $model_name ?>::getPrimaryKey()])) {
			$id = $_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		}
		
		if ('' === $id || null === $id) {
			// if no primary key found, create new <?php echo $model_name ?>

			$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
		} else {
			// if primary key found, retrieve the record from the db
			$this['<?php echo $single ?>'] = <?php echo $model_name ?>::retrieveByPK($id);
		}
<? else: ?>		$this['<?php echo $single ?>'] = new <?php echo $model_name ?>;
<? endif ?>
		return $this['<?php echo $single ?>'];
	}
	
}