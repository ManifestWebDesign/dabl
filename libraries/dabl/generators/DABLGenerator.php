<?php

class DABLGenerator extends BaseGenerator {

	function getViews($tableName){
		return array(
			'edit.php' => $this->getEditView($tableName),
			'index.php' => $this->getIndexView($tableName),
			'show.php' => $this->getShowView($tableName)
		);
	}

	/**
	 * Generates a String with an html/php view for editing view MVC
	 * objects in the given table.
	 * @param String $tableName
	 * @param String $className
	 * @return String
	 */
	function getEditView($tableName){
		$controllerName = $this->getControllerName($tableName);
		$className = $this->getModelName($tableName);
		$plural = $this->getViewDirName($tableName);
		$single = strtolower($tableName);
		$instance = new $className;
		$pk = $instance->getPrimaryKey();
		ob_start();
?>
<form method="POST" action="<?php echo "<?php echo site_url('".$plural."/save') ?>" ?>">
<?php
		if($pk){
?>
	<input type="hidden" name="<?php echo $pk ?>" value="<?php echo '<?php echo htmlentities($'.$single.'->'."get$pk".'()) ?>' ?>" />
<?php
		}
?>
	<table>
		<tbody>
<?php
		foreach($instance->getColumnNames() as $columnName){
			if($columnName==$pk)continue;
			$method = "get$columnName";
			$output = '<?php echo htmlentities($'.$single.'->'.$method.'()) ?>';
?>
			<tr>
				<th><?php echo $columnName ?></th>
				<td><input type="text" name="<?php echo $columnName ?>" value="<?php echo $output ?>" /></td>
			</tr>
<?php
		}
?>
			<tr>
				<td>
					<input type="submit" value="Save" />
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php
		return ob_get_clean();
	}

	/**
	 * Generates a String with an html/php view showing all of the
	 * objects from the given table in a grid
	 * @param String $tableName
	 * @param String $className
	 * @return String
	 */
	function getIndexView($tableName){
		$controllerName = $this->getControllerName($tableName);
		$className = $this->getModelName($tableName);
		$instance = new $className;
		$pk = $instance->getPrimaryKey();
		$plural = $this->getViewDirName($tableName);
		$single = strtolower($tableName);
		ob_start();
?>
<a href="<?php echo "<?php echo site_url('".$plural."/edit') ?>" ?>">new <?php echo str_replace('_', ' ', $single) ?></a>
<table>
	<thead>
		<tr>
<?php
		foreach($instance->getColumnNames() as $columnName){
?>
			<th><?php echo $columnName ?></th>
<?php
		}
		if($pk){
?>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
<?php
		}
?>
		</tr>
	</thead>
	<tbody>
<?php echo "<?" ?> foreach($<?php echo $plural ?> as $key => $<?php echo $single ?>): <?php echo "?>" ?>

		<tr class="<?php echo '<?php echo' ?> is_int($key/2) ? 'odd' : 'even' <?php echo '?>' ?>">
<?php
		foreach($instance->getColumnNames() as $columnName){
			$output = '<?php echo htmlentities($'.$single.'->'."get$columnName".'()) ?>';
?>
			<td><?php echo $output ?></td>
<?php
		}
		if($pk){
			$pkMethod = "get$pk";
			$showURL = "<?php echo site_url('".$plural."/show/'.$".$single."->".$pkMethod."()) ?>";
			$editURL = "<?php echo site_url('".$plural."/edit/'.$".$single."->".$pkMethod."()) ?>";
			$deleteURL = "<?php echo site_url('".$plural."/delete/'.$".$single."->".$pkMethod."()) ?>";
?>
			<td><a href="<?php echo $showURL ?>">Show</a></td>
			<td><a href="<?php echo $editURL ?>">Edit</a></td>
			<td><a href="<?php echo $deleteURL ?>">Delete</a></td>
<?php
		}
?>
		</tr>
<?php echo "<?" ?> endforeach; <?php echo "?>" ?>

	</tbody>
</table>
<?php
		return ob_get_clean();
	}

	/**
	 * Generates a String with an html/php view for show view MVC
	 * objects in the given table.
	 * @param String $tableName
	 * @param String $className
	 * @return String
	 */
	function getShowView($tableName){
		$controllerName = $this->getControllerName($tableName);
		$className = $this->getModelName($tableName);
		$plural = $this->getViewDirName($tableName);
		$single = strtolower($tableName);
		$instance = new $className;
		$pk = $instance->getPrimaryKey();
		ob_start();
?>
	<table>
		<tbody>
<?php
		foreach($instance->getColumnNames() as $columnName){
			if($columnName==$pk)continue;
			$method = "get$columnName";
?>
			<tr>
				<th><?php echo $columnName ?></th>
				<td><?php echo '<?php echo htmlentities($'.$single.'->'.$method.'()) ?>' ?></td>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
<?php
		return ob_get_clean();
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

class <?php echo $controllerName ?> extends ApplicationController {

	function index(){
		$this-><?php echo $plural ?> = <?php echo $className ?>::getAll();
	}

	function save($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$<?php echo $single ?>->fromArray($_POST);
		$<?php echo $single ?>->save();
		redirect('<?php echo $plural ?>');
	}

	function delete($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = <?php echo $className ?>::retrieveByPK($id);
		$<?php echo $single ?>->delete();
		redirect('<?php echo $plural ?>');
	}

	function show($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$this-><?php echo $single ?> = $<?php echo $single ?>;
	}

	function edit($id = null){
		$id = $id ? $id : @$_POST[<?php echo $className ?>::getPrimaryKey()];
		$<?php echo $single ?> = $id ? <?php echo $className ?>::retrieveByPK($id) : new <?php echo $className ?>;
		$this-><?php echo $single ?> = $<?php echo $single ?>;
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
		$controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName)));
		$controllerName = self::pluralize($controllerName);
		$controllerName = $controllerName.'Controller';
		return $controllerName;
	}

	function getControllerFileName($tableName){
		return $this->getControllerName($tableName).".php";
	}
}
