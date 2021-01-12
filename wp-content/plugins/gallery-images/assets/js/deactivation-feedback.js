/**
 * Created by User on 6/17/2017.
 */
"use strict";
jQuery(document).ready(function () {

    var confirmDeactivationLink = jQuery(".hugeit-deactivate-plugin-image-gallery"),
        cancelDeactivationLink = jQuery(".hugeit-cancel-deactivation-image-gallery"),
        deactivationURL;


    jQuery('body').on('click', '#the-list tr[data-slug=' + hugeitImagegalleryL10n.slug + '] .deactivate a', function (e) {
        e.preventDefault();

        hugeitModalGallery.show(hugeitImagegalleryL10n.slug + '-deactivation-feedback');
        deactivationURL = jQuery(this).attr('href');

        return false;
    });

    confirmDeactivationLink.on('click', function (e) {
        e.preventDefault();

        var checkedOption = jQuery('input[name=' + hugeitImagegalleryL10n.slug + '-deactivation-reason]:checked'),
            comment = jQuery('textarea[name=' + hugeitImagegalleryL10n.slug + '-deactivation-comment]').val(),
            nonce = jQuery('#hugeit-image-gallery-deactivation-nonce').val();
        if (checkedOption.length || comment.length) {
            hugeitModalGallery.hide(hugeitImagegalleryL10n.slug + '-deactivation-feedback');
            sendDeactivationFeedback(checkedOption.val(), comment, nonce);
            setTimeout(function () {
                window.location.replace(deactivationURL);
            }, 0);
        } else {
            hugeitModalGallery.hide(hugeitImagegalleryL10n.slug + '-deactivation-feedback');
            window.location.replace(deactivationURL);
        }

        return false;
    });

    cancelDeactivationLink.on('click', function (e) {
        e.preventDefault();

        hugeitModalGallery.hide(hugeitImagegalleryL10n.slug + '-deactivation-feedback');

        return false;
    });

    function sendDeactivationFeedback(v, c, n) {
        jQuery.ajax({
            url: ajaxurl,
            method: 'post',
            data: {
                action: 'hugeit_image_gallery_deactivation_feedback',
                value: v,
                comment: c,
                nonce: n
            }
        });
    }
});