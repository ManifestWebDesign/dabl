<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $title ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/style.css') ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/themes/start/jquery-ui-1.8.custom.css') ?>" />
	</head>
	<body>

<?php if(isset($errors)): ?>

		<div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">

	<?php foreach($errors as $error): ?>
			<p>
				<span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>
				<strong>Error:</strong> <?= $error ?>
			</p>
	<?php endforeach ?>

		</div>
<?php endif ?>

		<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<?php foreach($actions as $label => $url): ?>
				<li class="ui-state-default ui-corner-top <? if (@$page == $label) echo "ui-tabs-selected ui-state-active ui-state-hover"?>">
					<a href="<?php echo $url ?>/"><?php echo $label ?></a>
				</li>
			<?php endforeach ?>
			</ul>

			<?php echo $content ?>
		</div>
	</body>
</html>