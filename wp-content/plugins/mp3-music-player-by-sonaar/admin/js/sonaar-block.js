jQuery( document ).on('change', '#inspector-select-control-0, #inspector-toggle-control-0, #inspector-toggle-control-1, #inspector-toggle-control-2, #inspector-toggle-control-3, #inspector-toggle-control-4', function (e) {
    setTimeout(function(){ 
        // jQuery('#inspector-select-control-0').select2();
        
		IRON.players = []
		jQuery('.iron-audioplayer').each(function(){

			var player = Object.create(  IRON.audioPlayer )
			player.init(jQuery(this))

			IRON.players.push(player)
		})
	 }, 2500);
});

// jQuery(document).ready(function() {
//     jQuery('#inspector-select-control-0').select2();
// });

//Load Music player Content
function setIronAudioplayers(){
	

	setTimeout(function(){ 
		IRON.players = []
		jQuery('.iron-audioplayer').each(function(){

			var player = Object.create(  IRON.audioPlayer )
			player.init(jQuery(this))

			IRON.players.push(player)
		})
	 }, 4000);
  
}

setIronAudioplayers();
