			<?php if(@$messages): ?>
				<div class="ui-state-highlight ui-corner-all" style="padding: 0pt 0.7em;">
				<?php foreach($messages as $message): ?>
					<p>
						<span class="ui-message-icon ui-icon ui-icon-info"></span>
						<?php echo $message ?>
					</p>
				<?php endforeach ?>
				</div>
			<?php endif ?>