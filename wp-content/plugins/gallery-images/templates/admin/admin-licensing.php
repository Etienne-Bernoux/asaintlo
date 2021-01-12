<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$license = array(
    array(
        "title" => "Advanced View Customization",
        "text" => "Unlock all the settings of gallery views, allowing to edit and customize the views, size, effects, buttons, navigation tools, colors and more.",
        "icon" => "-26px -285px"
    ),
    array(
        "title" => "Full Image Configuration",
        "text" => "Unlock the advanced configuration settings, so that you could use the plugin fully, configure all the corners of images and videos to your taste.",
        "icon" => "-132px -288px"
    ),
    array(
        "title" => "Image Resizer Settings",
        "text" => "Unlock the options allowing to play around images, thumbs and edit all the corners of media using advanced resizer settings",
        "icon" => "-229px -286px"
    ),
    array(
        "title" => "Color and Text Styling",
        "text" => "Unlock more options allowing to edit, add or customize every text and color of the plugin with multiple solutions",
        "icon" => "-315px -286px"
    ),
    array(
        "title" => "YouTube Videos",
        "text" => "Of course, it’s great to know that the Gallery is very useful not only for fans of the photos, but as well as for the owners of videos",
        "icon" => "-25px -386px"
    ),
    array(
        "title" => "Lightbox Views Library",
        "text" => "Some view types of our wonderful Gallery uses quite new designed Lightbox/Popup tool and additional 4 Styles for it",
        "icon" => "-141px -383px"
    ), array(
        "title" => "Advanced Lightbox Options",
        "text" => "2 Type of Lightbox with tons of social sharing options, zooming, framing, navigation and sliding effects will make users love the plugin.",
        "icon" => "-243px -384px"
    ),
    array(
        "title" => "Image and Video slideshow",
        "text" => "Showcase Images and Videos in Stunning Slideshows with advanced options, styles and effects",
        "icon" => "-335px -387px"
    ),
    array(
        "title" => "vimeo Videos",
        "text" => "In the paid version apart from photos Gallery plugin it allows you to add links from Vimeo too and connect them with your website",
        "icon" => "-411px -316px"
    )
);
?>


<div class="responsive grid">
    <?php foreach ($license as $key => $val) { ?>
        <div class="col column_1_of_3">
            <div class="header">
                <div class="col-icon" style="background-position: <?php echo $val["icon"]; ?>; ">
                </div>
                <?php echo $val["title"]; ?>
            </div>
            <p><?php echo $val["text"]; ?></p>
            <div class="col-footer">
                <a href="https://goo.gl/NmLZX2" class="a-upgrate">Upgrade</a>
            </div>
        </div>
    <?php } ?>
</div>


<div class="license-footer">
    <p class="footer-text">
        You are using the Lite version of the Image Gallery Plugin for WordPress. If you want to get more awesome
        options,
        advanced features, settings to customize every area of the plugin, then check out the Full License plugin.
        The full version of the plugin is available in 3 different packages of one-time payment.
    </p>
    <p class="this-steps max-width">
        After the purchasing the commercial version follow this steps
    </p>
    <ul class="steps">
        <li>Deactivate Huge IT Image Gallery Plugin</li>
        <li>Delete Huge IT Image Gallery</li>
        <li>Install the downloaded commercial version of the plugin</li>
    </ul>
    <a href="https://goo.gl/NmLZX2" target="_blank">Purchase
        a License</a>
</div>
