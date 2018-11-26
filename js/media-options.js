(function($) {
	
	// Media Library JS
	$(document).on( 'click', '.image-preview-wrapper .remove', function() {
		$(this).siblings('img').attr('src', '');
		$(this).parent().siblings('.image_attachment_id').val('');
	});
	
	// Pagination Settings Hide/Show <table> & <tr> on READY
	$(document).ready( function() {
		// List
		if ($('.cpt-pagination').is(':checked')) {
			$('form.cpt-settings .loop-display').css('display', 'table'); 
			if($('#pagination-custom').is(':checked')) {
				$('form.cpt-settings > .pagination-styles').css('display', 'table');
			}
		}
		if($('#icon_default').is(':checked')) { $('.pagination-styles-icons .icon-default').css('display', 'table-row'); }
		if($('#icon_fontawesome').is(':checked')) { $('.pagination-styles-icons .icon-fontawesome').css('display', 'table-row'); }
		if($('#icon_upload').is(':checked')) { $('.pagination-styles-icons .icon-upload').css('display', 'table-row'); }
		
		// Single
	    if($('.cpt-single-pagination').is(':checked')) { 
			$('form.cpt-settings > .pagination-single').css('display', 'table');
			if ($('#icon_fontawesome_single').is(':checked')) { $('.fontawesome-link').css('display', 'block'); }
		}
		if($('#icon_default_single').is(':checked')) { $('.pagination-single-icons .icon-default').css('display', 'table-row'); }
		if($('#icon_fontawesome_single').is(':checked')) { $('.pagination-single-icons .icon-fontawesome').css('display', 'table-row'); }
		if($('#icon_upload_single').is(':checked')) { $('.pagination-single-icons .icon-upload').css('display', 'table-row'); }
	});
	// List 
	$(document).on( 'click', '.cpt-pagination', function() {
	    if($('.cpt-pagination').is(':checked')) { 
			$('form.cpt-settings > .loop-display').css('display', 'table');
			if($('#pagination-custom').is(':checked')) { $('form.cpt-settings > .pagination-styles').css('display', 'table'); } 
		}
	    else { 
			$('form.cpt-settings > .loop-display').css('display', 'none');
			$('form.cpt-settings > .pagination-styles').css('display', 'none');
		}
	});
	// List Pagination Custom/Default
	$(document).on( 'click', '.pagination-style-option input.pagination-radio', function() {
	    if($('#pagination-custom').is(':checked')) { $('form.cpt-settings > .pagination-styles').css('display', 'table'); }
	    else { $('form.cpt-settings > .pagination-styles').css('display', 'none'); }
	});	
	// List Pagination Icons
	$(document).on( 'click', '.pagination-icon-option input.icon-radio', function() {
	    if($('#icon_default').is(':checked')) { 
			$('.pagination-styles-icons .fontawesome-link').css('display', 'none');
			$('.pagination-styles-icons .icon-setting').css('display', 'none');
			$('.pagination-styles-icons .icon-default').css('display', 'table-row');
		}
	    if($('#icon_fontawesome').is(':checked')) { 
			$('.pagination-styles-icons .fontawesome-link').css('display', 'block');
			$('.pagination-styles-icons .icon-setting').css('display', 'none');
			$('.pagination-styles-icons .icon-fontawesome').css('display', 'table-row');
		}
	    if($('#icon_upload').is(':checked')) { 
			$('.pagination-styles-icons .fontawesome-link').css('display', 'none');
			$('.pagination-styles-icons .icon-setting').css('display', 'none');
			$('.pagination-styles-icons .icon-upload').css('display', 'table-row');
		}
	});	
	// Single Pagination Custom/Default
	$(document).on( 'click', 'input.cpt-single-pagination', function() {
	    if($('.cpt-single-pagination').is(':checked')) { $('form.cpt-settings > .pagination-single').css('display', 'table'); }
	    else { $('form.cpt-settings > .pagination-single').css('display', 'none'); }
	});
	// Single Pagination Icons
	$(document).on( 'click', '.pagination-single-option input.icon-radio', function() {
	    if($('#icon_default_single').is(':checked')) { 
			$('.pagination-single-icons .fontawesome-link').css('display', 'none');
			$('.pagination-single-icons .icon-setting').css('display', 'none');
			$('.pagination-single-icons .icon-default').css('display', 'table-row');
		}
	    if($('#icon_fontawesome_single').is(':checked')) { 
			$('.pagination-single-icons .fontawesome-link').css('display', 'block');
			$('.pagination-single-icons .icon-setting').css('display', 'none');
			$('.pagination-single-icons .icon-fontawesome').css('display', 'table-row');
		}
	    if($('#icon_upload_single').is(':checked')) { 
			$('.pagination-single-icons .fontawesome-link').css('display', 'none');
			$('.pagination-single-icons .icon-setting').css('display', 'none');
			$('.pagination-single-icons .icon-upload').css('display', 'table-row');
		}
	});		
	
	// Upload Image Buttons
	$(document).on( 'click', '#upload_image_button', function(e) {
		
		e.preventDefault();
		var image_frame;
		
		$(this).addClass('selecting-image');
		
		if(image_frame){
			image_frame.open();
		}
		
		// Define image_frame as wp.media object
		image_frame = wp.media({
			title: 'Select Media',
			multiple : false,
			library : {
			type : 'image',
			}
		});

		image_frame.on('close',function() {
			// On close, get selections and save to the hidden input
			// plus other AJAX stuff to refresh the image preview
			var selection =  image_frame.state().get('selection');
			var gallery_ids = new Array();
			var my_index = 0;

			selection.each(function(attachment) {

				$('.selecting-image').siblings('.image_attachment_id').val(attachment['id']);
				$('.selecting-image').siblings('.image-preview-wrapper').find('img').attr( 'src', attachment['attributes']['url']);
			});
			
			$('.selecting-image').removeClass('selecting-image');
		});

		image_frame.on('open',function() {
			// On open, get the id from the hidden input
			// and select the appropiate images in the media manager
			var selection =  image_frame.state().get('selection');
			ids = $('.selecting-image').siblings('.image_attachment_id').val().split(',');
			ids.forEach(function(id) {
				attachment = wp.media.attachment(id);
				attachment.fetch();
				selection.add( attachment ? [ attachment ] : [] );
			});
		});

		image_frame.open();
	});
	
})(jQuery);