<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1" />

		<title><?php echo $title ?></title>

		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/themes/light-green/jquery-ui-1.10.4.custom.css', true) ?>" />
		<link type="text/css" rel="stylesheet" href="<?php echo site_url('css/style.css', true) ?>" />

		<script language="Javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script language="Javascript" type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<script language="Javascript" type="text/javascript" src="<?php echo site_url('js/global.js', true) ?>"></script>
	</head>
	<body>

		<div class="ui-tabs ui-widget">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header">
			<?php foreach($actions as $label => $url): ?>
				<li class="ui-state-default ui-corner-top <?php if (@$current_page == $label) echo "ui-tabs-active ui-state-active"?>">
					<a class="ui-tabs-anchor" href="<?php echo $url ?>"><?php echo $label ?></a>
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