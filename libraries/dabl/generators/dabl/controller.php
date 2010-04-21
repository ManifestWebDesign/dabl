<?php echo "<?php\n"; ?>

class <?php echo $controller_name ?> extends ApplicationController {

	function index(){
		$this['<?php echo $plural ?>'] = <?php echo $className ?>::getAll();
		$this['page'] = '<?php echo ucfirst($plural) ?>';
	}

	function save($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$<?php echo $single ?>->fromArray($_POST);

		if($<?php echo $single ?>->save() || (!$<?php echo $single ?>->isModified() && !$<?php echo $single ?>->isNew())){
			$this->persistant['messages'][] = '<?php echo ucfirst($single) ?> saved';
			redirect('<?php echo $plural ?>/show/'.$<?php echo $single ?>-><?php echo $pkMethod ?>());
		}
		else{
			$this['errors'] = $<?php echo $single ?>->getValidationErrors();
			$this['<?php echo $single ?>'] = $<?php echo $single ?>;
			$this->loadView('<?php echo $plural ?>/edit');
		}
	}

	function delete($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = <?php echo $className ?>::retrieveByPK($id);

		if($<?php echo $single ?>->delete())
			$this->persistant['messages'][] = '<?php echo ucfirst($single) ?> deleted';
		else
			$this->persistant['errors'][] = '<?php echo ucfirst($single) ?> could not be deleted';

		redirect('<?php echo $plural ?>');
	}

	function show($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$this['<?php echo $single ?>'] = $<?php echo $single ?>;
	}

	function edit($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
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

		$this['<?php echo $plural ?>'] = $<?php echo $fk_single ?>->get<?php echo $table_name ?>s();
		$this->loadView('<?php echo $plural ?>/grid');
	}

<?php
		}
?>

}