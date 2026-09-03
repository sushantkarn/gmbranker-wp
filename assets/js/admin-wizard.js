/**
 * GMB Ranker SEO — Setup Wizard JavaScript
 */
(function($) {
    'use strict';

    var currentStep = 1;
    var totalSteps = 6;

    function setStep(step) {
        if (step < 1) step = 1;
        if (step > totalSteps) step = totalSteps;
        currentStep = step;

        // Update step panes
        $('.wiz-step-pane').hide();
        $('#wiz-pane-' + step).fadeIn(200);

        // Update progress bar
        var percent = ((step - 1) / (totalSteps - 1)) * 100;
        $('#wiz-progress').css('width', percent + '%');

        // Update step indicators
        for (var i = 1; i <= totalSteps; i++) {
            var $node = $('#wiz-step-indicator-' + i);
            $node.removeClass('active completed');
            if (i < step) {
                $node.addClass('completed');
                $node.find('.wiz-step-circle').html('');
            } else if (i === step) {
                $node.addClass('active');
                $node.find('.wiz-step-circle').text(i);
            } else {
                $node.find('.wiz-step-circle').text(i);
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $(document).ready(function() {
        var wizardNonce = $('#gmb-wizard-nonce').val() || (typeof gmb_ranker_admin !== 'undefined' ? gmb_ranker_admin.nonce : '');
        var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : (typeof gmb_ranker_admin !== 'undefined' ? gmb_ranker_admin.ajax_url : '');

        // Option Box Radio Selection
        $('.wiz-option-box').on('click', function() {
            $('.wiz-option-box').removeClass('active');
            $(this).addClass('active');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Server Compatibility Accordion Toggle
        $('#wiz-toggle-compat-btn').on('click', function(e) {
            e.preventDefault();
            $(this).toggleClass('is-active');
            $('#wiz-compat-details').slideToggle(200);
        });

        // Checkbox badge active toggle
        $('.wiz-checkbox-badge input[type="checkbox"]').on('change', function() {
            var $badge = $(this).closest('.wiz-checkbox-badge');
            if ($(this).is(':checked')) {
                $badge.addClass('is-checked');
            } else {
                $badge.removeClass('is-checked');
            }
        });

        // WordPress Media Library Uploader
        $('.wiz-media-btn').on('click', function(e) {
            e.preventDefault();
            var targetInput = $(this).data('target');
            var previewImg = $(this).data('preview');

            if (typeof wp !== 'undefined' && wp.media) {
                var customUploader = wp.media({
                    title: 'Select Image',
                    button: { text: 'Use Image' },
                    multiple: false
                }).on('select', function() {
                    var attachment = customUploader.state().get('selection').first().toJSON();
                    $(targetInput).val(attachment.url);
                    if (previewImg) {
                        $(previewImg).attr('src', attachment.url);
                        $(previewImg).closest('.wiz-preview-box').show();
                    }
                }).open();
            }
        });

        // Navigation Back Buttons
        $('.wiz-btn-back').on('click', function() {
            var target = parseInt($(this).data('to'), 10);
            setStep(target);
        });

        // Navigation Skip Buttons
        $('.wiz-btn-skip').on('click', function() {
            var target = parseInt($(this).data('to'), 10);
            setStep(target);
        });

        // STEP 1 -> STEP 2
        $('#wiz-btn-next-1').on('click', function() {
            var mode = $('input[name="wiz_setup_mode"]:checked').val() || 'advanced';
            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post(ajaxUrl, {
                action: 'gmb_save_wizard_step',
                step: 'mode',
                mode: mode,
                nonce: wizardNonce
            }, function(res) {
                $btn.prop('disabled', false).html('Start Wizard &rsaquo;');
                var modeLabel = (mode === 'easy') ? 'Easy Mode' : (mode === 'custom' ? 'Custom Mode' : 'Advanced Mode');
                $('#wiz-summary-mode').text(modeLabel);
                setStep(2);
            }).fail(function() {
                $btn.prop('disabled', false).html('Start Wizard &rsaquo;');
                setStep(2);
            });
        });

        // STEP 2 -> STEP 3
        $('#wiz-btn-next-2').on('click', function() {
            var siteType = $('#wiz_site_type').val();
            var orgName = $('#wiz_org_name').val();
            var siteLogo = $('#wiz_site_logo').val();
            var socialImage = $('#wiz_social_image').val();
            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post(ajaxUrl, {
                action: 'gmb_save_wizard_step',
                step: 'site_profile',
                site_type: siteType,
                org_name: orgName,
                site_logo: siteLogo,
                social_image: socialImage,
                nonce: wizardNonce
            }, function(res) {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(3);
            }).fail(function() {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(3);
            });
        });

        // STEP 3 -> STEP 4
        $('#wiz-btn-next-3').on('click', function() {
            var apiKey = $('#wiz_api_key').val();
            var aiProvider = $('#wiz_ai_provider').val();
            var aiKey = $('#wiz_ai_key').val();
            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post(ajaxUrl, {
                action: 'gmb_save_wizard_step',
                step: 'api_config',
                api_key: apiKey,
                ai_provider: aiProvider,
                ai_key: aiKey,
                nonce: wizardNonce
            }, function(res) {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(4);
            }).fail(function() {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(4);
            });
        });

        // STEP 4 -> STEP 5
        $('#wiz-btn-next-4').on('click', function() {
            var sitemaps = $('#wiz_module_sitemaps').is(':checked') ? '1' : '0';
            var sitemapImages = $('#wiz_sitemap_images').is(':checked') ? '1' : '0';
            var postTypes = [];
            $('input[name="wiz_post_types[]"]:checked').each(function() {
                postTypes.push($(this).val());
            });

            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post(ajaxUrl, {
                action: 'gmb_save_wizard_step',
                step: 'sitemaps',
                sitemaps: sitemaps,
                include_images: sitemapImages,
                post_types: postTypes,
                nonce: wizardNonce
            }, function(res) {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(5);
            }).fail(function() {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(5);
            });
        });

        // STEP 5 -> STEP 6 (READY)
        $('#wiz-btn-next-5').on('click', function() {
            var stripCat = $('#wiz_strip_cat').is(':checked') ? '1' : '0';
            var nofollowExt = $('#wiz_nofollow_ext').is(':checked') ? '1' : '0';
            var newWindow = $('#wiz_new_window').is(':checked') ? '1' : '0';
            var redirectAttach = $('#wiz_redirect_attachments').is(':checked') ? '1' : '0';
            var noindexEmpty = $('#wiz_noindex_empty').is(':checked') ? '1' : '0';

            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.post(ajaxUrl, {
                action: 'gmb_save_wizard_step',
                step: 'optimization',
                strip_cat: stripCat,
                nofollow: nofollowExt,
                new_window: newWindow,
                redirect_attachments: redirectAttach,
                noindex_empty: noindexEmpty,
                nonce: wizardNonce
            }, function(res) {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(6);
            }).fail(function() {
                $btn.prop('disabled', false).html('Save &amp; Continue &rsaquo;');
                setStep(6);
            });
        });

    });

})(jQuery);
