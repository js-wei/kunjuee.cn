;(function($){
	$('.checkbox').click(function(event) {
		/* Act on the event */
		event.preventDefault();
		$element = $(this);
		if($element.find('div.checker').children('span').attr('class')=='checked'){
			$element.find('div.checker').children('span').removeClass('checked');
			$element.find('div.checker').children('span').children('input[type="checkbox"]').removeAttr('checked');
		}else{
			$element.find('div.checker').children('span').addClass('checked');
			$element.find('div.checker').children('span').children('input[type="checkbox"]').attr('chekced', 'checked');
		}
		
	});
})(jQuery);