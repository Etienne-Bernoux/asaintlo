
(function ($) {
	"use strict";
	var isEditMode = false;
	$(window).on('elementor/frontend/init', function () {

		//Apply in the Editor mode only
		if ( elementorFrontend.isEditMode() ) { 
			isEditMode = true; 
			//playerLoadedinEditMode = false;
			//Load Music Player content in the Editor mode

			elementorFrontend.hooks.addAction( 'frontend/element_ready/music-player.default', function( $scope ) {
				var elementorWidgetID = $scope[0].dataset.id
				if ( typeof setIronAudioplayers == 'function') {
					$('.iron-audioplayer wave').remove();
					setIronAudioplayers('.elementor-widget-music-player[data-id="' + elementorWidgetID +'"]');
				}
			});	
		}
	});

}(jQuery));






			
		


