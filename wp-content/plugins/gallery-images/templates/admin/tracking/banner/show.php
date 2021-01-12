<?php
/**
 * @var $optin_url string
 * @var $optout_url string
 */
?>
<div class="hugeit-tracking-optin-image-gallery">
    <div class="hugeit-tracking-optin-image-gallery-left">
        <div class="hugeit-tracking-optin-image-gallery-icon"><img
                    src="<?php echo GALLERY_IMG_IMAGES_URL . '/admin_images/tracking/plugin-icon.png'; ?>"
                    alt="<?php echo Gallery_Img()->get_slug() ?>"/></div>
        <div class="hugeit-tracking-optin-image-gallery-info">
            <div class="hugeit-tracking-optin-image-gallery-header"><?php _e('Let us know how you wish to better this plugin! ', 'hugeit-image-gallery'); ?></div>
            <div class="hugeit-tracking-optin-image-gallery-description"><?php _e('Allow us to email you and ask how you like our plugin and what issues we may fix or add in the future. We collect <a href="http://huge-it.com/privacy-policy/#collected_data_from_plugins" target="_blank">basic data</a>, in order to help the community to improve the quality of the plugin for you. Data will never be shared with any third party.', 'hugeit-image-gallery'); ?></div>
            <div>
                <a href="<?php echo $optin_url; ?>"
                   class="hugeit-tracking-optin-image-gallery-button"><?php _e('Yes, sure', 'hugeit-image-gallery'); ?></a><a
                        href="<?php echo $optout_url; ?>"
                        class="hugeit-tracking-optout-button"><?php _e('No, thanks', 'hugeit-image-gallery'); ?></a>
            </div>
        </div>
    </div>
    <div class="hugeit-tracking-optin-image-gallery-right">
        <div class="hugeit-tracking-optin-image-gallery-logo">
            <img src="<?php echo GALLERY_IMG_IMAGES_URL . '/admin_images/tracking/logo.png'; ?>" alt="Huge-IT"/>
        </div>
        <div class="hugeit-tracking-optin-image-gallery-links">
            <a href="http://huge-it.com/privacy-policy/#collected_data_from_plugins"
               target="_blank"><?php _e('What data We Collect', 'hugeit-image-gallery'); ?></a>
            <a href="https://huge-it.com/privacy-policy"
               target="_blank"><?php _e('Privacy Policy', 'hugeit-image-gallery'); ?></a>
        </div>
    </div>
</div>