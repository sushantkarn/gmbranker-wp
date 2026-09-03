/**
 * GMB Ranker SEO — Admin Dashboard JavaScript
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Sidebar Sub-tab switching
    $(document).on("click", ".gmb-sidebar-nav-item", function (e) {
      e.preventDefault();
      var targetSub = $(this).attr("data-subtab") || $(this).data("subtab");
      if (!targetSub) return;

      var $container = $(this).closest(".gmb-sidebar-layout-container");
      if (!$container.length) {
        $container = $(document);
      }

      var $nav = $(this).closest(".gmb-sidebar-nav");
      if ($nav.length) {
        $nav.find(".gmb-sidebar-nav-item").removeClass("active");
      } else {
        $(".gmb-sidebar-nav-item").removeClass("active");
      }
      $(this).addClass("active");

      // Hide all subtab panels within container
      $container
        .find(".gmb-subtab-panel")
        .removeClass("active is-active")
        .removeAttr("style")
        .hide();

      // Show selected panel
      var $target = $("#" + targetSub);
      if ($target.length) {
        $target
          .addClass("active")
          .attr("style", "display: block !important;")
          .show();
      }

      // Sync URL parameter
      if (window.history && window.history.replaceState) {
        var cleanSub = targetSub
          .replace("gmb-subtab-sitemap-", "")
          .replace("gmb-subtab-schema-", "")
          .replace("gmb-subtab-", "");
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set("tab", cleanSub);
        window.history.replaceState({}, "", currentUrl.toString());
      }
    });

    // Type Button (Segmented Button / Radio) toggles
    $(document).on(
      "change",
      '.gmb-type-selector input[type="radio"]',
      function () {
        var $group = $(this).closest(".gmb-type-selector");
        $group.find(".gmb-type-btn").removeClass("active");
        $(this).closest(".gmb-type-btn").addClass("active");
      },
    );

    // Toggle Password Key Visibility
    $(document).on("click", "#gmb-toggle-key-visibility", function (e) {
      e.preventDefault();
      var $input = $("#gmb_ranker_api_key_input");
      if ($input.attr("type") === "password") {
        $input.attr("type", "text");
        $(this).text("Hide");
      } else {
        $input.attr("type", "password");
        $(this).text("Show");
      }
    });

    // AI Provider Section Switching
    $(document).on("change", "#gmb_ai_provider_select", function () {
      var val = $(this).val();
      $(".gmb-ai-section").hide();
      $("#ai-section-" + val).show();
    });

    // Copy Webhook URL
    $(document).on("click", "#gmb-copy-webhook-btn", function (e) {
      e.preventDefault();
      var $input = $("#gmb_webhook_endpoint");
      $input.select();
      if (navigator.clipboard) {
        navigator.clipboard.writeText($input.val());
      } else {
        document.execCommand("copy");
      }
      var $btn = $(this);
      var origText = $btn.text();
      $btn.text("Copied!");
      setTimeout(function () {
        $btn.text(origText);
      }, 2000);
    });

    // Generate IndexNow Key
    $(document).on("click", "#gmb-generate-indexnow-key", function (e) {
      e.preventDefault();
      var chars = "0123456789abcdef";
      var key = "";
      for (var i = 0; i < 32; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      $("#gmb_indexnow_key_input").val(key);
    });

    // Status & Tools Subtabs
    $(document).on("click", ".gmb-tools-tab-btn", function (e) {
      e.preventDefault();
      var targetTab = $(this).data("tab");
      if (!targetTab) return;

      $(".gmb-tools-tab-btn").removeClass("active");
      $(this).addClass("active");

      $(".gmb-tools-content-panel").hide();
      $("#" + targetTab).show();
    });

    // File picker name change
    $(document).on("change", "#gmb-restore-file-input", function () {
      var fileName =
        this.files && this.files[0] ? this.files[0].name : "No file chosen";
      $("#gmb-restore-filename").text(fileName);
    });
  });

  // Global Live Analytics Sync handler
  window.gmbSyncAnalytics = function () {
    var $btn = $("#gmb-sync-analytics-btn");
    var $label = $("#gmb-sync-btn-label");
    if (!$btn.length) return;

    $btn.prop("disabled", true);
    $label.text("Syncing Cloud Data...");

    var ajaxUrl =
      typeof gmb_ranker_admin !== "undefined" && gmb_ranker_admin.ajax_url
        ? gmb_ranker_admin.ajax_url
        : "/wp-admin/admin-ajax.php";
    var nonce =
      typeof gmb_ranker_admin !== "undefined" && gmb_ranker_admin.nonce
        ? gmb_ranker_admin.nonce
        : "";

    $.ajax({
      url: ajaxUrl,
      type: "POST",
      data: {
        action: "gmb_refresh_analytics",
        nonce: nonce,
      },
      success: function (response) {
        $btn.prop("disabled", false);
        $label.text("Sync Live Data");
        if (response.success && response.data && response.data.data) {
          var d = response.data.data;
          if (d.totals) {
            if (d.totals.clicks)
              $("#gmb-kpi-clicks").text(
                Number(d.totals.clicks).toLocaleString(),
              );
            if (d.totals.impressions)
              $("#gmb-kpi-impressions").text(
                Number(d.totals.impressions).toLocaleString(),
              );
            if (d.totals.ctr) $("#gmb-kpi-ctr").text(d.totals.ctr + "%");
            if (d.totals.position) $("#gmb-kpi-pos").text(d.totals.position);
          }
          if (d.status === "connected") {
            $("#gmb-analytics-status-badge")
              .removeClass("gmb-analytics-badge-preview")
              .addClass("gmb-analytics-badge-connected")
              .html(
                '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg> Live Cloud Sync',
              );
          }
          $label.text("Synced!");
          setTimeout(function () {
            $label.text("Sync Live Data");
          }, 2000);
        }
      },
      error: function () {
        $btn.prop("disabled", false);
        $label.text("Sync Live Data");
      },
    });
  };
})(jQuery);
