<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $title ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/style.css', true) ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/themes/redmond/jquery-ui-1.8.custom.css', true) ?>" />
		<script language="Javascript" type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
		<script language="Javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>
		<script>
		$(function() {
			$('li.ui-state-default, a.ui-state-default, input.ui-state-default, div.ui-state-default, span.ui-state-default').hover(function(){
				$(this).addClass('ui-state-hover');
			}, function(){
				$(this).removeClass('ui-state-hover');
			});
			$('input.datepicker').datepicker();
		});
		</script>
	</head>
	<body>

		<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<?php foreach($actions as $label => $url): ?>
				<li class="ui-state-default ui-corner-top <? if (@$current_page == $label) echo "ui-tabs-selected ui-state-active ui-state-hover"?>">
					<a href="<?php echo $url ?>"><?php echo $label ?></a>
				</li>
			<?php endforeach ?>
			</ul>

			<?php if(@$errors): ?>
				<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
				<?php foreach($errors as $error): ?>
					<p>
						<span class="ui-message-icon ui-icon ui-icon-alert"></span>
						<?= $error ?>
					</p>
				<?php endforeach ?>
				</div>
			<?php endif ?>

			<?php if(@$messages): ?>
				<div class="ui-state-highlight ui-corner-all" style="padding: 0pt 0.7em;">
				<?php foreach($messages as $message): ?>
					<p>
						<span class="ui-message-icon ui-icon ui-icon-info"></span>
						<?= $message ?>
					</p>
				<?php endforeach ?>
				</div>
			<?php endif ?>

			<?php echo $content ?>
		</div>
	</body>
</html>