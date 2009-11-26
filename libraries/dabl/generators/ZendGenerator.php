<?php

class ZendGenerator extends BaseGenerator {

	function getViews($tableName){
		return array(
			'edit.phtml' => $this->getEditView($tableName),
			'index.phtml' => $this->getIndexView($tableName)
		);
	}

	/**
	 * Generates a String with Controller class for MVC
	 * @param String $tableName
	 * @param String $className
	 * @return String
	 */
	function getController($tableName){
		$controllerName = $this->getControllerName($tableName);
		$plural = $this->getViewDirName($tableName);
		$className = $this->getModelName($tableName);
		$single = strtolower($tableName);
		ob_start();
		echo "<?php\n";
?>
class <?php echo $controllerName ?> extends Zend_Controller_Action {

	function indexAction(){
		$this->view-><?php echo $plural ?> = <?php echo $className ?>::getAll();
	}

	function saveAction($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$<?php echo $single ?>->fromArray($_POST);
		$<?php echo $single ?>->save();
		redirect('<?php echo $plural ?>');
	}

	function deleteAction($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = <?php echo $className ?>::retrieveByPK($id);
		$<?php echo $single ?>->delete();
		redirect('<?php echo $plural ?>');
	}

	function editAction($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$this->view-><?php echo $single ?> = $<?php echo $single ?>;
	}

}
<?php
		return ob_get_clean();
	}

	/**
	 * @param string $tableName
	 * @return string
	 */
	function getControllerName($tableName){
		$controllerName = str_replace(' ', '_', ucwords(strtolower(str_replace('_', ' ', $tableName))));
		$controllerName = self::pluralize($controllerName);
		$controllerName = $controllerName.'Controller';
		return $controllerName;
	}

	function getControllerFileName($tableName){
		return $this->getControllerName($tableName).".php";
	}

}
