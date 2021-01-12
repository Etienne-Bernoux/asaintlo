<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       sonaar.io
 * @since      1.0.0
 *
 * @package    Sonaar_Music
 * @subpackage Sonaar_Music/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css"/>
<link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.css"/>
<div id="sonaar_music">
  <b-jumbotron class="text-center" bg-variant="dark" text-variant="white">
  <div class="logo"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/sonaar-music-logo-white.png'?>"></div>
  <div class="headertxt">
    <h1>Why MP3 Player Pro</h1>
    <div><p class="text-center tagline">Stunning Sticky Player. Full Support for Elementor Page Builder. Useful Statistic delivered directly in your dashboard ! It's the most advanced Audio Player plugin for WordPress. </p></div>
  </div>
  	</b-jumbotron>

	<b-card-group deck>
		<b-card 
				title="Features you get with Pro version"
				bg-variant="dark"
				text-variant="white"
		        img-alt="Image"
		        img-top
		        tag="article"
		        class="text-center">
		        <div class="sr_it_listgroup">
					<ul>
						<li>Sticky footer audio player with soundwave​</li>
						<li>Continuous audio playback / persistent player</li>
						<li>Elementor widget with 70+ styling options​</li>
						<li>Statistic reports for admin (listen counts, top chart, etc.)</li>
						<li>Get insights reports directly in your dashboard.</li>
						<li>Stunning charts with numbers of plays filtered by days, weeks and months.</li>
						<li>Volume control​</li>
						<li>Shuffle tracks</li>
						<li>Scrollbar options within tracklist</li>
						<li>Option to automatically stop player when track is complete</li>
						<li>1 year of premium support via live chat</li>
						<li>1 year of automatic updates</li>
			         </ul>
		      	</div>
		      	

		        <em slot="footer"><div><a role="button" class="btn btn-primary btn-lg" href="https://goo.gl/mVUJEJ">Learn More</a></div></em>
		    	
		</b-card>
	  	<!--
	  	<b-card
	  			title="Looking for a Music WordPress theme?"
	  			bg-variant="white"
	  			text-variant="white"
	        	img-alt="Image"
	        	img-top
	        	tag="article"
	        	class="text-center">
	          	
				<div><img src="<?php echo plugin_dir_url( __FILE__ ) . '../img/premium-banner-sonaarthemes_smush.png'?>" class="img-fluid" alt="Responsive image"></div>
			    
				<div class="sr_it_listgroup">
				    <ul>
				      	<li>Everything in the MP3 Player Pro, plus:</li>
				      	<li>Continuous Audio Player with Ajax Page Loading</li>
						<li>+25 Stunning Themes for WordPress</li>
						<li>All themes designed for Musicians, Podcasters, Record Labels, Music Producers</li>
						<li>Events and Gigs manager</li>
						<li>Photos and Videos albums including YouTube and Instagram.</li>
						<li>Priority support through our live chat</li>
						<li>and much more!</li>
				    </ul>
				</div>


	   			 <em slot="footer"><div><a role="button" class="btn btn-primary btn-lg" href="https://goo.gl/yTNhXi">Explore our Themes</a></div></em>
	  	</b-card>
-->
	</b-card-group deck>
</div>
