<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index($page=1){
		$pageString = site_url('<?php echo $plural_url ?>/index/$page_num');

		$q = new Query('<?php echo $single ?>');
		if(isset($_REQUEST['SortBy'])) {
			$q->order($_REQUEST['SortBy'], isset($_REQUEST['Dir']) ? Query::DESC : Query::ASC);
			$pageString .= '?SortBy='.$_REQUEST['SortBy'];
		}
		if(isset($_REQUEST['Dir']))
			$pageString .= '&Dir='.$_REQUEST['Dir'];


		$qp = new QueryPager($q, 25, $page, '<?php echo $model_name ?>');
		$this['<?php echo $plural ?>'] = $qp->fetchPage();
		$this['page'] = '<?php echo self::spaceTitleCase($plural) ?>';
		$this['pageString'] = $pageString;
		$this['pager'] = $qp;
	}

	function save($id = null){
		$id = $id ? $id : @$_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$<?php echo $single ?>->fromArray($_REQUEST);

		if($<?php echo $single ?>->save() || (!$<?php echo $single ?>->isModified() && !$<?php echo $single ?>->isNew())){
			$this->persistant['messages'][] = '<?php echo self::spaceTitleCase($single) ?> saved';
			redirect('<?php echo $plural_url ?>/show/'.$<?php echo $single ?>-><?php echo $pkMethod ?>());
		}
		else{
			$this['errors'] = $<?php echo $single ?>->getValidationErrors();
			$this['<?php echo $single ?>'] = $<?php echo $single ?>;
			$this->loadView($this->getViewDir().'/edit');
		}
	}

	function delete($id = null){
		$id = $id ? $id : @$_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = <?php echo $model_name ?>::retrieveByPK($id);

		if($<?php echo $single ?>->delete())
			$this->persistant['messages'][] = '<?php echo self::spaceTitleCase($single) ?> deleted';
		else
			$this->persistant['errors'][] = '<?php echo self::spaceTitleCase($single) ?> could not be deleted';

		redirect('<?php echo $plural_url ?>');
	}

	function show($id = null){
		$id = $id ? $id : @$_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$this['<?php echo $single ?>'] = $<?php echo $single ?>;
	}

	function edit($id = null){
		$id = $id ? $id : @$_REQUEST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$<?php echo $single ?>->fromArray(@$_REQUEST);
		$this['<?php echo $single ?>'] = $<?php echo $single ?>;
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
	function <?php echo $fk_single ?>($id = null){
		$id = $id ? $id : @$_REQUEST[<?php echo $to_class_name ?>::getPrimaryKey()];
		$<?php echo $fk_single ?> = $id ? <?php echo $to_class_name ?>::retrieveByPK($id) : new <?php echo $to_class_name ?>;

		$this['<?php echo $plural ?>'] = $<?php echo $fk_single ?>->get<?php echo $model_name ?>s();
		$this['<?php echo $fk_single ?>'] = $<?php echo $fk_single ?>;
		$this['page'] = '<?php echo self::spaceTitleCase($plural) ?> for <?php echo $fk_single ?>';
	}
<?php
		}
?>
}
