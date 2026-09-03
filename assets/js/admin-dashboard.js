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

  // Instant Indexing Global Handlers
  window.gmbSubmitInstantIndexing = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    var urlsEl = document.getElementById("gmb_indexing_urls");
    var urls = urlsEl ? urlsEl.value : "";
    var actionInput = document.querySelector('input[name="gmb_api_action"]:checked');
    var action = actionInput ? actionInput.value : "bing_submit";
    var nonceEl = document.getElementById("gmb_instant_nonce");
    var nonce = nonceEl ? nonceEl.value : "";

    var btn = document.getElementById("gmb-indexing-submit-btn");
    var spinner = document.getElementById("gmb-indexing-spinner");
    var respBox = document.getElementById("gmb-indexing-response-box");
    var respMsg = document.getElementById("gmb-indexing-response-msg");
    var rawJson = document.getElementById("gmb-indexing-raw-json");

    if (btn) btn.disabled = true;
    if (spinner) spinner.style.display = "inline";
    if (respBox) respBox.style.display = "none";

    var formData = new FormData();
    formData.append("action", "gmb_instant_indexing_submit");
    formData.append("nonce", nonce);
    formData.append("urls", urls);
    formData.append("api_action", action);

    var ajaxUrl = typeof ajaxurl !== "undefined" ? ajaxurl : (typeof gmb_ranker_admin !== "undefined" ? gmb_ranker_admin.ajax_url : "");

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = "none";
        if (respBox) respBox.style.display = "block";

        if (data.success && respMsg) {
          respMsg.innerHTML = '<div class="gmb-alert-success-card">&check; Request successfully submitted! ' + (data.data && data.data.message ? data.data.message : '') + '</div>';
          if (rawJson) rawJson.value = JSON.stringify(data.data, null, 2);
        } else if (respMsg) {
          var err = (data.data && data.data.error) ? data.data.error : 'Submission error.';
          respMsg.innerHTML = '<div class="gmb-alert-danger-card">&cross; ' + err + '</div>';
          if (rawJson) rawJson.value = JSON.stringify(data, null, 2);
        }
      })
      .catch(function (err) {
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = "none";
        if (respBox) respBox.style.display = "block";
        if (respMsg) respMsg.innerHTML = '<div class="gmb-alert-danger-card">Network error: ' + err + '</div>';
      });

    return false;
  };

  window.gmbHandleGoogleJsonFileUpload = function (fileInput) {
    if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
    var file = fileInput.files[0];
    var reader = new FileReader();
    reader.onload = function (e) {
      try {
        var content = e.target.result;
        var parsed = JSON.parse(content);
        if (!parsed.client_email || !parsed.private_key) {
          alert("Invalid Google Service Account JSON: Missing client_email or private_key.");
          return;
        }
        var textarea = document.getElementById("gmb_ranker_google_json_key_field");
        if (textarea) textarea.value = JSON.stringify(parsed, null, 2);
        var badge = document.getElementById("gmb_google_json_upload_badge");
        if (badge) {
          badge.style.display = "block";
          badge.innerHTML = "&check; Loaded Service Account Key for: <strong>" + parsed.client_email + "</strong>";
        }
      } catch (err) {
        alert("Failed to parse JSON file: " + err.message);
      }
    };
    reader.readAsText(file);
  };

  window.gmbResetIndexNowKey = function () {
    if (!confirm("Are you sure you want to generate a new IndexNow API key?")) return;
    var nonceEl = document.getElementById("gmb_instant_nonce");
    var nonce = nonceEl ? nonceEl.value : "";
    var fd = new FormData();
    fd.append("action", "gmb_instant_indexing_reset_key");
    fd.append("nonce", nonce);

    var ajaxUrl = typeof ajaxurl !== "undefined" ? ajaxurl : (typeof gmb_ranker_admin !== "undefined" ? gmb_ranker_admin.ajax_url : "");

    fetch(ajaxUrl, { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success && res.data) {
          var field = document.getElementById("gmb_indexnow_key_field");
          if (field) field.value = res.data.key;
          alert("IndexNow API Key successfully regenerated.");
        }
      });
  };

  window.gmbClearIndexNowHistory = function () {
    if (!confirm("Clear all IndexNow submission logs?")) return;
    var nonceEl = document.getElementById("gmb_instant_nonce");
    var nonce = nonceEl ? nonceEl.value : "";
    var fd = new FormData();
    fd.append("action", "gmb_instant_indexing_clear_history");
    fd.append("nonce", nonce);

    var ajaxUrl = typeof ajaxurl !== "undefined" ? ajaxurl : (typeof gmb_ranker_admin !== "undefined" ? gmb_ranker_admin.ajax_url : "");

    fetch(ajaxUrl, { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res.success) location.reload();
      });
  };

  window.gmbUpdateIndexBtnText = function () {
    var actionInput = document.querySelector('input[name="gmb_api_action"]:checked');
    var labelSpan = document.getElementById("gmb-submit-btn-label");
    if (!actionInput || !labelSpan) return;
    if (actionInput.value === "bing_submit") {
      labelSpan.textContent = "Submit to IndexNow";
    } else if (actionInput.value === "remove") {
      labelSpan.textContent = "Remove from Google Index";
    } else if (actionInput.value === "getstatus") {
      labelSpan.textContent = "Get Google URL Status";
    } else {
      labelSpan.textContent = "Submit to Google API";
    }
  };

  // Change Username Modal Handler
  function syncCurrentLogin() {
    var userSelect = document.getElementById('gmb-modal-user-select');
    var currentInput = document.getElementById('gmb-modal-current-username');
    if (!userSelect || !currentInput) return;
    var opt = userSelect.options[userSelect.selectedIndex];
    if (opt) {
      currentInput.value = opt.getAttribute('data-login') || opt.textContent.trim();
    }
  }

  $(document).on("change", "#gmb-modal-user-select", syncCurrentLogin);

  $(document).on("click", ".gmb-open-change-username-modal-btn, #gmb-sec-trigger-rename-btn", function (e) {
    e.preventDefault();
    var modal = document.getElementById("gmb-change-username-modal");
    if (!modal) return;
    modal.classList.add("is-active");

    var uname = $(this).data("username");
    var userSelect = document.getElementById("gmb-modal-user-select");
    if (!uname && this.id === "gmb-sec-trigger-rename-btn") {
      var sel = document.getElementById("gmb-sec-select-user");
      uname = sel ? sel.options[sel.selectedIndex].getAttribute("data-login") : null;
    }

    if (userSelect && uname) {
      for (var i = 0; i < userSelect.options.length; i++) {
        if (userSelect.options[i].getAttribute("data-login") === uname) {
          userSelect.selectedIndex = i;
          break;
        }
      }
    }
    syncCurrentLogin();
  });

  $(document).on("click", "#gmb-close-username-modal, #gmb-cancel-username-modal", function (e) {
    e.preventDefault();
    $("#gmb-change-username-modal").removeClass("is-active");
  });

  $(document).on("click", "#gmb-submit-username-modal", function (e) {
    e.preventDefault();
    var userSelect = document.getElementById("gmb-modal-user-select");
    var newInput = document.getElementById("gmb-modal-new-username");
    var errorSpan = document.getElementById("gmb-modal-username-error");
    var submitBtn = document.getElementById("gmb-submit-username-modal");

    var newName = newInput ? newInput.value.trim() : "";
    if (!newName) {
      if (errorSpan) {
        errorSpan.textContent = "Please enter a valid new username.";
        errorSpan.style.display = "block";
      }
      return;
    }

    var userId = userSelect ? userSelect.value : "";
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Updating...";
    }
    if (errorSpan) errorSpan.style.display = "none";

    var formData = new FormData();
    formData.append("action", "gmb_change_username");
    formData.append("user_id", userId);
    formData.append("new_username", newName);
    var nonce = (window.gmb_ranker_admin && window.gmb_ranker_admin.nonce) ? window.gmb_ranker_admin.nonce : "";
    formData.append("nonce", nonce);

    var ajaxUrl = typeof ajaxurl !== "undefined" ? ajaxurl : (typeof gmb_ranker_admin !== "undefined" ? gmb_ranker_admin.ajax_url : "/wp-admin/admin-ajax.php");

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Update Username";
        }
        if (data && data.success) {
          alert(data.data.message || "Username updated successfully!");
          window.location.reload();
        } else if (errorSpan) {
          errorSpan.textContent = (data && data.data && data.data.message) ? data.data.message : "Error updating username.";
          errorSpan.style.display = "block";
        }
      })
      .catch(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Update Username";
        }
        if (errorSpan) {
          errorSpan.textContent = "Network error occurred.";
          errorSpan.style.display = "block";
        }
      });
  });

  $(document).on("click", "#gmb-auto-fix-display-name-btn", function (e) {
    e.preventDefault();
    var autoFixBtn = this;
    autoFixBtn.disabled = true;
    autoFixBtn.textContent = "Fixing...";

    var formData = new FormData();
    formData.append("action", "gmb_auto_fix_display_names");
    var nonce = (window.gmb_ranker_admin && window.gmb_ranker_admin.nonce) ? window.gmb_ranker_admin.nonce : "";
    formData.append("nonce", nonce);

    var ajaxUrl = typeof ajaxurl !== "undefined" ? ajaxurl : (typeof gmb_ranker_admin !== "undefined" ? gmb_ranker_admin.ajax_url : "/wp-admin/admin-ajax.php");

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success) {
          var card = document.getElementById("gmb-display-name-risk-card");
          if (card) {
            card.style.transition = "opacity 0.3s ease";
            card.style.opacity = "0";
            setTimeout(function () { card.remove(); }, 300);
          }
          alert("Public display names updated successfully!");
        } else {
          autoFixBtn.disabled = false;
          autoFixBtn.textContent = "Auto-Fix Display Name";
          alert("Failed to auto-fix display names.");
        }
      })
      .catch(function () {
        autoFixBtn.disabled = false;
        autoFixBtn.textContent = "Auto-Fix Display Name";
        alert("Network error occurred.");
      });
  });
})(jQuery);
