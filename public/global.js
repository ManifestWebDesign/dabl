(function($){
	window.initWidgets = function initWidgets($element) {
		$element = $element || $('body');

		$element.find('.button-group').buttonset();
		$element.find('.button').each(function(){
			var $btn = $(this),
				icon = $btn.attr('data-icon'),
				hasText = $btn.text().replace(/^\s+|\s+$/g,'') !== '' || $btn.find('input').length !== 0;

			if (!hasText) {
				$btn.text('&nbsp;');
			}
			if (icon) {
				$btn.button({
					icons: {
						primary: 'ui-icon-' + icon
					},
					text: hasText
				});
			} else {
				$btn.button();
			}
		});

		$element.find('li.ui-state-default, a.ui-state-default, input.ui-state-default, div.ui-state-default, span.ui-state-default')
			.on('mouseenter', function(){
				$(this).addClass('ui-state-hover');
			})
			.on('mouseleave', function(){
				$(this).removeClass('ui-state-hover');
			});
		$element.find('span.button').on('click', function(e){
			if ($(e.target).is('span')) {
				$(this).find('a, input').click();
			}
		});

		$element.find('input.datepicker').datepicker();
		$element.find('.tooltip[title]').tooltip();
	};

	$(function(){
		initWidgets();
	});
})(jQuery);