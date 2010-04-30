<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index(){
		$this['<?php echo $plural ?>'] = <?php echo $model_name ?>::getAll();
		$this['page'] = '<?php echo self::spaceTitleCase($plural) ?>';
	}

	function save($id = null){
		$id = $id ? $id : @$_POST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$<?php echo $single ?>->fromArray($_POST);

		if($<?php echo $single ?>->save() || (!$<?php echo $single ?>->isModified() && !$<?php echo $single ?>->isNew())){
			$this->persistant['messages'][] = '<?php echo self::spaceTitleCase($single) ?> saved';
			redirect('<?php echo $plural ?>/show/'.$<?php echo $single ?>-><?php echo $pkMethod ?>());
		}
		else{
			$this['errors'] = $<?php echo $single ?>->getValidationErrors();
			$this['<?php echo $single ?>'] = $<?php echo $single ?>;
			$this->loadView($this->getViewDir().'/edit');
		}
	}

	function delete($id = null){
		$id = $id ? $id : @$_POST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = <?php echo $model_name ?>::retrieveByPK($id);

		if($<?php echo $single ?>->delete())
			$this->persistant['messages'][] = '<?php echo self::spaceTitleCase($single) ?> deleted';
		else
			$this->persistant['errors'][] = '<?php echo self::spaceTitleCase($single) ?> could not be deleted';

		redirect('<?php echo $plural ?>');
	}

	function show($id = null){
		$id = $id ? $id : @$_POST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$this['<?php echo $single ?>'] = $<?php echo $single ?>;
	}

	function edit($id = null){
		$id = $id ? $id : @$_POST[<?php echo $model_name ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $model_name ?>::retrieveByPK($id) : new <?php echo $model_name ?>;
		$this['<?php echo $single ?>'] = $<?php echo $single ?>;
	}

<?php
		foreach($this->getForeignKeysFromTable($table_name) as $r){
			$to_table = $r['to_table'];
			$to_class_name = $this->getModelName($to_table);
			$from_column = $r['from_column'];
			$fk_single =  strtolower($to_table);
			if(@$used_from[$to_table]) continue;
			$used_from[$to_table] = $from_column;
?>
	function <?php echo $fk_single ?>($id = null){
		$id = $id ? $id : @$_POST[<?php echo $to_class_name ?>::getPrimaryKey()];
		$<?php echo $fk_single ?> = $id ? <?php echo $to_class_name ?>::retrieveByPK($id) : new <?php echo $to_class_name ?>;

		$this['<?php echo $plural ?>'] = $<?php echo $fk_single ?>->get<?php echo $model_name ?>s();
		$this->loadView($this->getViewDir().'/grid');
	}

<?php
		}
?>

}
