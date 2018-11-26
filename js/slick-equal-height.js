(function($) {
	
	$(window).ready( function() {
		
		setTimeout(function() {
			
			var slider_height = $('.slick-track').css('height');
			// console.log( slider_height );
			$('.blitz-slide > div').each( function() {
				$(this).css('height', slider_height);
			});
			
		}, 500);
	});
	
})(jQuery);