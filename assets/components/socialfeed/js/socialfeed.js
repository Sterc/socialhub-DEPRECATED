$(document).ready(function() {
	
	if($("#socialfeed.sf-wall").length != 0){
		var $container = $('#socialfeed');

		// initialize Masonry after all images have loaded  
		$container.imagesLoaded( function() {
	    	$container.masonry({
	    		columnWidth: '.grid-sizer',
	    		itemSelector: '.masonry-item',
	    		percentPosition: true
	    	});
		});
	}

	$('.sf-filterlist li a').click(function(event){
		event.preventDefault();
		var channel = $(this).attr('class');

		var hasInactive = false;
		$(".sf-filterlist li a").each(function(){
			if($(this).parent().hasClass("inactive")){
				hasInactive = true;
				return true;
			}

		});
		
		function toggleSocialFeed() {

				$('#sf-inner .sf-item.' + channel).parent().show();	
				$('#sf-inner .sf-item:not(.' + channel + ')').parent().hide();	

				$('.sf-filterlist li a:not(.' + channel + ')').parent().addClass('inactive');
				$('.sf-filterlist li a.' + channel).parent().removeClass('inactive');
		}

		if($('#socialfeed').length != 0){

			if(hasInactive === true){
				console.log('test');
				if($(this).parent().hasClass("inactive") === false ){
					$(".sf-filterlist li").removeClass("inactive");
					$('#sf-inner .sf-item').parent().show();	
				}
				else {
					toggleSocialFeed();
				}
			}else {
				toggleSocialFeed();
			}
			
		}
		
		if($("#socialfeed.sf-wall").length != 0){
			$container.masonry('layout');
		}

	});

});
