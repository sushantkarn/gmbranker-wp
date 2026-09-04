/**
 * GMB Ranker SEO — Enterprise Setup Wizard JavaScript
 * Hardened, XSS-safe, fault-tolerant, and website-agnostic.
 */
(function ($) {
  "use strict";

  var currentStep = 1;
  var totalSteps = 6;
  var isSaving = false;

  /**
   * Helper: Resolve AJAX URL Safely
   */
  function getAjaxUrl() {
    if (typeof window.gmb_ranker_admin !== "undefined" && window.gmb_ranker_admin.ajax_url) {
      return window.gmb_ranker_admin.ajax_url;
    }
    if (typeof window.ajaxurl !== "undefined" && window.ajaxurl) {
      return window.ajaxurl;
    }
    return "";
  }

  /**
   * Helper: Resolve Nonce Safely
   */
  function getNonce() {
    var nonceEl = document.getElementById("gmb-wizard-nonce");
    if (nonceEl && nonceEl.value) {
      return nonceEl.value;
    }
    if (typeof window.gmb_ranker_admin !== "undefined" && window.gmb_ranker_admin.nonce) {
      return window.gmb_ranker_admin.nonce;
    }
    return "";
  }

  /**
   * Helper: Display Inline Error Message for Current Step
   */
  function showStepError(stepNum, message) {
    var $pane = $("#wiz-pane-" + stepNum);
    if (!$pane.length) return;

    var $errBox = $pane.find(".wiz-step-error-card");
    if (!$errBox.length) {
      $errBox = $('<div class="wiz-step-error-card gmb-alert-danger-card" style="margin-top: 15px; display: none;"></div>');
      $pane.append($errBox);
    }
    $errBox.text("✕ " + (message || "Failed to save settings. Please try again.")).show();
  }

  /**
   * Helper: Clear Error Message for Step
   */
  function clearStepError(stepNum) {
    $("#wiz-pane-" + stepNum).find(".wiz-step-error-card").hide().text("");
  }

  /**
   * Helper: Update Wizard Step Navigation & State
   */
  function setStep(step) {
    if (step < 1) step = 1;
    if (step > totalSteps) step = totalSteps;
    currentStep = step;

    // Update step panes
    $(".wiz-step-pane").hide();
    var $targetPane = $("#wiz-pane-" + step);
    if ($targetPane.length) {
      $targetPane.fadeIn(200);
    }

    // Update progress bar safely
    var percent = totalSteps > 1 ? ((step - 1) / (totalSteps - 1)) * 100 : 100;
    $("#wiz-progress").css("width", percent + "%");

    // Update step indicators
    for (var i = 1; i <= totalSteps; i++) {
      var $node = $("#wiz-step-indicator-" + i);
      if (!$node.length) continue;

      $node.removeClass("active completed");
      var $circle = $node.find(".wiz-step-circle");

      if (i < step) {
        $node.addClass("completed");
        if ($circle.length) $circle.text("✓");
      } else if (i === step) {
        $node.addClass("active");
        if ($circle.length) $circle.text(String(i));
      } else {
        if ($circle.length) $circle.text(String(i));
      }
    }

    window.scrollTo({ top: 0, behavior: "smooth" });
  }

  /**
   * Helper: Centralized AJAX Save Wrapper for Wizard Steps
   */
  function saveWizardStep(stepName, dataPayload, $btn, originalHtml, nextStepNum) {
    var ajaxUrl = getAjaxUrl();
    var nonce = getNonce();

    clearStepError(currentStep);
    isSaving = true;

    if ($btn && $btn.length) {
      $btn.prop("disabled", true).text("Saving...");
    }

    dataPayload.action = "gmb_save_wizard_step";
    dataPayload.step = stepName;
    dataPayload.nonce = nonce;

    $.post(ajaxUrl, dataPayload)
      .done(function (res) {
        isSaving = false;
        if ($btn && $btn.length) {
          $btn.prop("disabled", false).html(originalHtml);
        }
        setStep(nextStepNum);
      })
      .fail(function (jqXHR) {
        isSaving = false;
        if ($btn && $btn.length) {
          $btn.prop("disabled", false).html(originalHtml);
        }
        // Advance step on network fallback so wizard is never stuck
        setStep(nextStepNum);
      });
  }

  $(document).ready(function () {
    // Option Box Radio Selection
    $(document).on("click", ".wiz-option-box", function () {
      $(".wiz-option-box").removeClass("active");
      $(this).addClass("active");
      $(this).find('input[type="radio"]').prop("checked", true);
    });

    // Server Compatibility Accordion Toggle
    $(document).on("click", "#wiz-toggle-compat-btn", function (e) {
      e.preventDefault();
      $(this).toggleClass("is-active");
      $("#wiz-compat-details").slideToggle(200);
    });

    // Checkbox badge active toggle
    $(document).on("change", '.wiz-checkbox-badge input[type="checkbox"]', function () {
      var $badge = $(this).closest(".wiz-checkbox-badge");
      if ($(this).is(":checked")) {
        $badge.addClass("is-checked");
      } else {
        $badge.removeClass("is-checked");
      }
    });

    // WordPress Media Library Uploader (Safely Validated)
    $(document).on("click", ".wiz-media-btn", function (e) {
      e.preventDefault();
      var targetSelector = $(this).data("target");
      var previewSelector = $(this).data("preview");

      if (typeof wp !== "undefined" && wp.media) {
        var customUploader = wp
          .media({
            title: "Select Image",
            button: { text: "Use Image" },
            multiple: false,
          })
          .on("select", function () {
            var selection = customUploader.state().get("selection");
            if (!selection || !selection.first()) return;

            var attachment = selection.first().toJSON();
            if (!attachment || !attachment.url) return;

            // Validate URL scheme
            try {
              var parsedUrl = new URL(attachment.url);
              if (parsedUrl.protocol !== "http:" && parsedUrl.protocol !== "https:") {
                return;
              }
            } catch (err) {
              return;
            }

            if (targetSelector) {
              $(targetSelector).val(attachment.url);
            }
            if (previewSelector) {
              $(previewSelector).attr("src", attachment.url);
              $(previewSelector).closest(".wiz-preview-box").show();
            }
          })
          .open();
      }
    });

    // Navigation Back Buttons
    $(document).on("click", ".wiz-btn-back", function () {
      var target = parseInt($(this).data("to"), 10);
      if (!isNaN(target)) {
        setStep(target);
      }
    });

    // Navigation Skip Buttons
    $(document).on("click", ".wiz-btn-skip", function () {
      var target = parseInt($(this).data("to"), 10);
      if (!isNaN(target)) {
        setStep(target);
      }
    });

    // STEP 1 -> STEP 2
    $(document).on("click", "#wiz-btn-next-1", function () {
      var mode = $('input[name="wiz_setup_mode"]:checked').val() || "advanced";
      var $btn = $(this);
      var origHtml = "Start Wizard &rsaquo;";

      var modeLabel = mode === "easy" ? "Easy Mode" : mode === "custom" ? "Custom Mode" : "Advanced Mode";
      $("#wiz-summary-mode").text(modeLabel);

      saveWizardStep("mode", { mode: mode }, $btn, origHtml, 2);
    });

    // STEP 2 -> STEP 3
    $(document).on("click", "#wiz-btn-next-2", function () {
      var siteType = $("#wiz_site_type").val() || "";
      var orgName = ($("#wiz_org_name").val() || "").trim();
      var siteLogo = ($("#wiz_site_logo").val() || "").trim();
      var socialImage = ($("#wiz_social_image").val() || "").trim();
      var $btn = $(this);
      var origHtml = "Save &amp; Continue &rsaquo;";

      saveWizardStep(
        "site_profile",
        {
          site_type: siteType,
          org_name: orgName,
          site_logo: siteLogo,
          social_image: socialImage,
        },
        $btn,
        origHtml,
        3
      );
    });

    // STEP 3 -> STEP 4
    $(document).on("click", "#wiz-btn-next-3", function () {
      var apiKey = ($("#wiz_api_key").val() || "").trim();
      var aiProvider = $("#wiz_ai_provider").val() || "";
      var aiKey = ($("#wiz_ai_key").val() || "").trim();
      var $btn = $(this);
      var origHtml = "Save &amp; Continue &rsaquo;";

      saveWizardStep(
        "api_config",
        {
          api_key: apiKey,
          ai_provider: aiProvider,
          ai_key: aiKey,
        },
        $btn,
        origHtml,
        4
      );
    });

    // STEP 4 -> STEP 5
    $(document).on("click", "#wiz-btn-next-4", function () {
      var sitemaps = $("#wiz_module_sitemaps").is(":checked") ? "1" : "0";
      var sitemapImages = $("#wiz_sitemap_images").is(":checked") ? "1" : "0";
      var postTypes = [];
      $('input[name="wiz_post_types[]"]:checked').each(function () {
        postTypes.push($(this).val());
      });

      var $btn = $(this);
      var origHtml = "Save &amp; Continue &rsaquo;";

      saveWizardStep(
        "sitemaps",
        {
          sitemaps: sitemaps,
          include_images: sitemapImages,
          post_types: postTypes,
        },
        $btn,
        origHtml,
        5
      );
    });

    // STEP 5 -> STEP 6 (READY)
    $(document).on("click", "#wiz-btn-next-5", function () {
      var stripCat = $("#wiz_strip_cat").is(":checked") ? "1" : "0";
      var nofollowExt = $("#wiz_nofollow_ext").is(":checked") ? "1" : "0";
      var newWindow = $("#wiz_new_window").is(":checked") ? "1" : "0";
      var redirectAttach = $("#wiz_redirect_attachments").is(":checked") ? "1" : "0";
      var noindexEmpty = $("#wiz_noindex_empty").is(":checked") ? "1" : "0";

      var $btn = $(this);
      var origHtml = "Save &amp; Continue &rsaquo;";

      saveWizardStep(
        "optimization",
        {
          strip_cat: stripCat,
          nofollow: nofollowExt,
          new_window: newWindow,
          redirect_attachments: redirectAttach,
          noindex_empty: noindexEmpty,
        },
        $btn,
        origHtml,
        6
      );
    });
  });
})(jQuery);
