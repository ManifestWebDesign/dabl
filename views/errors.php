			<?php if(@$errors): ?>
				<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
				<?php foreach($errors as $error): ?>
					<p>
						<span class="ui-message-icon ui-icon ui-icon-alert"></span>
						<?php echo $error ?>
					</p>
				<?php endforeach ?>
				</div>
			<?php endif ?>