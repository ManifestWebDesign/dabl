<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $title ?></title>
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/style.css', true) ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/themes/light-green/jquery-ui-1.8.custom.css', true) ?>" />
		<script language="Javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<script language="Javascript" type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
		<script language="Javascript" type="text/javascript" src="<?php echo site_url('js/global.js', true) ?>"></script>
	</head>
	<body>

		<div class="ui-tabs ui-widget ui-widget-header">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix">
			<?php foreach($actions as $label => $url): ?>
				<li class="ui-state-default ui-corner-top <?php if (@$current_page == $label) echo "ui-tabs-selected ui-state-active ui-state-hover"?>">
					<a href="<?php echo $url ?>"><?php echo $label ?></a>
				</li>
			<?php endforeach ?>
			</ul>
		</div>

		<div class="content-wrapper ui-widget">
			<?php View::load('errors', compact('errors')) ?>
			<?php View::load('messages', compact('messages')) ?>

			<div class="content">
				<?php View::load($content_view, $params) ?>
			</div>
		</div>

	</body>
</html>