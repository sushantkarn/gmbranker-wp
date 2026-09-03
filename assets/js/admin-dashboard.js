/**
 * GMB Ranker SEO — Enterprise Admin Dashboard JavaScript
 * Hardened, XSS-safe, race-condition resistant, and 100% website-agnostic.
 */
(function ($) {
  "use strict";

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
  function getNonce(overrideId) {
    if (overrideId) {
      var el = document.getElementById(overrideId);
      if (el && el.value) {
        return el.value;
      }
    }
    if (typeof window.gmb_ranker_admin !== "undefined" && window.gmb_ranker_admin.nonce) {
      return window.gmb_ranker_admin.nonce;
    }
    return "";
  }

  /**
   * Helper: Cryptographically Secure Random String
   */
  function generateSecureRandomKey(length) {
    length = length || 32;
    var chars = "0123456789abcdef";
    var result = "";
    if (window.crypto && window.crypto.getRandomValues) {
      var values = new Uint8Array(length);
      window.crypto.getRandomValues(values);
      for (var i = 0; i < length; i++) {
        result += chars.charAt(values[i] % chars.length);
      }
      return result;
    }
    // Fallback if crypto API is unavailable
    for (var j = 0; j < length; j++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }

  /**
   * Helper: Safe Alert Box Renderer (XSS-Safe)
   */
  function renderAlertBox(container, message, type) {
    if (!container) return;
    container.textContent = "";

    var card = document.createElement("div");
    card.className = type === "success" ? "gmb-alert-success-card" : "gmb-alert-danger-card";

    var iconSpan = document.createElement("span");
    iconSpan.style.marginRight = "6px";
    iconSpan.textContent = type === "success" ? "✓ " : "✕ ";
    card.appendChild(iconSpan);

    var textSpan = document.createElement("span");
    textSpan.textContent = message || (type === "success" ? "Operation completed." : "An error occurred.");
    card.appendChild(textSpan);

    container.appendChild(card);
    container.style.display = "block";
  }

  /**
   * Helper: Validate and Sanitize URLs for Indexing
   */
  function sanitizeIndexingUrls(rawInput) {
    if (!rawInput || typeof rawInput !== "string") return [];
    var lines = rawInput.split(/\r?\n/);
    var validUrls = [];
    for (var i = 0; i < lines.length; i++) {
      var trimmed = lines[i].trim();
      if (!trimmed) continue;
      try {
        var parsed = new URL(trimmed);
        if (parsed.protocol === "http:" || parsed.protocol === "https:") {
          validUrls.push(parsed.toString());
        }
      } catch (err) {
        // Skip invalid URL schemes
      }
    }
    return validUrls;
  }

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
        try {
          var currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set("tab", cleanSub);
          window.history.replaceState({}, "", currentUrl.toString());
        } catch (err) {
          // Ignore URL manipulation failures
        }
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

    // Copy Webhook URL (Safely)
    $(document).on("click", "#gmb-copy-webhook-btn", function (e) {
      e.preventDefault();
      var $input = $("#gmb_webhook_endpoint");
      var val = $input.val();
      var $btn = $(this);
      var origText = $btn.text();

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard
          .writeText(val)
          .then(function () {
            $btn.text("Copied!");
            setTimeout(function () {
              $btn.text(origText);
            }, 2000);
          })
          .catch(function () {
            $input.select();
            document.execCommand("copy");
            $btn.text("Copied!");
            setTimeout(function () {
              $btn.text(origText);
            }, 2000);
          });
      } else {
        $input.select();
        document.execCommand("copy");
        $btn.text("Copied!");
        setTimeout(function () {
          $btn.text(origText);
        }, 2000);
      }
    });

    // Generate IndexNow Key
    $(document).on("click", "#gmb-generate-indexnow-key", function (e) {
      e.preventDefault();
      var key = generateSecureRandomKey(32);
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

  // Global Live Analytics Sync handler (Guard against concurrency)
  var isAnalyticsSyncing = false;
  window.gmbSyncAnalytics = function () {
    if (isAnalyticsSyncing) return;

    var $btn = $("#gmb-sync-analytics-btn");
    var $label = $("#gmb-sync-btn-label");
    if (!$btn.length) return;

    isAnalyticsSyncing = true;
    $btn.prop("disabled", true);
    $label.text("Syncing Cloud Data...");

    var ajaxUrl = getAjaxUrl();
    var nonce = getNonce();

    if (!ajaxUrl) {
      isAnalyticsSyncing = false;
      $btn.prop("disabled", false);
      $label.text("Sync Live Data");
      return;
    }

    $.ajax({
      url: ajaxUrl,
      type: "POST",
      data: {
        action: "gmb_refresh_analytics",
        nonce: nonce,
      },
      success: function (response) {
        isAnalyticsSyncing = false;
        $btn.prop("disabled", false);
        $label.text("Sync Live Data");

        if (response && response.success && response.data && response.data.data) {
          var d = response.data.data;
          if (d.totals) {
            if (typeof d.totals.clicks !== "undefined" && !isNaN(Number(d.totals.clicks))) {
              $("#gmb-kpi-clicks").text(Number(d.totals.clicks).toLocaleString());
            }
            if (typeof d.totals.impressions !== "undefined" && !isNaN(Number(d.totals.impressions))) {
              $("#gmb-kpi-impressions").text(Number(d.totals.impressions).toLocaleString());
            }
            if (typeof d.totals.ctr !== "undefined" && d.totals.ctr !== null) {
              var ctrVal = String(d.totals.ctr).replace("%", "");
              $("#gmb-kpi-ctr").text(ctrVal + "%");
            }
            if (typeof d.totals.position !== "undefined" && d.totals.position !== null) {
              $("#gmb-kpi-pos").text(String(d.totals.position));
            }
          }
          if (d.status === "connected") {
            var $badge = $("#gmb-analytics-status-badge");
            $badge
              .removeClass("gmb-analytics-badge-preview")
              .addClass("gmb-analytics-badge-connected");
            $badge.text("");

            var svgNs = "http://www.w3.org/2000/svg";
            var svg = document.createElementNS(svgNs, "svg");
            svg.setAttribute("width", "10");
            svg.setAttribute("height", "10");
            svg.setAttribute("viewBox", "0 0 24 24");
            svg.setAttribute("fill", "currentColor");

            var circle = document.createElementNS(svgNs, "circle");
            circle.setAttribute("cx", "12");
            circle.setAttribute("cy", "12");
            circle.setAttribute("r", "10");
            svg.appendChild(circle);

            $badge.append(svg);
            $badge.append(document.createTextNode(" Live Cloud Sync"));
          }
          $label.text("Synced!");
          setTimeout(function () {
            $label.text("Sync Live Data");
          }, 2000);
        }
      },
      error: function () {
        isAnalyticsSyncing = false;
        $btn.prop("disabled", false);
        $label.text("Sync Live Data");
      },
    });
  };

  // Instant Indexing Global Handlers (Guard against concurrency)
  var isIndexingSubmitting = false;
  window.gmbSubmitInstantIndexing = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    if (isIndexingSubmitting) return false;

    var urlsEl = document.getElementById("gmb_indexing_urls");
    var rawUrls = urlsEl ? urlsEl.value : "";
    var sanitizedUrls = sanitizeIndexingUrls(rawUrls);

    var actionInput = document.querySelector('input[name="gmb_api_action"]:checked');
    var action = actionInput ? actionInput.value : "bing_submit";
    var nonce = getNonce("gmb_instant_nonce");

    var btn = document.getElementById("gmb-indexing-submit-btn");
    var spinner = document.getElementById("gmb-indexing-spinner");
    var respBox = document.getElementById("gmb-indexing-response-box");
    var respMsg = document.getElementById("gmb-indexing-response-msg");
    var rawJson = document.getElementById("gmb-indexing-raw-json");

    if (sanitizedUrls.length === 0) {
      if (respBox && respMsg) {
        renderAlertBox(respMsg, "Please enter at least one valid HTTP/HTTPS URL.", "danger");
        respBox.style.display = "block";
      }
      return false;
    }

    isIndexingSubmitting = true;
    if (btn) btn.disabled = true;
    if (spinner) spinner.style.display = "inline";
    if (respBox) respBox.style.display = "none";

    var formData = new FormData();
    formData.append("action", "gmb_instant_indexing_submit");
    formData.append("nonce", nonce);
    formData.append("urls", sanitizedUrls.join("\n"));
    formData.append("api_action", action);

    var ajaxUrl = getAjaxUrl();

    if (!ajaxUrl) {
      isIndexingSubmitting = false;
      if (btn) btn.disabled = false;
      if (spinner) spinner.style.display = "none";
      if (respBox && respMsg) {
        renderAlertBox(respMsg, "AJAX endpoint URL is undefined.", "danger");
      }
      return false;
    }

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (r) {
        return r.json().catch(function () {
          throw new Error("Invalid JSON server response");
        });
      })
      .then(function (data) {
        isIndexingSubmitting = false;
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = "none";

        if (data && data.success && respMsg) {
          var msg = (data.data && data.data.message) ? data.data.message : "Request successfully submitted!";
          renderAlertBox(respMsg, "Request successfully submitted! " + msg, "success");
          if (rawJson) rawJson.value = JSON.stringify(data.data, null, 2);
        } else if (respMsg) {
          var err = (data && data.data && data.data.error) ? data.data.error : "Submission error.";
          renderAlertBox(respMsg, err, "danger");
          if (rawJson) rawJson.value = JSON.stringify(data, null, 2);
        }
      })
      .catch(function (err) {
        isIndexingSubmitting = false;
        if (btn) btn.disabled = false;
        if (spinner) spinner.style.display = "none";
        if (respMsg) {
          renderAlertBox(respMsg, "Submission failed: " + (err.message || "Network error"), "danger");
        }
      });

    return false;
  };

  // Google Service Account JSON File Handler (Safely validated)
  window.gmbHandleGoogleJsonFileUpload = function (fileInput) {
    if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
    var file = fileInput.files[0];

    // File size guard (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      alert("File is too large. Please select a valid JSON key under 2MB.");
      return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
      try {
        var content = e.target.result;
        var parsed = JSON.parse(content);
        if (!parsed || typeof parsed !== "object" || !parsed.client_email || !parsed.private_key) {
          alert("Invalid Google Service Account JSON: Missing client_email or private_key.");
          return;
        }
        if (typeof parsed.client_email !== "string" || typeof parsed.private_key !== "string") {
          alert("Invalid Google Service Account JSON structure.");
          return;
        }
        var textarea = document.getElementById("gmb_ranker_google_json_key_field");
        if (textarea) textarea.value = JSON.stringify(parsed, null, 2);

        var badge = document.getElementById("gmb_google_json_upload_badge");
        if (badge) {
          badge.textContent = "";
          badge.style.display = "block";

          var iconSpan = document.createElement("span");
          iconSpan.textContent = "✓ Loaded Service Account Key for: ";
          badge.appendChild(iconSpan);

          var emailStrong = document.createElement("strong");
          emailStrong.textContent = parsed.client_email;
          badge.appendChild(emailStrong);
        }
      } catch (err) {
        alert("Failed to parse JSON file safely.");
      }
    };
    reader.readAsText(file);
  };

  // Reset IndexNow API Key
  window.gmbResetIndexNowKey = function () {
    if (!confirm("Are you sure you want to generate a new IndexNow API key?")) return;
    var nonce = getNonce("gmb_instant_nonce");
    var fd = new FormData();
    fd.append("action", "gmb_instant_indexing_reset_key");
    fd.append("nonce", nonce);

    var ajaxUrl = getAjaxUrl();
    if (!ajaxUrl) return;

    fetch(ajaxUrl, { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success && res.data && res.data.key) {
          var field = document.getElementById("gmb_indexnow_key_field");
          if (field) field.value = res.data.key;
          alert("IndexNow API Key successfully regenerated.");
        }
      })
      .catch(function () {
        alert("Failed to regenerate IndexNow key.");
      });
  };

  // Clear IndexNow History
  window.gmbClearIndexNowHistory = function () {
    if (!confirm("Clear all IndexNow submission logs?")) return;
    var nonce = getNonce("gmb_instant_nonce");
    var fd = new FormData();
    fd.append("action", "gmb_instant_indexing_clear_history");
    fd.append("nonce", nonce);

    var ajaxUrl = getAjaxUrl();
    if (!ajaxUrl) return;

    fetch(ajaxUrl, { method: "POST", body: fd })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.success) location.reload();
      })
      .catch(function () {
        alert("Failed to clear IndexNow logs.");
      });
  };

  // Update Index Button Text based on Action Selection
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
    var userSelect = document.getElementById("gmb-modal-user-select");
    var currentInput = document.getElementById("gmb-modal-current-username");
    if (!userSelect || !currentInput) return;
    var opt = userSelect.options[userSelect.selectedIndex];
    if (opt) {
      currentInput.textContent = "";
      currentInput.value = opt.getAttribute("data-login") || opt.textContent.trim();
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

  var isUsernameUpdating = false;
  $(document).on("click", "#gmb-submit-username-modal", function (e) {
    e.preventDefault();
    if (isUsernameUpdating) return;

    var userSelect = document.getElementById("gmb-modal-user-select");
    var newInput = document.getElementById("gmb-modal-new-username");
    var errorSpan = document.getElementById("gmb-modal-username-error");
    var submitBtn = document.getElementById("gmb-submit-username-modal");

    var newName = newInput ? newInput.value.trim() : "";
    if (!newName || !/^[a-zA-Z0-9_\-\.\@\+\s]+$/.test(newName)) {
      if (errorSpan) {
        errorSpan.textContent = "Please enter a valid, acceptable new username.";
        errorSpan.style.display = "block";
      }
      return;
    }

    var userId = userSelect ? userSelect.value : "";
    isUsernameUpdating = true;
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Updating...";
    }
    if (errorSpan) errorSpan.style.display = "none";

    var formData = new FormData();
    formData.append("action", "gmb_change_username");
    formData.append("user_id", userId);
    formData.append("new_username", newName);
    formData.append("nonce", getNonce());

    var ajaxUrl = getAjaxUrl();

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        isUsernameUpdating = false;
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Update Username";
        }
        if (data && data.success) {
          alert((data.data && data.data.message) ? data.data.message : "Username updated successfully!");
          window.location.reload();
        } else if (errorSpan) {
          errorSpan.textContent = (data && data.data && data.data.message) ? data.data.message : "Error updating username.";
          errorSpan.style.display = "block";
        }
      })
      .catch(function () {
        isUsernameUpdating = false;
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

  var isDisplayFixing = false;
  $(document).on("click", "#gmb-auto-fix-display-name-btn", function (e) {
    e.preventDefault();
    if (isDisplayFixing) return;

    var autoFixBtn = this;
    isDisplayFixing = true;
    autoFixBtn.disabled = true;
    autoFixBtn.textContent = "Fixing...";

    var formData = new FormData();
    formData.append("action", "gmb_auto_fix_display_names");
    formData.append("nonce", getNonce());

    var ajaxUrl = getAjaxUrl();

    fetch(ajaxUrl, { method: "POST", body: formData })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        isDisplayFixing = false;
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
        isDisplayFixing = false;
        autoFixBtn.disabled = false;
        autoFixBtn.textContent = "Auto-Fix Display Name";
        alert("Network error occurred.");
      });
  });

  // Instant Indexing Unobtrusive Event Handlers
  $(document).on("submit", "#gmb-instant-indexing-form", function (e) {
    window.gmbSubmitInstantIndexing(e);
  });

  $(document).on("change", 'input[name="gmb_api_action"]', function () {
    window.gmbUpdateIndexBtnText();
  });

  $(document).on("click", "#gmb-toggle-raw-json-btn", function (e) {
    e.preventDefault();
    var rawJson = document.getElementById("gmb-indexing-raw-json");
    if (rawJson) {
      rawJson.style.display = (rawJson.style.display === "none" || !rawJson.style.display) ? "block" : "none";
    }
  });

  $(document).on("click", "#gmb-trigger-file-upload-btn", function (e) {
    e.preventDefault();
    var picker = document.getElementById("gmb_google_json_file_picker");
    if (picker) picker.click();
  });

  $(document).on("change", "#gmb_google_json_file_picker", function () {
    window.gmbHandleGoogleJsonFileUpload(this);
  });

  $(document).on("click", "#gmb-copy-service-email-btn", function (e) {
    e.preventDefault();
    var email = this.getAttribute("data-email");
    if (email && navigator.clipboard) {
      navigator.clipboard.writeText(email).then(function () {
        alert("Service Account email copied to clipboard:\n" + email + "\n\nAdd this email as an OWNER in Google Search Console > Settings > Users & Permissions.");
      }).catch(function () {
        alert("Email: " + email);
      });
    }
  });

  $(document).on("click", "#gmb-reset-indexnow-key-btn", function (e) {
    e.preventDefault();
    window.gmbResetIndexNowKey();
  });

  $(document).on("click", "#gmb-clear-history-btn", function (e) {
    e.preventDefault();
    window.gmbClearIndexNowHistory();
  });
})(jQuery);
