(function($){

	$(function() {
		$('.button-group').buttonset();
		$('.button').each(function(){
			var $btn = $(this),
				icon = $btn.attr('data-icon');
			if (icon) {
				$btn.button({
					icons: {
						primary: 'ui-icon-' + icon
					}
				});
			} else {
				$btn.button();
			}
		});

		$('li.ui-state-default, a.ui-state-default, input.ui-state-default, div.ui-state-default, span.ui-state-default')
			.on('mouseenter', function(){
				$(this).addClass('ui-state-hover');
			})
			.on('mouseleave', function(){
				$(this).removeClass('ui-state-hover');
			});
		$('span.button').on('click', function(e){
			if ($(e.target).is('span')) {
				$(this).find('a, input').click();
			}
		});

		$('input.datepicker').datepicker();
	});

})(jQuery);