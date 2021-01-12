<?php
/**
 * @var $slug string The plugin slug
 */
?>
<div id="<?php echo $slug ?>-deactivation-feedback" class="-hugeit-modal">
    <div class="-hugeit-modal-content">
        <div class="-hugeit-modal-content-header">
            <div class="-hugeit-modal-header-icon">
                <img src="<?php echo GALLERY_IMG_IMAGES_URL . '/admin_images/tracking/plugin-icon.png'; ?>"
                     alt="<?php echo $slug; ?>"/>
            </div>
            <div class="-hugeit-modal-header-info">
                <div class="-hugeit-modal-header-title"><?php _e('We\'re sorry to see you go.', 'hugeit-image-gallery'); ?></div>
                <div class="-hugeit-modal-header-subtitle"><?php _e('Before deactivating and deleting Image Gallery plugin, we\'d love to know why you\'re leaving us.', 'hugeit-image-gallery'); ?></div>
            </div>
            <div class="-hugeit-modal-close"></div>
        </div>
        <div class="-hugeit-modal-content-body">
            <?php wp_nonce_field('hugeit-image-gallery-deactivation-feedback', 'hugeit-image-gallery-deactivation-nonce'); ?>
            <div class="-hugeit-modal-cb">
                <label>
                    <input type="radio" value="useless_and_limited_plugin"
                           name="<?php echo $slug ?>-deactivation-reason"/><span><?php _e('Useless and limited plugin', 'hugeit-image-gallery'); ?></span>
                </label>
            </div>
            <div class="-hugeit-modal-cb">
                <label>
                    <input type="radio" value="found_another_plugin"
                           name="<?php echo $slug ?>-deactivation-reason"/><span><?php _e('Found another plugin', 'hugeit-image-gallery'); ?></span>
                </label>
            </div>
            <div class="-hugeit-modal-cb">
                <label>
                    <input type="radio" value="activating_pro_version"
                           name="<?php echo $slug ?>-deactivation-reason"/><span><?php _e('Activating Pro version', 'hugeit-image-gallery'); ?></span>
                </label>
            </div>
            <div class="-hugeit-modal-cb">
                <label>
                    <input type="radio" value="support_was_bad"
                           name="<?php echo $slug ?>-deactivation-reason"/><span><?php _e('Support was bad', 'hugeit-image-gallery'); ?></span>
                </label>
            </div>
            <div class="-hugeit-modal-cb">
                <label>
                    <input type="radio" value="plugin_does_not_meet_your_expectations"
                           name="<?php echo $slug ?>-deactivation-reason"/><span><?php _e('Plugin doesn\'t meet your expectations', 'hugeit-image-gallery'); ?></span>
                </label>
            </div>
            <div class="-hugeit-modal-textarea">
                <label for="<?php echo $slug; ?>-deactivation-comment"
                       class="-deactivation-feedback-textarea-label"><?php _e('My other reason is', 'hugeit-image-gallery'); ?></label>
                <textarea name="<?php echo $slug; ?>-deactivation-comment"
                          id="<?php echo $slug; ?>-deactivation-comment"></textarea>
            </div>
        </div>
        <div class="-hugeit-modal-content-footer">
            <a href="#"
               class="hugeit-deactivate-plugin-image-gallery"><?php _e('Deactivate', 'hugeit-image-gallery') ?></a>
            <a href="#"
               class="hugeit-cancel-deactivation-image-gallery"><?php _e('Cancel', 'hugeit-image-gallery') ?></a>
        </div>
    </div>
</div>