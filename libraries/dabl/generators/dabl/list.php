<ul class="object-list <?php echo $single ?>-list">
	<?php echo '<?php foreach($'.$plural.' as $key => $'.$single.'): ?>' ?>

	<li class="<?php echo '<?php echo' ?> ($key & 1) ? 'even' : 'odd' <?php echo '?>' ?>">
		<dl>
			<?php foreach($instance->getColumnNames() as $column_name): ?>

			<dt><?php echo $column_name ?></dt>
			<dd><?php echo '<?php echo htmlentities($'.$single.'->'."get$column_name".'()) ?>' ?></dd>
			<?php endforeach ?>

		</dl>
	</li>
	<?php echo '<?php endforeach ?>' ?>

</ul>