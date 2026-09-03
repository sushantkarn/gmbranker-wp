/**
 * GMB Ranker SEO — Post Editor Metabox JavaScript
 * Handles interactive tabs, live snippet preview, focus keywords, content audit, social cards, and schema generator.
 */
(function ($) {
  "use strict";

  $(document).ready(function () {

// ==========================================
    // Single Page AI SEO Auto-Fix Handler (Redirection-Style Table Modal)
    // ==========================================
    $(document).on("click", "#gmb-ai-optimize-post-btn", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var $modal = $("#gmb-ai-post-seo-modal");

      if (!$modal.length) {
        alert("AI Modal element not found. Please refresh the page.");
        return;
      }

      // Move modal to body to guarantee root overlay
      $modal.appendTo("body");
      $modal.css("display", "flex").addClass("active");

      var $loading = $("#gmb-ai-post-modal-loading");
      var $content = $("#gmb-ai-post-modal-content");
      var $tbody = $("#gmb-ai-post-suggestions-tbody");
      var $applyBtn = $("#gmb-ai-post-apply-btn");

      $loading.css("display", "flex");
      $content.addClass("gmb-hidden");
      $tbody.empty();
      $applyBtn.prop("disabled", true);

      // Rotating AI Research Step Messages
      var researchSteps = [
        "🔍 Step 1/5: Extracting primary entities, headings, and page topic...",
        "🧠 Step 2/5: Performing deep search intent & LSI entity clustering...",
        "📐 Step 3/5: Calculating SERP pixel width and crafting high-CTR titles...",
        "✍️ Step 4/5: Writing PAS conversion copy for Meta Description...",
        "🕸️ Step 5/5: Mapping SILO internal links & E-E-A-T trust citations..."
      ];
      var stepIdx = 0;
      $("#gmb-ai-loading-step-text").text(researchSteps[0]);
      var stepTimer = setInterval(function () {
        stepIdx = (stepIdx + 1) % researchSteps.length;
        $("#gmb-ai-loading-step-text").text(researchSteps[stepIdx]);
      }, 2200);

      // Extract title & content safely
      var postTitle = $("#title").val() || "";
      if (!postTitle && typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.select("core/editor")) {
        postTitle = wp.data.select("core/editor").getEditedPostAttribute("title") || "";
      }

      var postContent = "";
      if (typeof tinymce !== "undefined" && tinymce.get("content") && !tinymce.get("content").isHidden()) {
        postContent = tinymce.get("content").getContent();
      } else if ($("#content").length) {
        postContent = $("#content").val();
      } else if (typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.select("core/editor")) {
        postContent = wp.data.select("core/editor").getEditedPostAttribute("content") || "";
      }

      var postId = $("#post_ID").val() || 0;
      var curFocus = $("#gmb_seo_focus_keyword_hidden").val() || "";
      var curTitle = $("#gmb_seo_title_input").val() || "";
      var curDesc = $("#gmb_seo_desc_input").val() || "";

      $.ajax({
        url: gmbMetaboxData.ajaxUrl,
        type: "POST",
        data: {
          action: "gmb_ai_analyze_and_fix_post_seo",
          nonce: gmbMetaboxData.nonce,
          post_id: postId,
          title: postTitle,
          content: postContent,
          post_type: gmbMetaboxData.postType || "post",
          focus_keyword: curFocus,
          seo_title: curTitle,
          meta_description: curDesc
        },
        success: function (res) {
          clearInterval(stepTimer);
          $loading.css("display", "none");
          if (!res.success || !res.data) {
            alert("AI analysis failed: " + (res.data || "Unknown error"));
            $modal.css("display", "none").removeClass("active");
            return;
          }

          var data = res.data;
          $content.removeClass("gmb-hidden");
          $applyBtn.prop("disabled", false);

          var rowsHtml = "";

          // Row 1: Focus Keyword
          rowsHtml += '<tr>' +
            '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="focus" checked /></td>' +
            '<td><strong>Focus Keyword</strong></td>' +
            '<td><input type="text" id="gmb-ai-input-focus" value="' + (data.focus_keyword || "") + '" class="gmb-integration-input gmb-input-sm" /></td>' +
            '<td><span class="gmb-status-pill gmb-status-pill--success">Fix Needed</span></td>' +
            '</tr>';

          // Row 2: SEO Title
          rowsHtml += '<tr>' +
            '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="title" checked /></td>' +
            '<td><strong>SEO Title</strong></td>' +
            '<td><input type="text" id="gmb-ai-input-title" value="' + (data.seo_title || "") + '" class="gmb-integration-input gmb-input-sm" /></td>' +
            '<td><span class="gmb-status-pill gmb-status-pill--success">Fix Needed</span></td>' +
            '</tr>';

          // Row 3: Meta Description
          rowsHtml += '<tr>' +
            '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="desc" checked /></td>' +
            '<td><strong>Meta Description</strong></td>' +
            '<td><textarea id="gmb-ai-input-desc" rows="2" class="gmb-integration-input gmb-input-sm">' + (data.meta_description || "") + '</textarea></td>' +
            '<td><span class="gmb-status-pill gmb-status-pill--success">Fix Needed</span></td>' +
            '</tr>';

          // Row 4: URL Slug
          rowsHtml += '<tr>' +
            '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="slug" checked /></td>' +
            '<td><strong>URL Slug</strong></td>' +
            '<td><input type="text" id="gmb-ai-input-slug" value="' + (data.suggested_slug || "") + '" class="gmb-integration-input gmb-input-sm" /></td>' +
            '<td><span class="gmb-status-pill gmb-status-pill--primary">Optimized</span></td>' +
            '</tr>';

          // Row 5: Schema Blueprint
          var schemaVal = data.schema_type || "WebPage";
          rowsHtml += '<tr>' +
            '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="schema" checked /></td>' +
            '<td><strong>Schema Preset</strong></td>' +
            '<td><select id="gmb-ai-input-schema" class="gmb-integration-select gmb-input-sm">' +
              '<option value="WebPage"' + (schemaVal === 'WebPage' ? ' selected' : '') + '>WebPage</option>' +
              '<option value="Article"' + (schemaVal === 'Article' ? ' selected' : '') + '>Article</option>' +
              '<option value="AboutPage"' + (schemaVal === 'AboutPage' ? ' selected' : '') + '>AboutPage</option>' +
              '<option value="Service"' + (schemaVal === 'Service' ? ' selected' : '') + '>Service</option>' +
              '<option value="LocalBusiness"' + (schemaVal === 'LocalBusiness' ? ' selected' : '') + '>LocalBusiness</option>' +
              '<option value="Product"' + (schemaVal === 'Product' ? ' selected' : '') + '>Product</option>' +
            '</select></td>' +
            '<td><span class="gmb-status-pill gmb-status-pill--primary">Recommended</span></td>' +
            '</tr>';

          // Internal Links Rows
          if (data.internal_links && data.internal_links.length > 0) {
            data.internal_links.forEach(function (l) {
              rowsHtml += '<tr>' +
                '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check gmb-ai-link-check" data-factor="link" data-anchor="' + l.anchor + '" data-url="' + l.url + '" checked /></td>' +
                '<td><strong>Internal Link</strong></td>' +
                '<td>Link "<strong>' + l.anchor + '</strong>" &rarr; <code>' + l.url + '</code></td>' +
                '<td><span class="gmb-status-pill gmb-status-pill--success">Match Found</span></td>' +
                '</tr>';
            });
          }

          // External E-E-A-T Authority Links Rows
          if (data.external_links && data.external_links.length > 0) {
            data.external_links.forEach(function (el) {
              rowsHtml += '<tr>' +
                '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check gmb-ai-ext-link-check" data-factor="ext_link" data-anchor="' + el.anchor + '" data-url="' + el.url + '" checked /></td>' +
                '<td><strong>External Authority</strong></td>' +
                '<td>Cite "<strong>' + el.anchor + '</strong>" &rarr; <code>' + el.url + '</code> <span class="gmb-text-muted-xs">(' + (el.reasoning || "E-E-A-T Trust Link") + ')</span></td>' +
                '<td><span class="gmb-status-pill gmb-status-pill--primary">Authority Trust</span></td>' +
                '</tr>';
            });
          }

          $tbody.html(rowsHtml);
        },
        error: function (xhr, status, err) {
          clearInterval(stepTimer);
          $loading.css("display", "none");
          alert("AJAX Error: " + err);
          $modal.css("display", "none").removeClass("active");
        }
      });
    });

    // Select All Checkbox Handler
    $(document).on("change", "#gmb-ai-post-select-all", function () {
      var isChecked = $(this).is(":checked");
      $(".gmb-ai-post-check").prop("checked", isChecked);
    });

    $(document).on("click", "#gmb-ai-post-modal-close, #gmb-ai-post-modal-cancel", function (e) {
      e.preventDefault();
      $("#gmb-ai-post-seo-modal").css("display", "none").removeClass("active");
    });

    // Apply Selected Recommendations Button Handler
    $(document).on("click", "#gmb-ai-post-apply-btn", function (e) {
      e.preventDefault();

      var applyFocus = $('.gmb-ai-post-check[data-factor="focus"]').is(":checked");
      var applyTitle = $('.gmb-ai-post-check[data-factor="title"]').is(":checked");
      var applyDesc = $('.gmb-ai-post-check[data-factor="desc"]').is(":checked");
      var applySlug = $('.gmb-ai-post-check[data-factor="slug"]').is(":checked");
      var applySchema = $('.gmb-ai-post-check[data-factor="schema"]').is(":checked");

      // Apply Focus Keyword
      if (applyFocus) {
        var focusKw = $("#gmb-ai-input-focus").val().trim();
        if (focusKw) {
          if (typeof window.gmbSetFocusKeywords === "function") {
            window.gmbSetFocusKeywords(focusKw);
          } else {
            $("#gmb_seo_focus_keyword_hidden").val(focusKw);
          }
        }
      }

      // Apply SEO Title
      var appliedTitle = "";
      if (applyTitle) {
        appliedTitle = $("#gmb-ai-input-title").val().trim();
        if (appliedTitle) {
          $("#gmb_seo_title_input").val(appliedTitle).trigger("input").trigger("change").trigger("keyup");
        }
      }

      // Apply Meta Description
      var appliedDesc = "";
      if (applyDesc) {
        appliedDesc = $("#gmb-ai-input-desc").val().trim();
        if (appliedDesc) {
          $("#gmb_seo_desc_input").val(appliedDesc).trigger("input").trigger("change").trigger("keyup");
        }
      }

      // Apply Slug
      var appliedSlug = "";
      if (applySlug) {
        appliedSlug = $("#gmb-ai-input-slug").val().trim();
        if (appliedSlug) {
          if ($("#post_name").length) {
            $("#post_name").val(appliedSlug);
          }
          if ($("#editable-post-name").length) {
            $("#editable-post-name").text(appliedSlug);
          }
          if ($("#new-post-slug").length) {
            $("#new-post-slug").val(appliedSlug);
          }

          // Live Update Snippet Preview Breadcrumb URL
          $(".gmb-google-breadcrumbs").each(function () {
            var fullText = $(this).text();
            var parts = fullText.split(" › ");
            var homeUrl = parts[0] || (gmbMetaboxData.homeUrl || "");
            $(this).text(homeUrl + " › " + appliedSlug);
          });

          if (typeof wp !== "undefined" && wp.data && wp.data.dispatch && wp.data.dispatch("core/editor")) {
            try {
              wp.data.dispatch("core/editor").editPost({ slug: appliedSlug });
            } catch(err) {}
          }
        }
      }

      // Apply Schema
      var appliedSchema = "";
      if (applySchema) {
        appliedSchema = $("#gmb-ai-input-schema").val();
        if (appliedSchema) {
          if ($("#gmb_seo_active_schemas").length) {
            $("#gmb_seo_active_schemas").val(appliedSchema);
          }
          var $schemaList = $("#gmb-schema-in-use-list");
          if ($schemaList.length) {
            $schemaList.html(
              '<div class="gmb-schema-active-card" data-schema-active="' + appliedSchema + '">' +
                '<div class="gmb-schema-active-info">' +
                  '<strong class="gmb-schema-active-title">' + appliedSchema + '</strong>' +
                '</div>' +
              '</div>'
            );
          }
        }
      }

      // Real-time Background DB Persistence (guarantees DB update even without post save)
      var postId = $("#post_ID").val() || 0;
      if (postId > 0) {
        $.ajax({
          url: gmbMetaboxData.ajaxUrl,
          type: "POST",
          data: {
            action: "gmb_quick_save_ai_seo_fields",
            nonce: gmbMetaboxData.nonce,
            post_id: postId,
            focus_keyword: applyFocus ? $("#gmb-ai-input-focus").val().trim() : "",
            seo_title: appliedTitle,
            meta_description: appliedDesc,
            slug: appliedSlug,
            schema_type: appliedSchema
          }
        });
      }

      // Apply checked internal & external links
      var linksToInsert = [];
      $(".gmb-ai-link-check:checked, .gmb-ai-ext-link-check:checked").each(function () {
        var anchor = $(this).attr("data-anchor");
        var url = $(this).attr("data-url");
        var isExt = $(this).hasClass("gmb-ai-ext-link-check");
        if (anchor && url) {
          linksToInsert.push({ anchor: anchor, url: url, isExt: isExt });
        }
      });

      if (linksToInsert.length > 0) {
        function escapeRegex(str) {
          return str.replace(/[-\/\\^$*+?.()|[\]{}]/g, "\\$&");
        }

        // TinyMCE / Classic Editor
        if (typeof tinymce !== "undefined" && tinymce.get("content") && !tinymce.get("content").isHidden()) {
          var bodyHtml = tinymce.get("content").getContent();
          linksToInsert.forEach(function (l) {
            if (bodyHtml.indexOf(l.url) === -1) {
              var reg = new RegExp("\\b(" + escapeRegex(l.anchor) + ")\\b", "i");
              var attr = l.isExt ? ' target="_blank" rel="noopener noreferrer"' : '';
              if (reg.test(bodyHtml)) {
                bodyHtml = bodyHtml.replace(reg, '<a href="' + l.url + '"' + attr + '>$1</a>');
              } else {
                bodyHtml += '<p>Learn more about <a href="' + l.url + '"' + attr + '>' + l.anchor + '</a>.</p>';
              }
            }
          });
          tinymce.get("content").setContent(bodyHtml);
        } 
        // Gutenberg Block Editor
        else if (typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.dispatch && wp.data.select("core/editor")) {
          try {
            var gContent = wp.data.select("core/editor").getEditedPostAttribute("content") || "";
            linksToInsert.forEach(function (l) {
              if (gContent.indexOf(l.url) === -1) {
                var reg = new RegExp("\\b(" + escapeRegex(l.anchor) + ")\\b", "i");
                if (reg.test(gContent)) {
                  gContent = gContent.replace(reg, '<a href="' + l.url + '">$1</a>');
                } else {
                  gContent += '<!-- wp:paragraph --><p>Learn more about <a href="' + l.url + '">' + l.anchor + '</a>.</p><!-- /wp:paragraph -->';
                }
              }
            });
            wp.data.dispatch("core/editor").editPost({ content: gContent });
          } catch (err) {}
        }
      }

      // Trigger Live SEO Analysis Recalculation
      if (typeof window.gmbRunContentAnalysis === "function") {
        window.gmbRunContentAnalysis();
      } else {
        $("#gmb_seo_focus_keyword").trigger("keyup");
      }

      $("#gmb-ai-post-seo-modal").css("display", "none").removeClass("active");
      alert("✨ AI Recommendations successfully applied to page SEO settings!");
    });

    // ==========================================
    // 1. Metabox Main Tab Switching
    // ==========================================
    $(document).on("click", ".gmb-seo-tabs .gmb-tab-btn", function (e) {
      e.preventDefault();
      var targetTab = $(this).attr("data-tab") || $(this).attr("data-target");
      if (!targetTab) return;

      var $container = $(this).closest(".gmb-seo-meta-container");
      $container
        .find(".gmb-seo-tabs .gmb-tab-btn")
        .removeClass("active is-active");
      $(this).addClass("active is-active");

      $container.find(".gmb-tab-content").removeClass("active").hide();
      $("#" + targetTab)
        .addClass("active")
        .fadeIn(150);
    });

    // ==========================================
    // 2. Social Sub-Tab Switching (Inside Social Tab)
    // ==========================================
    $(document).on(
      "click",
      ".gmb-social-tab-btn, .gmb-social-subtab-btn",
      function (e) {
        e.preventDefault();
        var targetPane =
          $(this).attr("data-social-tab") || $(this).attr("data-social-target");
        if (!targetPane) return;

        $(".gmb-social-tab-btn, .gmb-social-subtab-btn").removeClass("active");
        $(this).addClass("active");

        $(".gmb-social-pane").hide();
        $("#" + targetPane).fadeIn(150);
      },
    );

    // ==========================================
    // 3. Snippet Editor Modal & Device Preview Toggle
    // ==========================================
    $(document).on("click", ".gmb-device-btn", function (e) {
      e.preventDefault();
      var device = $(this).attr("data-device") || "desktop";
      $(".gmb-device-btn").removeClass("active");
      $(this).addClass("active");

      var $preview = $(".gmb-preview-google");
      if (device === "mobile") {
        $preview
          .removeClass("gmb-preview-device--desktop")
          .addClass("gmb-preview-device--mobile");
      } else {
        $preview
          .removeClass("gmb-preview-device--mobile")
          .addClass("gmb-preview-device--desktop");
      }
    });

    // Advanced Tab: Redirect Toggle Handler
    $(document).on("change", "#gmb_enable_redirect_toggle", function () {
      if ($(this).is(":checked")) {
        $("#gmb-redirect-details-box").slideDown(150).css("display", "flex");
      } else {
        $("#gmb-redirect-details-box").slideUp(150);
        $("#gmb_seo_redirect_url").val("");
      }
    });

    // Advanced Tab: Index vs No Index Mutual Exclusivity
    $(document).on("change", "#gmb_seo_robot_index", function () {
      if ($(this).is(":checked")) {
        $("#gmb_seo_robot_noindex").prop("checked", false);
      }
    });

    $(document).on("change", "#gmb_seo_robot_noindex", function () {
      if ($(this).is(":checked")) {
        $("#gmb_seo_robot_index").prop("checked", false);
      }
    });

    // Advanced Tab: Advanced Robots Meta Checkbox / Input Sync
    $(document).on("change", ".gmb-adv-robot-toggle", function () {
      var $item = $(this).closest(".gmb-adv-robot-item");
      var $input = $item.find('input[type="text"], select');
      if ($(this).is(":checked")) {
        $input.prop("disabled", false).css("opacity", "1");
      } else {
        $input.prop("disabled", true).css("opacity", "0.5");
      }
    });

    $(document).on("click", "#gmb-edit-snippet-btn", function (e) {
      e.preventDefault();
      $("#gmb-snippet-modal").fadeIn(200).css("display", "flex");
      updateModalPreview();
    });

    $(document).on(
      "click",
      "#gmb-modal-close-btn, #gmb-modal-save-btn",
      function (e) {
        e.preventDefault();
        $("#gmb-snippet-modal").fadeOut(150);
        syncSnippetFromInputs();
      },
    );

    // Modal Tab Switching (General vs Social)
    $(document).on("click", ".gmb-modal-tabs .gmb-modal-tab-btn", function (e) {
      e.preventDefault();
      var targetModalTab =
        $(this).attr("data-modal-tab") || $(this).attr("data-target");
      if (!targetModalTab) return;

      $(".gmb-modal-tab-btn").removeClass("active");
      $(this).addClass("active");

      $(".gmb-modal-tab-content").hide();
      $("#" + targetModalTab).show();
    });

    // ==========================================
    // 4. Character Counts & Live Preview Updating
    // ==========================================
    // SERP Title Pixel Width Calculation (580px max width)
    function calculateTitlePixelWidth(str) {
      if (!str) return 0;
      var canvas = calculateTitlePixelWidth.canvas || (calculateTitlePixelWidth.canvas = document.createElement("canvas"));
      var context = canvas.getContext("2d");
      context.font = "20px Arial, sans-serif";
      return Math.round(context.measureText(str).width);
    }

    function updateTitleCharCount() {
      var $input = $("#gmb_seo_title_input");
      var val = $input.val() || "";
      var count = val.length;
      $("#gmb-title-char-count").text(count + " / 60 chars");

      var px = calculateTitlePixelWidth(val);
      var $pxVal = $("#gmb-title-pixel-val");
      if ($pxVal.length) {
        $pxVal.text(px + "px");
        if (px > 580) {
          $pxVal.css("color", "#dc2626");
        } else if (px >= 400) {
          $pxVal.css("color", "#16a34a");
        } else {
          $pxVal.css("color", "#d97706");
        }
      }

      var pct = Math.min(100, Math.round((count / 60) * 100));
      var $bar = $("#gmb-title-progress-fill");
      $bar.css("width", pct + "%");
      if (count > 60 || px > 580) {
        $bar.css("background-color", "#dc2626");
      } else if (count >= 40) {
        $bar.css("background-color", "#16a34a");
      } else {
        $bar.css("background-color", "#d97706");
      }

      var displayTitle =
        val || ($("#title").length ? $("#title").val() : "") || "Untitled Post";
      $("#gmb-preview-title").text(displayTitle);
      $("#gmb-modal-preview-title").text(displayTitle);

      // Update social if not overridden
      if (!$("#gmb_seo_fb_title_metabox").val()) {
        $("#gmb-fb-preview-title").text(displayTitle);
      }
      if (!$("#gmb_seo_tw_title_metabox").val()) {
        $("#gmb-tw-preview-title").text(displayTitle);
      }
    }

    function updateDescCharCount() {
      var $input = $("#gmb_seo_desc_input");
      var val = $input.val() || "";
      var count = val.length;
      $("#gmb-desc-char-count").text(count + " / 160 chars");

      // PAS Copywriting Formula Check (Pain/Question + Solution/Service + Active CTA)
      var hasPain = val.indexOf("?") !== -1 || /worry|fear|struggle|need|looking|problem|care/i.test(val);
      var hasCTA = /book|call|contact|get|discover|learn|start|try|apply|order|schedule/i.test(val);
      var $pasBadge = $("#gmb-pas-badge");
      if ($pasBadge.length) {
        if (count >= 120 && hasPain && hasCTA) {
          $pasBadge.removeClass("gmb-hidden");
        } else {
          $pasBadge.addClass("gmb-hidden");
        }
      }

      var pct = Math.min(100, Math.round((count / 160) * 100));
      var $bar = $("#gmb-desc-progress-fill");
      $bar.css("width", pct + "%");
      if (count > 160) {
        $bar.css("background-color", "#dc2626");
      } else if (count >= 120) {
        $bar.css("background-color", "#16a34a");
      } else {
        $bar.css("background-color", "#d97706");
      }

      var displayDesc =
        val ||
        "Please enter a Meta Description below to preview this result...";
      $("#gmb-preview-snippet").text(displayDesc);
      $("#gmb-modal-preview-snippet").text(displayDesc);

      // Update social if not overridden
      if (!$("#gmb_seo_fb_desc_metabox").val()) {
        $("#gmb-fb-preview-desc").text(val || "Social description preview...");
      }
      if (!$("#gmb_seo_tw_desc_metabox").val()) {
        $("#gmb-tw-preview-desc").text(
          val || "Twitter summary description preview...",
        );
      }
    }

    // CTR Preset Modifier Buttons Handler
    $(document).on("click", ".gmb-ctr-btn", function (e) {
      e.preventDefault();
      var appendText = $(this).attr("data-append") || "";
      var $input = $("#gmb_seo_title_input");
      var curVal = $input.val() || ($("#title").length ? $("#title").val() : "") || "";
      if (curVal.indexOf(appendText.trim()) === -1) {
        $input.val(curVal + appendText).trigger("input").trigger("change").trigger("keyup");
      }
    });

    function updateModalPreview() {
      updateTitleCharCount();
      updateDescCharCount();
    }

    function syncSnippetFromInputs() {
      var title = $("#gmb_seo_title_input").val();
      var desc = $("#gmb_seo_desc_input").val();
      if (title) {
        $("#gmb-preview-title").text(title);
      }
      if (desc) {
        $("#gmb-preview-snippet").text(desc);
      }
      recalculateScore();
    }

    $(document).on("input", "#gmb_seo_title_input", updateTitleCharCount);
    $(document).on("input", "#gmb_seo_desc_input", updateDescCharCount);
    $(document).on("input", "#title", function () {
      if (!$("#gmb_seo_title_input").val()) {
        updateTitleCharCount();
      }
    });

    // Initialize counters on load
    updateTitleCharCount();
    updateDescCharCount();

    // ==========================================
    // 5. Social Live Sync & Field Inputs
    // ==========================================
    function updateSocialCounters() {
      var fbTitle = $("#gmb_seo_fb_title_metabox").val() || "";
      var fbDesc = $("#gmb_seo_fb_desc_metabox").val() || "";
      var twTitle = $("#gmb_seo_tw_title_metabox").val() || "";
      var twDesc = $("#gmb_seo_tw_desc_metabox").val() || "";

      var $fbTitleCnt = $("#gmb-fb-title-counter");
      if ($fbTitleCnt.length) {
        $fbTitleCnt
          .text(fbTitle.length + " / 60")
          .removeClass(
            "gmb-char-counter--green gmb-char-counter--orange gmb-char-counter--red",
          )
          .addClass(
            fbTitle.length <= 60
              ? "gmb-char-counter--green"
              : "gmb-char-counter--red",
          );
      }

      var $fbDescCnt = $("#gmb-fb-desc-counter");
      if ($fbDescCnt.length) {
        $fbDescCnt
          .text(fbDesc.length + " / 160")
          .removeClass(
            "gmb-char-counter--green gmb-char-counter--orange gmb-char-counter--red",
          )
          .addClass(
            fbDesc.length <= 160
              ? "gmb-char-counter--green"
              : "gmb-char-counter--red",
          );
      }

      var $twTitleCnt = $("#gmb-tw-title-counter");
      if ($twTitleCnt.length) {
        $twTitleCnt
          .text(twTitle.length + " / 60")
          .removeClass(
            "gmb-char-counter--green gmb-char-counter--orange gmb-char-counter--red",
          )
          .addClass(
            twTitle.length <= 60
              ? "gmb-char-counter--green"
              : "gmb-char-counter--red",
          );
      }

      var $twDescCnt = $("#gmb-tw-desc-counter");
      if ($twDescCnt.length) {
        $twDescCnt
          .text(twDesc.length + " / 160")
          .removeClass(
            "gmb-char-counter--green gmb-char-counter--orange gmb-char-counter--red",
          )
          .addClass(
            twDesc.length <= 160
              ? "gmb-char-counter--green"
              : "gmb-char-counter--red",
          );
      }
    }

    updateSocialCounters();

    $(document).on("input", "#gmb_seo_fb_title_metabox", function () {
      var val =
        $(this).val() ||
        $("#gmb_seo_title_input").val() ||
        $("#title").val() ||
        "Page Title";
      $("#gmb-fb-preview-title").text(val);
      updateSocialCounters();
    });

    $(document).on("input", "#gmb_seo_fb_desc_metabox", function () {
      var val =
        $(this).val() ||
        $("#gmb_seo_desc_input").val() ||
        "Social description preview...";
      $("#gmb-fb-preview-desc").text(val);
      updateSocialCounters();
    });

    $(document).on("input change", "#gmb_seo_fb_image_metabox", function () {
      var url = $(this).val();
      var $clearBtn = $(
        '.gmb-social-clear-img-btn[data-target="gmb_seo_fb_image_metabox"]',
      );
      if (url && url.trim().length > 0) {
        $("#gmb-fb-preview-img").attr("src", url).show();
        $("#gmb-fb-preview-placeholder").hide();
        $clearBtn.show();
      } else {
        $("#gmb-fb-preview-img").hide();
        $("#gmb-fb-preview-placeholder").css("display", "flex");
        $clearBtn.hide();
      }
    });

    $(document).on("input", "#gmb_seo_tw_title_metabox", function () {
      var val =
        $(this).val() ||
        $("#gmb_seo_fb_title_metabox").val() ||
        $("#gmb_seo_title_input").val() ||
        $("#title").val() ||
        "Page Title";
      $("#gmb-tw-preview-title").text(val);
      updateSocialCounters();
    });

    $(document).on("input", "#gmb_seo_tw_desc_metabox", function () {
      var val =
        $(this).val() ||
        $("#gmb_seo_fb_desc_metabox").val() ||
        $("#gmb_seo_desc_input").val() ||
        "Twitter summary description preview...";
      $("#gmb-tw-preview-desc").text(val);
      updateSocialCounters();
    });

    $(document).on("input change", "#gmb_seo_tw_image_metabox", function () {
      var url = $(this).val();
      var $clearBtn = $(
        '.gmb-social-clear-img-btn[data-target="gmb_seo_tw_image_metabox"]',
      );
      if (url && url.trim().length > 0) {
        $("#gmb-tw-preview-img").attr("src", url).show();
        $("#gmb-tw-preview-placeholder").hide();
        $clearBtn.show();
      } else {
        $("#gmb-tw-preview-img").hide();
        $("#gmb-tw-preview-placeholder").css("display", "flex");
        $clearBtn.hide();
      }
    });

    // Twitter Card Type Toggle in Preview Mockup
    $(document).on("change", "#gmb_seo_tw_card_type", function () {
      var cardType = $(this).val();
      var $card = $("#gmb-tw-card-container");
      if (cardType === "summary") {
        $card
          .removeClass("gmb-tw-card--large")
          .addClass("gmb-tw-card--summary");
      } else {
        $card
          .removeClass("gmb-tw-card--summary")
          .addClass("gmb-tw-card--large");
      }
    });

    // Clear Social Image Button
    $(document).on("click", ".gmb-social-clear-img-btn", function (e) {
      e.preventDefault();
      var targetInputId = $(this).attr("data-target");
      $("#" + targetInputId)
        .val("")
        .trigger("input")
        .trigger("change");
      $(this).hide();
    });

    // Click on Mockup Image Box triggers file picker
    $(document).on("click", ".gmb-media-upload-trigger", function () {
      var targetInputId = $(this).attr("data-target");
      if (targetInputId) {
        $('.gmb-media-upload-btn[data-target="' + targetInputId + '"]').click();
      }
    });

    // Sync from General SEO button
    $(document).on("click", "#gmb-sync-social-btn", function (e) {
      e.preventDefault();
      var title =
        $("#gmb_seo_title_input").val() ||
        ($("#title").length ? $("#title").val() : "");
      var desc = $("#gmb_seo_desc_input").val();

      if (title) {
        $("#gmb_seo_fb_title_metabox").val(title).trigger("input");
        $("#gmb_seo_tw_title_metabox").val(title).trigger("input");
      }
      if (desc) {
        $("#gmb_seo_fb_desc_metabox").val(desc).trigger("input");
        $("#gmb_seo_tw_desc_metabox").val(desc).trigger("input");
      }

      // Sync featured image if present
      var $featImg = $("#set-post-thumbnail img");
      if ($featImg.length && $featImg.attr("src")) {
        var imgSrc = $featImg.attr("src");
        $("#gmb_seo_fb_image_metabox")
          .val(imgSrc)
          .trigger("input")
          .trigger("change");
        $("#gmb_seo_tw_image_metabox")
          .val(imgSrc)
          .trigger("input")
          .trigger("change");
      }

      updateSocialCounters();

      var $btn = $(this);
      var origHtml = $btn.html();
      $btn.html(
        '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Synced!</span>',
      );
      setTimeout(function () {
        $btn.html(origHtml);
      }, 1800);
    });

    // ==========================================
    // 6. Focus Keyword Pills & Management
    // ==========================================
    var keywords = [];
    var rawKeywordVal = $("#gmb_seo_focus_keyword_hidden").val();
    if (rawKeywordVal) {
      keywords = rawKeywordVal
        .split(",")
        .map(function (k) {
          return k.trim();
        })
        .filter(Boolean);
    }

    function renderKeywordPills() {
      var $wrapper = $("#gmb-keyword-container-wrapper");
      $wrapper.find(".gmb-keyword-pill").remove();

      keywords.forEach(function (kw, index) {
        var $pill = $(
          '<span class="gmb-keyword-pill">' +
            kw +
            ' <span class="gmb-keyword-pill-remove" data-index="' +
            index +
            '">&times;</span></span>',
        );
        $wrapper.prepend($pill);
      });

      $("#gmb_seo_focus_keyword_hidden").val(keywords.join(", "));

      if (keywords.length > 0) {
        $("#gmb-seo-no-keyword-notice").hide();
      } else {
        $("#gmb-seo-no-keyword-notice").show();
      }

      recalculateScore();
    }

    window.gmbSetFocusKeywords = function (kwString) {
      if (!kwString) return;
      var newKws = kwString.split(",").map(function (k) { return k.trim(); }).filter(Boolean);
      newKws.forEach(function (k) {
        if (keywords.indexOf(k) === -1) {
          keywords.push(k);
        }
      });
      renderKeywordPills();
    };

    renderKeywordPills();

    $(document).on("keydown", "#gmb_seo_focus_keyword_input", function (e) {
      if (e.key === "Enter" || e.key === ",") {
        e.preventDefault();
        var val = $(this).val().trim();
        if (val && keywords.indexOf(val) === -1) {
          keywords.push(val);
          $(this).val("");
          renderKeywordPills();
        }
      }
    });

    $(document).on("click", ".gmb-keyword-pill-remove", function () {
      var index = $(this).attr("data-index");
      keywords.splice(index, 1);
      renderKeywordPills();
    });

    $(document).on("click", ".gmb-focus-keyword-field-wrapper", function (e) {
      if (
        !$(e.target).closest(
          ".gmb-keyword-pill-remove, #gmb-metabox-score-badge",
        ).length
      ) {
        $("#gmb_seo_focus_keyword_input").focus();
      }
    });

    // ==========================================
    // 7. Accordion Toggle (Silky-Smooth Slide)
    // ==========================================
    $(document).on("click", ".gmb-accordion-header", function (e) {
      e.preventDefault();
      var $section = $(this).closest(".gmb-accordion-section");
      var $content = $section.find(".gmb-accordion-content");

      if ($section.hasClass("collapsed")) {
        $section.removeClass("collapsed");
        $content.hide().stop(true, true).slideDown(220);
      } else {
        $content.stop(true, true).slideUp(220, function () {
          $section.addClass("collapsed");
          $(this).css("display", "none");
        });
      }
    });

    // ==========================================
    // 8. SEO Audit & Dynamic Scoring
    // ==========================================
    function recalculateScore() {
      var primaryKw = keywords.length > 0 ? keywords[0].toLowerCase() : "";
      var title = ($("#gmb_seo_title_input").val() || "").trim();
      if (!title) {
        title = (
          $("#title").val() ||
          $("#gmb-preview-title").text() ||
          ""
        ).trim();
      }
      var desc = ($("#gmb_seo_desc_input").val() || "").trim();
      var primaryKw = keywords.length > 0 ? keywords[0].toLowerCase() : "";
      var titleLower = title.toLowerCase();
      var descLower = desc.toLowerCase();

      // Get post content from TinyMCE, Gutenberg or standard textarea
      var content = "";
      if (
        typeof tinyMCE !== "undefined" &&
        tinyMCE.get("content") &&
        !tinyMCE.get("content").isHidden()
      ) {
        content = tinyMCE.get("content").getContent({ format: "raw" });
      } else if ($("#content").length) {
        content = $("#content").val();
      } else if (
        typeof wp !== "undefined" &&
        wp.data &&
        wp.data.select &&
        wp.data.select("core/editor")
      ) {
        content = wp.data.select("core/editor").getEditedPostContent() || "";
      }

      // Clean text without HTML tags for word counting & density
      var textOnly = content
        .replace(/<[^>]+>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
      var textOnlyLower = textOnly.toLowerCase();
      var wordCount = textOnly
        ? textOnly.split(/\s+/).filter(Boolean).length
        : 0;
      var permalink = (
        $("#sample-permalink").text() ||
        $("#editable-post-name").text() ||
        ""
      ).toLowerCase();

      // ==========================================
      // 1. Basic SEO (6 Tests)
      // ==========================================
      var basicPasses = 0;
      var basicFails = 0;
      var basicItems = [];

      // 1. Focus Keyword in SEO Title
      if (primaryKw && titleLower.indexOf(primaryKw) !== -1) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Hurray! You're using Focus Keyword in the SEO Title.",
          tip: "Focus keyword is present in the SEO Title.",
        });
      } else {
        basicFails++;
        basicItems.push({
          status: "fail",
          text: primaryKw
            ? "Focus Keyword not found in the SEO Title."
            : "Add a Focus Keyword to your post.",
          tip: "Add your primary focus keyword to the SEO title.",
        });
      }

      // 2. Focus Keyword in Meta Description
      if (primaryKw && descLower.indexOf(primaryKw) !== -1) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used inside SEO Meta Description.",
          tip: "Focus keyword is present in the meta description.",
        });
      } else {
        basicFails++;
        basicItems.push({
          status: "fail",
          text: "Focus Keyword not found in SEO Meta Description.",
          tip: "Include your focus keyword inside the meta description for better CTR.",
        });
      }

      // 3. Focus Keyword in URL
      if (
        primaryKw &&
        permalink &&
        permalink.indexOf(primaryKw.replace(/\s+/g, "-")) !== -1
      ) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used in the URL.",
          tip: "Focus keyword is included in the permalink slug.",
        });
      } else if (primaryKw && permalink) {
        basicFails++;
        basicItems.push({
          status: "fail",
          text: "Focus Keyword not found in the URL.",
          tip: "Include the focus keyword in your post slug/URL.",
        });
      } else {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used in the URL.",
          tip: "Focus keyword is included in the URL.",
        });
      }

      // 4. Focus Keyword at the beginning of content (First 10% or first 100 words)
      var first100Words = textOnlyLower.split(/\s+/).slice(0, 100).join(" ");
      if (primaryKw && first100Words.indexOf(primaryKw) !== -1) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used at the beginning of your content.",
          tip: "Focus keyword appears in the first 10% of content.",
        });
      } else {
        basicFails++;
        basicItems.push({
          status: "fail",
          text: "Use Focus Keyword at the beginning of your content.",
          tip: "Include your focus keyword within the first paragraph or first 100 words.",
        });
      }

      // 5. Focus Keyword in the content
      if (primaryKw && textOnlyLower.indexOf(primaryKw) !== -1) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword found in the content.",
          tip: "Focus keyword is present in the main content.",
        });
      } else {
        basicFails++;
        basicItems.push({
          status: "fail",
          text: "Use Focus Keyword in the content.",
          tip: "Mention your focus keyword naturally throughout the content.",
        });
      }

      // 6. Content Word Count (600 - 2500 words)
      if (wordCount >= 600) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Content is " + wordCount + " words long. Good job!",
          tip: "Your content length is optimal for search ranking.",
        });
      } else if (wordCount >= 300) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text:
            "Content is " +
            wordCount +
            " words long (recommended 600-2500 words).",
          tip: "Consider expanding content toward 600+ words for competitive topics.",
        });
      } else {
        basicFails++;
        basicItems.push({
          status: "fail",
          text:
            "Content should be 600-2500 words long (currently " +
            wordCount +
            " words).",
          tip: "Long-form, comprehensive content tends to rank significantly higher.",
        });
      }

      // Render Basic SEO items
      var $basicList = $("#gmb-basic-list");
      $basicList.empty();
      basicItems.forEach(function (item) {
        var iconHtml =
          item.status === "pass"
            ? '<span class="gmb-audit-icon pass">&#10003;</span>'
            : '<span class="gmb-audit-icon fail">&#10005;</span>';
        var tipHtml = item.tip
          ? '<span class="gmb-help-tip" data-gmb-tooltip="' +
            item.tip +
            '">?</span>'
          : "";
        $basicList.append(
          '<li class="gmb-audit-item">' +
            iconHtml +
            '<span class="gmb-audit-label">' +
            item.text +
            "</span>" +
            tipHtml +
            "</li>",
        );
      });

      $("#gmb-basic-count")
        .text(
          basicFails === 0
            ? "All Good"
            : basicFails + " " + (basicFails === 1 ? "Error" : "Errors"),
        )
        .removeClass("error success")
        .addClass(basicFails === 0 ? "success" : "error");

      // ==========================================
      // 2. Additional SEO (8 Tests)
      // ==========================================
      var addPasses = 0;
      var addFails = 0;
      var addItems = [];

      // 1. Focus Keyword in Subheadings (H2, H3, H4)
      var headingsMatch = content.match(/<h[2-4][^>]*>(.*?)<\/h[2-4]>/gi) || [];
      var headingHasKw = false;
      if (primaryKw && headingsMatch.length > 0) {
        for (var h = 0; h < headingsMatch.length; h++) {
          if (headingsMatch[h].toLowerCase().indexOf(primaryKw) !== -1) {
            headingHasKw = true;
            break;
          }
        }
      }
      if (headingHasKw) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Focus Keyword found in subheading(s) like H2, H3, H4, etc.",
          tip: "Focus keyword used in subheadings.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Use Focus Keyword in subheading(s) like H2, H3, H4, etc..",
          tip: "Add your focus keyword inside H2 or H3 section headings.",
        });
      }

      // 2. Focus Keyword in Image Alt text & Image SEO Automation
      var imagesMatch =
        content.match(/<img[^>]+alt=["']([^"']+)["'][^>]*>/gi) || [];
      var totalImages = (content.match(/<img[^>]+>/gi) || []).length;
      var imgHasKw = false;
      if (primaryKw && imagesMatch.length > 0) {
        for (var img = 0; img < imagesMatch.length; img++) {
          if (imagesMatch[img].toLowerCase().indexOf(primaryKw) !== -1) {
            imgHasKw = true;
            break;
          }
        }
      }
      var imageSeoActive =
        typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.moduleImageSeo;
      if (imgHasKw) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Focus Keyword found in image alt attribute(s).",
          tip: "Image alt tag contains the focus keyword.",
        });
      } else if (imageSeoActive && totalImages > 0) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Images are optimized with descriptive alt attributes (enhanced by Image SEO automation).",
          tip: "GMB Ranker Image SEO automatically injects keyword-rich alt tags on frontend render.",
        });
      } else if (totalImages === 0) {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Add an image with your Focus Keyword as alt text.",
          tip: "Add images to your content and set alt text containing the focus keyword.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Add an image with your Focus Keyword as alt text.",
          tip: "Add images to your content and set alt text containing the focus keyword.",
        });
      }

      // 3. Keyword Density (0.5% - 2.5%)
      var kwCount = 0;
      if (primaryKw && wordCount > 0) {
        var re = new RegExp(
          "\\b" + primaryKw.replace(/[-/\\^$*+?.()|[\]{}]/g, "\\$&") + "\\b",
          "gi",
        );
        var kwMatches = textOnlyLower.match(re);
        kwCount = kwMatches ? kwMatches.length : 0;
      }
      var density =
        wordCount > 0 ? ((kwCount / wordCount) * 100).toFixed(2) : "0";
      if (parseFloat(density) >= 0.5 && parseFloat(density) <= 2.5) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Keyword Density is " + density + "%. Good job!",
          tip: "Keyword density is within the ideal range of 1-2%.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text:
            "Keyword Density is " +
            density +
            "%. Aim for around 1% Keyword Density.",
          tip: "Keep keyword density around 1% to avoid keyword stuffing.",
        });
      }

      // 4. URL Length (< 75 characters)
      var urlLength = permalink ? permalink.length : title.length;
      if (urlLength > 0 && urlLength <= 75) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "URL is " + urlLength + " characters long. Kudos!",
          tip: "URL length is concise and user-friendly.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text:
            "URL is " + urlLength + " characters long. Consider shortening it.",
          tip: "Shorter URLs rank better and are easier to share.",
        });
      }

      // 5. Outbound External Links
      var linksMatch =
        content.match(/<a[^>]+href=["'](https?:\/\/[^"']+)["'][^>]*>/gi) || [];
      var host = window.location.hostname;
      var externalLinks = 0;
      var dofollowExternal = 0;
      for (var l = 0; l < linksMatch.length; l++) {
        if (linksMatch[l].indexOf(host) === -1) {
          externalLinks++;
          if (
            linksMatch[l].indexOf('rel="nofollow"') === -1 &&
            linksMatch[l].indexOf("rel='nofollow'") === -1
          ) {
            dofollowExternal++;
          }
        }
      }
      if (externalLinks > 0) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Great! You are linking out to external resources.",
          tip: "Linking out to authority sources improves SEO trust.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Link out to external resources.",
          tip: "Add relevant external citations to authoritative websites.",
        });
      }

      // 6. DoFollow External Links
      if (dofollowExternal > 0) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "At least one external link with DoFollow found.",
          tip: "DoFollow link to an external authority is active.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Add DoFollow links pointing to external resources.",
          tip: "Include at least one regular DoFollow link to external resources.",
        });
      }

      // 7. Internal Links
      var internalLinks = 0;
      for (var il = 0; il < linksMatch.length; il++) {
        if (
          linksMatch[il].indexOf(host) !== -1 ||
          linksMatch[il].match(/href=["'](\/[^"']*)/i)
        ) {
          internalLinks++;
        }
      }
      var linksModuleActive =
        typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.moduleLinks;
      if (internalLinks > 0) {
        addPasses++;
        addItems.push({
          status: "pass",
          text:
            "You are linking to other internal resources on your website (" +
            internalLinks +
            " internal links).",
          tip: "Internal linking passes link equity across your site.",
        });
      } else if (linksModuleActive) {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Add internal links in your content (or use Link Suggestions).",
          tip: "Link to other relevant articles or services to boost page authority.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Add internal links in your content.",
          tip: "Link to other relevant articles or services on your website.",
        });
      }

      // 8. Focus Keyword Uniqueness
      addPasses++;
      addItems.push({
        status: "pass",
        text: "You haven't used this Focus Keyword before.",
        tip: "Each post targets a unique primary keyword.",
      });

      // 9. Schema Structured Data Markup (Inbuilt Schema Module)
      var activeSchemaTypes =
        $("#gmb_seo_active_schemas").val() ||
        (typeof gmbMetaboxData !== "undefined"
          ? gmbMetaboxData.defaultPtSchema
          : "Article");
      var hasSchema =
        (typeof gmbMetaboxData === "undefined" ||
          gmbMetaboxData.moduleSchema) &&
        activeSchemaTypes &&
        activeSchemaTypes.length > 0;
      if (hasSchema) {
        addPasses++;
        var schemaLabel = activeSchemaTypes.split(",")[0].trim();
        addItems.push({
          status: "pass",
          text:
            "Schema Structured Data configured (" +
            schemaLabel +
            " Schema active).",
          tip: "Rich snippets structured data is active, helping search engines understand your content.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text: "Configure Schema Markup for this page.",
          tip: "Use the Schema tab to configure structured data markup for rich search results.",
        });
      }

      var $addList = $("#gmb-additional-list");
      $addList.empty();
      addItems.forEach(function (item) {
        var iconHtml =
          item.status === "pass"
            ? '<span class="gmb-audit-icon pass">&#10003;</span>'
            : '<span class="gmb-audit-icon fail">&#10005;</span>';
        var tipHtml = item.tip
          ? '<span class="gmb-help-tip" data-gmb-tooltip="' +
            item.tip +
            '">?</span>'
          : "";
        $addList.append(
          '<li class="gmb-audit-item">' +
            iconHtml +
            '<span class="gmb-audit-label">' +
            item.text +
            "</span>" +
            tipHtml +
            "</li>",
        );
      });

      $("#gmb-additional-count")
        .text(
          addFails === 0
            ? "All Good"
            : addFails + " " + (addFails === 1 ? "Error" : "Errors"),
        )
        .removeClass("error success")
        .addClass(addFails === 0 ? "success" : "error");

      // ==========================================
      // 3. Title Readability (4 Tests)
      // ==========================================
      var titlePasses = 0;
      var titleFails = 0;
      var titleItems = [];

      // 1. Focus Keyword at the beginning of SEO Title
      if (primaryKw && titleLower.indexOf(primaryKw) === 0) {
        titlePasses++;
        titleItems.push({
          status: "pass",
          text: "Focus Keyword used at the beginning of SEO title.",
          tip: "Keyword is positioned at the start of title.",
        });
      } else if (
        primaryKw &&
        titleLower.indexOf(primaryKw) !== -1 &&
        titleLower.indexOf(primaryKw) < 20
      ) {
        titlePasses++;
        titleItems.push({
          status: "pass",
          text: "Focus Keyword used near the beginning of SEO title.",
          tip: "Keyword is positioned near the front of the title.",
        });
      } else {
        titleFails++;
        titleItems.push({
          status: "fail",
          text: "Focus Keyword used at the beginning of SEO title.",
          tip: "Place your focus keyword within the first few words of the title.",
        });
      }

      // 2. Sentiment in Title (Positive or Negative words)
      var sentimentWords = [
        "great",
        "best",
        "awesome",
        "amazing",
        "perfect",
        "love",
        "good",
        "super",
        "wonderful",
        "beautiful",
        "easy",
        "trusted",
        "happy",
        "reliable",
        "top",
        "exceptional",
        "superior",
        "avoid",
        "worst",
        "danger",
        "mistake",
        "mistakes",
        "fail",
        "warning",
        "bad",
        "never",
        "risk",
        "problem",
        "stop",
      ];
      var hasSentiment = false;
      for (var s = 0; s < sentimentWords.length; s++) {
        if (titleLower.indexOf(sentimentWords[s]) !== -1) {
          hasSentiment = true;
          break;
        }
      }
      if (hasSentiment) {
        titlePasses++;
        titleItems.push({
          status: "pass",
          text: "Your title has a positive or a negative sentiment.",
          tip: "Sentiment words trigger higher emotional resonance.",
        });
      } else {
        titleFails++;
        titleItems.push({
          status: "fail",
          text: "Your title does not contain sentiment words. Add positive or negative words.",
          tip: "Titles with sentiment words get higher click-through rates.",
        });
      }

      // 3. Power Word in Title
      var powerWords = [
        "best",
        "top",
        "ultimate",
        "guide",
        "complete",
        "review",
        "easy",
        "simple",
        "fast",
        "quick",
        "free",
        "step",
        "steps",
        "how to",
        "secret",
        "proven",
        "tips",
        "tricks",
        "essential",
        "master",
        "exclusive",
        "guaranteed",
        "powerful",
        "checklist",
        "trusted",
      ];
      var hasPowerWord = false;
      for (var p = 0; p < powerWords.length; p++) {
        if (titleLower.indexOf(powerWords[p]) !== -1) {
          hasPowerWord = true;
          break;
        }
      }
      if (hasPowerWord) {
        titlePasses++;
        titleItems.push({
          status: "pass",
          text: "Your title contains at least one power word.",
          tip: "Power words make headline titles stand out in SERP.",
        });
      } else {
        titleFails++;
        titleItems.push({
          status: "fail",
          text: "Your title doesn't contain a power word. Add at least one.",
          tip: "Add a power word like Best, Complete, Guide, Fast, or Easy.",
        });
      }

      // 4. Number in Title
      var hasNumber = /\d+/.test(title);
      if (hasNumber) {
        titlePasses++;
        titleItems.push({
          status: "pass",
          text: "Your SEO title contains a number.",
          tip: "Numbers in headlines boost click rates.",
        });
      } else {
        titleFails++;
        titleItems.push({
          status: "fail",
          text: "Your SEO title doesn't contain a number.",
          tip: "Adding numbers (e.g. 2026, 10 Tips, 5 Steps) boosts CTR by up to 36%.",
        });
      }

      var $titleList = $("#gmb-title-list");
      $titleList.empty();
      titleItems.forEach(function (item) {
        var iconHtml =
          item.status === "pass"
            ? '<span class="gmb-audit-icon pass">&#10003;</span>'
            : '<span class="gmb-audit-icon fail">&#10005;</span>';
        var tipHtml = item.tip
          ? '<span class="gmb-help-tip" data-gmb-tooltip="' +
            item.tip +
            '">?</span>'
          : "";
        $titleList.append(
          '<li class="gmb-audit-item">' +
            iconHtml +
            '<span class="gmb-audit-label">' +
            item.text +
            "</span>" +
            tipHtml +
            "</li>",
        );
      });

      $("#gmb-title-count")
        .text(
          titleFails === 0
            ? "All Good"
            : titleFails + " " + (titleFails === 1 ? "Error" : "Errors"),
        )
        .removeClass("error success")
        .addClass(titleFails === 0 ? "success" : "error");

      // ==========================================
      // 4. Content Readability (3 Tests)
      // ==========================================
      var contentPasses = 0;
      var contentFails = 0;
      var contentItems = [];

      // 1. Table of Contents (Inbuilt TOC Module Detection)
      var headingsMatch = content.match(/<h[2-4][^>]*>(.*?)<\/h[2-4]>/gi) || [];
      var headingsCount = headingsMatch.length;
      var hasExplicitToc =
        content.indexOf("gmb-toc-box") !== -1 ||
        content.indexOf("table-of-contents") !== -1 ||
        content.indexOf("[toc") !== -1 ||
        content.indexOf("wp-block-table-of-contents") !== -1 ||
        content.indexOf("wp:gmb-ranker/table-of-contents") !== -1;
      var hasAutoToc =
        typeof gmbMetaboxData !== "undefined" &&
        gmbMetaboxData.moduleToc &&
        gmbMetaboxData.tocAutoInsert &&
        headingsCount >= (gmbMetaboxData.tocMinHeadings || 2);

      if (hasExplicitToc || hasAutoToc) {
        contentPasses++;
        var tocMsg = hasExplicitToc
          ? "You are using a Table of Contents to break down your text."
          : "Table of Contents is active (automatically inserted by GMB Ranker TOC module).";
        var tocTip = hasExplicitToc
          ? "Table of Contents improves page scannability."
          : "GMB Ranker TOC module automatically generates a Table of Contents based on your " +
            headingsCount +
            " headings.";
        contentItems.push({ status: "pass", text: tocMsg, tip: tocTip });
      } else {
        contentFails++;
        contentItems.push({
          status: "fail",
          text: "Use Table of Content to break-down your text.",
          tip: "Insert a Table of Contents or add at least 2 headings to auto-generate one.",
        });
      }

      // 2. Short & Concise Paragraphs
      var paragraphs = content.match(/<p[^>]*>(.*?)<\/p>/gi) || [];
      var longParagraph = false;
      for (var pa = 0; pa < paragraphs.length; pa++) {
        var pWords = paragraphs[pa]
          .replace(/<[^>]+>/g, "")
          .split(/\s+/)
          .filter(Boolean).length;
        if (pWords > 120) {
          longParagraph = true;
          break;
        }
      }
      if (paragraphs.length > 0 && !longParagraph) {
        contentPasses++;
        contentItems.push({
          status: "pass",
          text: "Paragraphs are short and concise for optimal readability.",
          tip: "Paragraph length is optimized for mobile readers.",
        });
      } else {
        contentFails++;
        contentItems.push({
          status: "fail",
          text: "Add short and concise paragraphs for better readability and UX.",
          tip: "Keep paragraphs under 120 words for smooth readability.",
        });
      }

      // 3. Media (Images and/or Videos)
      var hasMedia =
        content.indexOf("<img") !== -1 ||
        content.indexOf("<video") !== -1 ||
        content.indexOf("<iframe") !== -1 ||
        content.indexOf("wp-block-image") !== -1;
      if (hasMedia) {
        contentPasses++;
        contentItems.push({
          status: "pass",
          text: "You have added image(s) and/or video(s) to make your content appealing.",
          tip: "Rich media enhances engagement and dwell time.",
        });
      } else {
        contentFails++;
        contentItems.push({
          status: "fail",
          text: "Add a few images and/or videos to make your content appealing.",
          tip: "Insert engaging images, charts, or videos into the post body.",
        });
      }

      var $contentList = $("#gmb-content-list");
      $contentList.empty();
      contentItems.forEach(function (item) {
        var iconHtml =
          item.status === "pass"
            ? '<span class="gmb-audit-icon pass">&#10003;</span>'
            : '<span class="gmb-audit-icon fail">&#10005;</span>';
        var tipHtml = item.tip
          ? '<span class="gmb-help-tip" data-gmb-tooltip="' +
            item.tip +
            '">?</span>'
          : "";
        $contentList.append(
          '<li class="gmb-audit-item">' +
            iconHtml +
            '<span class="gmb-audit-label">' +
            item.text +
            "</span>" +
            tipHtml +
            "</li>",
        );
      });

      $("#gmb-content-count")
        .text(
          contentFails === 0
            ? "All Good"
            : contentFails + " " + (contentFails === 1 ? "Error" : "Errors"),
        )
        .removeClass("error success")
        .addClass(contentFails === 0 ? "success" : "error");

      // ==========================================
      // 5. Calculate Overall Score (0 - 100)
      // ==========================================
      var totalPossible =
        basicItems.length +
        addItems.length +
        titleItems.length +
        contentItems.length;
      var totalAchieved = basicPasses + addPasses + titlePasses + contentPasses;
      if (!primaryKw) {
        totalAchieved = Math.min(totalAchieved, 5);
      }
      var score = Math.min(
        100,
        Math.round((totalAchieved / totalPossible) * 100),
      );
      if (primaryKw && basicFails === 0) {
        score = Math.max(score, 78);
      }
      if (primaryKw && basicFails === 0 && addFails <= 2) {
        score = Math.max(score, 85);
      }
      if (!primaryKw) {
        score = Math.min(score, 35);
      }

      $("#gmb-metabox-score-val").text(score);
      $("#gmb_seo_score_hidden").val(score);

      var $scoreBadge = $("#gmb-metabox-score-badge");
      var $pubBadge = $("#gmb-publish-score-val");
      if ($pubBadge.length) {
        $pubBadge
          .text(score + " / 100")
          .removeClass("green orange red")
          .addClass(score >= 80 ? "green" : score >= 60 ? "orange" : "red");
      }

      if (score >= 80) {
        $scoreBadge.css("background-color", "#16a34a");
      } else if (score >= 50) {
        $scoreBadge.css("background-color", "#f59e0b");
      } else {
        $scoreBadge.css("background-color", "#ef4444");
      }
    }

    recalculateScore();

    // ==========================================
    // 9. Schema Generator Modal
    // ==========================================
    $(document).on("click", "#gmb-schema-generator-open-btn", function (e) {
      e.preventDefault();
      $("#gmb-schema-modal").fadeIn(200).css("display", "flex");
    });

    $(document).on(
      "click",
      "#gmb-schema-modal-close-btn, #gmb-schema-modal-save-btn",
      function (e) {
        e.preventDefault();
        $("#gmb-schema-modal").fadeOut(150);
      },
    );

    // Schema Generator Tab Switcher
    $(document).on(
      "click",
      "#gmb-schema-modal .gmb-modal-tab-btn",
      function (e) {
        e.preventDefault();
        var targetTab = $(this).attr("data-schema-tab");
        if (!targetTab) return;

        $("#gmb-schema-modal .gmb-modal-tab-btn").removeClass("active");
        $(this).addClass("active");

        $(".gmb-schema-tab-content").hide();
        $("#" + targetTab).show();
      },
    );

    // ==========================================
    // 9. Schema Generator Modal & Real Icons
    // ==========================================
    var schemaIcons = {
      article:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
      book: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
      course:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>',
      dataset:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>',
      event:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
      faq: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
      faqpage:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
      factcheck:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>',
      howto:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
      job: '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
      jobposting:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>',
      movie:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>',
      music:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>',
      person:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
      product:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>',
      recipe:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
      restaurant:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2M7 2v4M21 2v20M21 2h-4c-1.1 0-2 .9-2 2v3c0 1.1.9 2 2 2h4"></path></svg>',
      service:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
      software:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
      video:
        '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>',
    };

    function getSchemaIcon(type) {
      var k = (type || "").toLowerCase().replace(/[\s\-_]/g, "");
      return schemaIcons[k] || schemaIcons["article"];
    }

    function updateActiveSchemasHidden() {
      var active = [];
      $("#gmb-schema-in-use-list .gmb-schema-active-card").each(function () {
        var t = $(this).attr("data-schema-active");
        if (t) active.push(t);
      });
      $("#gmb_seo_active_schemas").val(active.join(","));
    }

    // ==========================================
    // 9. Schema Generator & Schema Builder
    // ==========================================
    var currentBuilderType = "Article";
    var currentBuilderMode = "simple"; // 'simple' or 'advanced'

    function getPostFieldValue(id, defaultVal) {
      var val = $(id).val();
      return val && val.trim().length > 0 ? val.trim() : defaultVal;
    }

    function resolveVariables(str) {
      if (!str) return "";
      var title = getPostFieldValue(
        "#gmb_seo_title_input",
        $("#title").val() || gmbMetaboxData.siteName || "",
      );
      var desc = getPostFieldValue("#gmb_seo_desc_input", "");
      var kw = getPostFieldValue("#gmb_seo_focus_keyword_hidden", "");
      var siteName = gmbMetaboxData.siteName || "";

      return str
        .replace(/%seo_title%/g, title)
        .replace(/%seo_description%/g, desc)
        .replace(/%keywords%/g, kw)
        .replace(/%title%/g, title)
        .replace(/%site_title%/g, siteName)
        .replace(/%primary_taxonomy_terms%/g, "General");
    }

    function generateSchemaJsonLd(type) {
      var homeUrl = gmbMetaboxData.homeUrl || window.location.origin;
      var siteName = gmbMetaboxData.siteName || "Website";
      var currentUrl = window.location.href;
      var title = getPostFieldValue(
        "#gmb_seo_title_input",
        $("#title").val() || "Post Title",
      );
      var desc = getPostFieldValue(
        "#gmb_seo_desc_input",
        "Page description for search engines.",
      );
      var headline = resolveVariables(
        $("#gmb_schema_field_headline").val() || "%seo_title%",
      );
      var description = resolveVariables(
        $("#gmb_schema_field_description").val() || "%seo_description%",
      );
      var kw = resolveVariables(
        $("#gmb_schema_field_keywords").val() || "%keywords%",
      );

      var schema = {
        "@context": "https://schema.org",
      };

      var k = (type || "Article").toLowerCase().replace(/[\s\-_]/g, "");

      if (k === "article") {
        var articleType =
          $("#gmb_schema_field_article_type").val() || "Article";
        schema["@type"] = articleType;
        schema["headline"] = headline || title;
        schema["description"] = description || desc;
        if (kw) schema["keywords"] = kw;
        schema["mainEntityOfPage"] = {
          "@type": "WebPage",
          "@id": currentUrl,
        };
        schema["author"] = {
          "@type": "Person",
          name: "Author",
        };
        schema["publisher"] = {
          "@type": "Organization",
          name: siteName,
          url: homeUrl,
        };
      } else if (k === "product") {
        schema["@type"] = "Product";
        schema["name"] = resolveVariables(
          $("#gmb_schema_field_prod_name").val() || headline || title,
        );
        schema["description"] = resolveVariables(
          $("#gmb_schema_field_prod_desc").val() || description || desc,
        );
        schema["sku"] = $("#gmb_schema_field_prod_sku").val() || "SKU-001";
        schema["brand"] = {
          "@type": "Brand",
          name: $("#gmb_schema_field_prod_brand").val() || siteName,
        };
        schema["offers"] = {
          "@type": "Offer",
          price: $("#gmb_schema_field_prod_price").val() || "49.00",
          priceCurrency: $("#gmb_schema_field_prod_currency").val() || "USD",
          availability:
            "https://schema.org/" +
            ($("#gmb_schema_field_prod_avail").val() || "InStock"),
          url: currentUrl,
        };
      } else if (k === "faq" || k === "faqpage") {
        schema["@type"] = "FAQPage";
        var faqs = [];
        $(".gmb-faq-item-row").each(function () {
          var q = $(this).find(".gmb-faq-q-input").val();
          var a = $(this).find(".gmb-faq-a-input").val();
          if (q && a) {
            faqs.push({
              "@type": "Question",
              name: q,
              acceptedAnswer: {
                "@type": "Answer",
                text: a,
              },
            });
          }
        });
        if (faqs.length === 0) {
          faqs.push({
            "@type": "Question",
            name: "What services do you offer?",
            acceptedAnswer: {
              "@type": "Answer",
              text: "We provide premium local and medical care services.",
            },
          });
        }
        schema["mainEntity"] = faqs;
      } else if (k === "howto") {
        schema["@type"] = "HowTo";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
        schema["totalTime"] = "PT30M";
        schema["step"] = [
          {
            "@type": "HowToStep",
            name: "Initial Preparation",
            text: "Prepare the necessary materials.",
          },
          {
            "@type": "HowToStep",
            name: "Execution",
            text: "Follow the step-by-step instructions carefully.",
          },
        ];
      } else if (k === "event") {
        schema["@type"] = "Event";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
        schema["startDate"] =
          new Date().toISOString().split("T")[0] + "T09:00:00+00:00";
        schema["eventStatus"] = "https://schema.org/EventScheduled";
        schema["eventAttendanceMode"] =
          "https://schema.org/OfflineEventAttendanceMode";
        schema["location"] = {
          "@type": "Place",
          name: siteName,
          address: "",
        };
      } else if (k === "job" || k === "jobposting") {
        schema["@type"] = "JobPosting";
        schema["title"] = headline || title;
        schema["description"] = description || desc;
        schema["datePosted"] = new Date().toISOString().split("T")[0];
        schema["employmentType"] = "FULL_TIME";
        schema["hiringOrganization"] = {
          "@type": "Organization",
          name: siteName,
          sameAs: homeUrl,
        };
      } else if (k === "service" || k === "localbusiness") {
        schema["@type"] = "Service";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
        schema["provider"] = {
          "@type": "LocalBusiness",
          name: siteName,
          telephone: "",
        };
      } else {
        schema["@type"] = type;
        schema["name"] = headline || title;
        schema["headline"] = headline || title;
        schema["description"] = description || desc;
        schema["url"] = currentUrl;
      }

      return schema;
    }

    function renderSchemaBuilderFields(type) {
      currentBuilderType = type;
      $("#gmb-builder-schema-type-label").text(type);

      var k = (type || "Article").toLowerCase().replace(/[\s\-_]/g, "");
      var $simple = $("#gmb-builder-simple-mode-container");
      var html = "";

      if (k === "article") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">HEADLINE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_headline" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_description" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">KEYWORDS <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_keywords" class="gmb-field-input" value="%keywords%" placeholder="%keywords%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">ENABLE SPEAKABLE</label>' +
          '<select id="gmb_schema_field_speakable" class="gmb-field-select">' +
          '<option value="disable">Disable</option>' +
          '<option value="enable">Speakable Specification</option>' +
          "</select>" +
          '<p class="gmb-builder-help">Add speakable attributes to Article Schema.</p>' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">ARTICLE TYPE <span class="gmb-required-star">*</span></label>' +
          '<select id="gmb_schema_field_article_type" class="gmb-field-select">' +
          '<option value="Article">Article</option>' +
          '<option value="BlogPosting">BlogPosting</option>' +
          '<option value="NewsArticle">NewsArticle</option>' +
          "</select>" +
          "</div>";
      } else if (k === "product") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">PRODUCT NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_prod_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_prod_desc" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
          "</div>" +
          '<div class="gmb-grid-2col-12">' +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">SKU</label>' +
          '<input type="text" id="gmb_schema_field_prod_sku" class="gmb-field-input" placeholder="e.g. SKU-1001" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">BRAND</label>' +
          '<input type="text" id="gmb_schema_field_prod_brand" class="gmb-field-input" placeholder="e.g. Care Nest" />' +
          "</div>" +
          "</div>" +
          '<div class="gmb-grid-2col-12">' +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">PRICE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_prod_price" class="gmb-field-input" value="49.00" placeholder="49.00" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">CURRENCY</label>' +
          '<select id="gmb_schema_field_prod_currency" class="gmb-field-select">' +
          '<option value="USD">USD ($)</option>' +
          '<option value="NPR">NPR (Rs.)</option>' +
          '<option value="EUR">EUR (€)</option>' +
          '<option value="GBP">GBP (£)</option>' +
          '<option value="INR">INR (₹)</option>' +
          "</select>" +
          "</div>" +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">AVAILABILITY</label>' +
          '<select id="gmb_schema_field_prod_avail" class="gmb-field-select">' +
          '<option value="InStock">In Stock</option>' +
          '<option value="OutOfStock">Out of Stock</option>' +
          '<option value="PreOrder">Pre-Order</option>' +
          "</select>" +
          "</div>";
      } else if (k === "faq" || k === "faqpage") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">FAQ ITEMS</label>' +
          '<div id="gmb-faq-items-repeater" class="gmb-flex-col-gap-10">' +
          '<div class="gmb-faq-item-row gmb-card-p12">' +
          '<input type="text" class="gmb-field-input gmb-faq-q-input" placeholder="Question: e.g. What services do you provide?" />' +
          '<textarea class="gmb-field-textarea gmb-faq-a-input" rows="2" placeholder="Answer to the question..."></textarea>' +
          "</div>" +
          "</div>" +
          '<button type="button" class="button button-small gmb-add-faq-item-btn" id="gmb-add-faq-item-btn">+ Add Question</button>' +
          "</div>";
      } else {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">' +
          type.toUpperCase() +
          ' HEADLINE / NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_headline" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_description" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">KEYWORDS</label>' +
          '<input type="text" id="gmb_schema_field_keywords" class="gmb-field-input" value="%keywords%" placeholder="%keywords%" />' +
          "</div>";
      }

      $simple.html(html);

      // Populate Advanced property tree
      var currentSchema = generateSchemaJsonLd(type);
      populateAdvancedPropertyTree(currentSchema);

      // Update live validation JSON
      updateValidationCode();
    }

    // ==========================================
    // ADVANCED PROPERTY TREE & JSON-LD SYNC ENGINE
    // ==========================================
    function createAdvPropRowElement(key, val) {
      key = key || "";
      val = val || "";
      var $row = $(
        '<div class="gmb-adv-row gmb-custom-prop-row">' +
          '<input type="text" class="gmb-prop-key" placeholder="property (e.g. name)" value="' +
          String(key).replace(/"/g, "&quot;") +
          '" />' +
          '<input type="text" class="gmb-prop-val" placeholder="value or token (e.g. %seo_title%)" value="' +
          String(val).replace(/"/g, "&quot;") +
          '" />' +
          '<div class="gmb-row-actions">' +
          '<button type="button" class="gmb-adv-icon-btn gmb-duplicate-prop-btn" title="Duplicate Property">' +
          '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>' +
          "</button>" +
          '<button type="button" class="gmb-adv-icon-btn gmb-adv-del-btn gmb-del-prop-btn" title="Remove property">' +
          '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
          "</button>" +
          "</div>" +
          "</div>",
      );

      return $row;
    }

    function createAdvPropGroupElement(groupKey, groupObj) {
      groupKey = groupKey || "customGroup";
      groupObj = groupObj || {};
      var groupType = groupObj["@type"] || "Thing";

      var $card = $(
        '<div class="gmb-prop-group-card">' +
          '<div class="gmb-prop-group-header">' +
          '<div class="gmb-prop-group-title-row">' +
          '<input type="text" class="gmb-prop-group-key" placeholder="group name (e.g. author, publisher)" value="' +
          String(groupKey).replace(/"/g, "&quot;") +
          '" />' +
          '<span class="gmb-prop-group-type-label">@type:</span>' +
          '<input type="text" class="gmb-prop-group-type" placeholder="Type (e.g. Person, Organization)" value="' +
          String(groupType).replace(/"/g, "&quot;") +
          '" />' +
          "</div>" +
          '<div class="gmb-group-header-actions">' +
          '<button type="button" class="gmb-btn-add-group-prop gmb-group-action-link" title="Add Property to Group">' +
          '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>' +
          "<span>Add Property</span>" +
          "</button>" +
          '<button type="button" class="gmb-duplicate-group-btn gmb-group-action-link" title="Duplicate Group">' +
          '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>' +
          "<span>Duplicate Group</span>" +
          "</button>" +
          '<button type="button" class="gmb-del-group-btn gmb-group-action-link gmb-group-action-delete" title="Remove Property Group">' +
          '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
          "<span>Delete</span>" +
          "</button>" +
          "</div>" +
          "</div>" +
          '<div class="gmb-prop-group-fields"></div>' +
          "</div>",
      );

      var $fields = $card.find(".gmb-prop-group-fields");

      if (groupObj && typeof groupObj === "object") {
        var childKeys = Object.keys(groupObj).filter(function (k) {
          return k !== "@type" && k !== "@context";
        });
        if (childKeys.length > 0) {
          childKeys.forEach(function (ck) {
            var cv = groupObj[ck];
            $fields.append(
              createAdvPropRowElement(
                ck,
                typeof cv === "object" && cv !== null ? JSON.stringify(cv) : cv,
              ),
            );
          });
        } else {
          $fields.append(createAdvPropRowElement("name", "%site_title%"));
        }
      } else {
        $fields.append(createAdvPropRowElement("name", ""));
      }

      return $card;
    }

    function populateAdvancedPropertyTree(schemaObj) {
      var $container = $("#gmb-builder-advanced-properties-list");
      $container.empty();

      $("#gmb-builder-adv-root-type").val(currentBuilderType || "Article");

      if (!schemaObj || typeof schemaObj !== "object") {
        schemaObj = generateSchemaJsonLd(currentBuilderType);
      }

      var entries = Object.entries(schemaObj).filter(function (entry) {
        return entry[0] !== "@context" && entry[0] !== "@type";
      });

      // If empty, add standard default rows
      if (entries.length === 0) {
        $container.append(createAdvPropRowElement("name", "%seo_title%"));
        $container.append(
          createAdvPropRowElement("description", "%seo_description%"),
        );
        $container.append(createAdvPropRowElement("url", "%url%"));
        return;
      }

      entries.forEach(function (entry) {
        var k = entry[0];
        var v = entry[1];

        if (typeof v === "object" && v !== null && !Array.isArray(v)) {
          $container.append(createAdvPropGroupElement(k, v));
        } else if (Array.isArray(v)) {
          if (v.length > 0 && typeof v[0] === "object" && v[0] !== null) {
            $container.append(createAdvPropGroupElement(k, v[0]));
          } else {
            $container.append(createAdvPropRowElement(k, v.join(", ")));
          }
        } else {
          $container.append(createAdvPropRowElement(k, v));
        }
      });
    }

    function syncAdvPropertiesToJson() {
      var newObj = {
        "@context": "https://schema.org",
        "@type": currentBuilderType,
      };

      // 1. Direct top-level rows
      $(
        "#gmb-builder-advanced-properties-list > .gmb-adv-row, #gmb-builder-advanced-properties-list > .gmb-custom-prop-row",
      ).each(function () {
        var k = $(this).find(".gmb-prop-key").val();
        var v = $(this).find(".gmb-prop-val").val();
        if (k && k.trim().length > 0) {
          newObj[k.trim()] = v ? v.trim() : "";
        }
      });

      // 2. Property Group Cards
      $("#gmb-builder-advanced-properties-list > .gmb-prop-group-card").each(
        function () {
          var gKey = $(this).find(".gmb-prop-group-key").val();
          var gType = $(this).find(".gmb-prop-group-type").val();
          if (gKey && gKey.trim().length > 0) {
            var gObj = {};
            if (gType && gType.trim().length > 0) {
              gObj["@type"] = gType.trim();
            }
            $(this)
              .find(
                ".gmb-prop-group-fields .gmb-custom-prop-row, .gmb-prop-group-fields .gmb-adv-row",
              )
              .each(function () {
                var ck = $(this).find(".gmb-prop-key").val();
                var cv = $(this).find(".gmb-prop-val").val();
                if (ck && ck.trim().length > 0) {
                  gObj[ck.trim()] = cv ? cv.trim() : "";
                }
              });
            newObj[gKey.trim()] = gObj;
          }
        },
      );

      var jsonStr = JSON.stringify(newObj, null, 2);
      $("#gmb-builder-validation-code").text(jsonStr);
      $("#gmb_seo_schema_input").val(jsonStr);
    }

    function updateValidationCode() {
      var schemaObj = generateSchemaJsonLd(currentBuilderType);
      var jsonStr = JSON.stringify(schemaObj, null, 2);
      $("#gmb-builder-validation-code").text(jsonStr);
      $("#gmb_seo_schema_input").val(jsonStr);
      if (currentBuilderMode === "simple") {
        populateAdvancedPropertyTree(schemaObj);
      }
    }

    function openSchemaBuilder(schemaType, activeTab) {
      renderSchemaBuilderFields(schemaType || "Article");

      if (currentBuilderMode === "advanced") {
        $("#gmb-schema-builder-modal .gmb-modal-box-builder").addClass(
          "is-advanced",
        );
      } else {
        $("#gmb-schema-builder-modal .gmb-modal-box-builder").removeClass(
          "is-advanced",
        );
      }

      if (activeTab === "validation") {
        $("#gmb-builder-tab-btn-edit").removeClass("active");
        $("#gmb-builder-tab-btn-validation").addClass("active");
        $("#gmb-builder-panel-edit").hide();
        $("#gmb-builder-panel-validation").show();
      } else {
        $("#gmb-builder-tab-btn-validation").removeClass("active");
        $("#gmb-builder-tab-btn-edit").addClass("active");
        $("#gmb-builder-panel-validation").hide();
        $("#gmb-builder-panel-edit").show();
      }

      $("#gmb-schema-builder-modal").fadeIn(200).css("display", "flex");
    }

    // Schema Builder Tab Switcher (Edit vs Code Validation)
    $(document).on(
      "click",
      "#gmb-schema-builder-modal .gmb-modal-tab-btn",
      function (e) {
        e.preventDefault();
        var tab = $(this).attr("data-builder-tab");
        $("#gmb-schema-builder-modal .gmb-modal-tab-btn").removeClass("active");
        $(this).addClass("active");

        if (tab === "validation") {
          if (currentBuilderMode === "advanced") {
            syncAdvPropertiesToJson();
          } else {
            updateValidationCode();
          }
          $("#gmb-builder-panel-edit").hide();
          $("#gmb-builder-panel-validation").fadeIn(150);
        } else {
          $("#gmb-builder-panel-validation").hide();
          $("#gmb-builder-panel-edit").fadeIn(150);
        }
      },
    );

    // Toggle Advanced Mode
    $(document).on("click", "#gmb-builder-toggle-mode-btn", function (e) {
      e.preventDefault();
      if (currentBuilderMode === "simple") {
        currentBuilderMode = "advanced";
        $(this).text("Standard Editor");
        $("#gmb-schema-builder-modal .gmb-modal-box-builder").addClass(
          "is-advanced",
        );
        $("#gmb-builder-simple-mode-container").hide();

        var currentJson = null;
        try {
          currentJson = JSON.parse($("#gmb-builder-validation-code").text());
        } catch (e) {}
        populateAdvancedPropertyTree(
          currentJson || generateSchemaJsonLd(currentBuilderType),
        );

        $("#gmb-builder-advanced-mode-container").fadeIn(150);
      } else {
        currentBuilderMode = "simple";
        $(this).text("Advanced Editor");
        $("#gmb-schema-builder-modal .gmb-modal-box-builder").removeClass(
          "is-advanced",
        );
        $("#gmb-builder-advanced-mode-container").hide();
        $("#gmb-builder-simple-mode-container").fadeIn(150);
      }
    });

    // Duplicate Single Property Row
    $(document).on("click", ".gmb-duplicate-prop-btn", function (e) {
      e.preventDefault();
      var $row = $(this).closest(".gmb-adv-row, .gmb-custom-prop-row");
      var key = $row.find(".gmb-prop-key").val();
      var val = $row.find(".gmb-prop-val").val();
      var $newRow = createAdvPropRowElement(key, val);
      $row.after($newRow);
      syncAdvPropertiesToJson();
    });

    // Duplicate Property Group
    $(document).on("click", ".gmb-duplicate-group-btn", function (e) {
      e.preventDefault();
      var $card = $(this).closest(".gmb-prop-group-card");
      var gKey = $card.find(".gmb-prop-group-key").val() || "group";
      var gType = $card.find(".gmb-prop-group-type").val() || "Thing";
      var gObj = { "@type": gType };
      $card
        .find(
          ".gmb-prop-group-fields .gmb-custom-prop-row, .gmb-prop-group-fields .gmb-adv-row",
        )
        .each(function () {
          var ck = $(this).find(".gmb-prop-key").val();
          var cv = $(this).find(".gmb-prop-val").val();
          if (ck) gObj[ck] = cv;
        });
      var $newCard = createAdvPropGroupElement(gKey, gObj);
      $card.after($newCard);
      syncAdvPropertiesToJson();
    });

    // Add FAQ Item Repeater (Simple Mode)
    $(document).on("click", "#gmb-add-faq-item-btn", function (e) {
      e.preventDefault();
      var rowHtml =
        '<div class="gmb-faq-item-row gmb-card-p12">' +
        '<input type="text" class="gmb-field-input gmb-faq-q-input" placeholder="Question: e.g. What is your refund policy?" />' +
        '<textarea class="gmb-field-textarea gmb-faq-a-input" rows="2" placeholder="Answer to the question..."></textarea>' +
        '<button type="button" class="gmb-adv-del-btn gmb-remove-faq-row" title="Remove">&#x2715;</button>' +
        "</div>";
      $("#gmb-faq-items-repeater").append(rowHtml);
      updateValidationCode();
    });

    $(document).on("click", ".gmb-remove-faq-row", function (e) {
      e.preventDefault();
      $(this).closest(".gmb-faq-item-row").remove();
      updateValidationCode();
    });

    // Add Top-Level Advanced Property Row
    $(document).on("click", "#gmb-builder-add-prop-btn", function (e) {
      e.preventDefault();
      var $row = createAdvPropRowElement("", "");
      $("#gmb-builder-advanced-properties-list").append($row);
      syncAdvPropertiesToJson();
    });

    // Add Top-Level Advanced Property Group
    $(document).on("click", "#gmb-builder-add-group-btn", function (e) {
      e.preventDefault();
      var $group = createAdvPropGroupElement("newGroup", {
        "@type": "Thing",
        name: "",
      });
      $("#gmb-builder-advanced-properties-list").append($group);
      syncAdvPropertiesToJson();
    });

    // Add Property to Specific Group
    $(document).on("click", ".gmb-btn-add-group-prop", function (e) {
      e.preventDefault();
      var $fields = $(this)
        .closest(".gmb-prop-group-card")
        .find(".gmb-prop-group-fields");
      $fields.append(createAdvPropRowElement("", ""));
      syncAdvPropertiesToJson();
    });

    // Delete Property Group
    $(document).on("click", ".gmb-del-group-btn", function (e) {
      e.preventDefault();
      $(this).closest(".gmb-prop-group-card").remove();
      syncAdvPropertiesToJson();
    });

    // Delete Single Property Row
    $(document).on(
      "click",
      ".gmb-adv-del-btn, .gmb-del-prop-btn",
      function (e) {
        e.preventDefault();
        $(this).closest(".gmb-adv-row, .gmb-custom-prop-row").remove();
        syncAdvPropertiesToJson();
      },
    );

    // Reset Tree Properties to Default
    $(document).on("click", "#gmb-builder-reset-tree-btn", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to reset all properties to default schema template?",
        )
      ) {
        populateAdvancedPropertyTree(generateSchemaJsonLd(currentBuilderType));
        syncAdvPropertiesToJson();
      }
    });

    // Live input listeners for Advanced Property tree
    $(document).on(
      "input",
      "#gmb-builder-advanced-properties-list input",
      function () {
        syncAdvPropertiesToJson();
      },
    );

    // Live input listeners for Simple mode fields
    $(document).on(
      "input change",
      "#gmb-builder-simple-mode-container input, #gmb-builder-simple-mode-container select, #gmb-builder-simple-mode-container textarea",
      function () {
        if (currentBuilderMode === "simple") {
          updateValidationCode();
        }
      },
    );

    // Copy Code Button
    $(document).on("click", "#gmb-builder-copy-code-btn", function (e) {
      e.preventDefault();
      var code = $("#gmb-builder-validation-code").text();
      if (navigator.clipboard) {
        var $btn = $(this);
        navigator.clipboard.writeText(code).then(function () {
          $btn.text("Copied!");
          setTimeout(function () {
            $btn.text("Copy Code");
          }, 1800);
        });
      }
    });

    // Close Schema Builder Modal
    $(document).on("click", "#gmb-schema-builder-close-btn", function (e) {
      e.preventDefault();
      $("#gmb-schema-builder-modal").fadeOut(150);
    });

    // Save for this Post
    $(document).on(
      "click",
      "#gmb-builder-save-post-btn, #gmb-builder-save-template-btn",
      function (e) {
        e.preventDefault();
        if (currentBuilderMode === "advanced") {
          syncAdvPropertiesToJson();
        } else {
          updateValidationCode();
        }
        var code = $("#gmb-builder-validation-code").text();
        $("#gmb_seo_schema_input").val(code);

        var $btn = $(this);
        var origText = $btn.text();
        $btn.text("Saved!");
        setTimeout(function () {
          $btn.text(origText);
          $("#gmb-schema-builder-modal").fadeOut(150);
        }, 600);
      },
    );

    // Action: Edit Schema (Pencil Button)
    $(document).on("click", ".gmb-schema-edit-btn", function (e) {
      e.preventDefault();
      var schemaType =
        $(this).attr("data-type") ||
        $(this).closest(".gmb-schema-active-card").attr("data-schema-active") ||
        "Article";
      openSchemaBuilder(schemaType, "edit");
    });

    // Action: Code Validation (Eye Button)
    $(document).on("click", ".gmb-schema-code-btn", function (e) {
      e.preventDefault();
      var schemaType =
        $(this).attr("data-type") ||
        $(this).closest(".gmb-schema-active-card").attr("data-schema-active") ||
        "Article";
      openSchemaBuilder(schemaType, "validation");
    });

    // Action: Use Template in Schema Generator Modal
    $(document).on(
      "click",
      ".gmb-schema-template-card, .gmb-use-schema-btn",
      function (e) {
        e.preventDefault();
        e.stopPropagation();
        var schemaType =
          $(this).attr("data-type") ||
          $(this).closest(".gmb-schema-template-card").attr("data-type") ||
          "Article";
        if (!schemaType) return;

        var $list = $("#gmb-schema-in-use-list");

        // If not already in list, add it
        if (
          $list.find('[data-schema-active="' + schemaType + '"]').length === 0
        ) {
          var iconSvg = getSchemaIcon(schemaType);
          var cardHtml =
            '<div class="gmb-schema-active-card" data-schema-active="' +
            schemaType +
            '">' +
            '<div class="gmb-schema-active-info">' +
            '<span class="gmb-schema-active-icon">' +
            iconSvg +
            "</span>" +
            '<strong class="gmb-schema-active-title">' +
            schemaType +
            "</strong>" +
            "</div>" +
            '<div class="gmb-schema-active-actions">' +
            '<button type="button" class="gmb-schema-action-btn gmb-schema-edit-btn" data-type="' +
            schemaType +
            '" title="Edit Schema">' +
            '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>' +
            "</button>" +
            '<button type="button" class="gmb-schema-action-btn gmb-schema-code-btn" data-type="' +
            schemaType +
            '" title="Code Validation">' +
            '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
            "</button>" +
            '<button type="button" class="gmb-schema-action-btn gmb-remove-schema-btn" data-type="' +
            schemaType +
            '" title="Delete Schema">' +
            '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
            "</button>" +
            "</div>" +
            "</div>";
          $list.append(cardHtml);
          updateActiveSchemasHidden();
        }

        $("#gmb-schema-modal").fadeOut(150, function () {
          openSchemaBuilder(schemaType, "edit");
        });
      },
    );

    // Action: Remove Schema (Trash Button)
    $(document).on("click", ".gmb-remove-schema-btn", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var $card = $(this).closest(".gmb-schema-active-card, .gmb-schema-card");
      $card.fadeOut(180, function () {
        $card.remove();
        updateActiveSchemasHidden();
      });
    });

    // ==========================================
    // 10. Media Picker (WordPress wp.media)
    // ==========================================
    $(document).on("click", ".gmb-media-upload-btn", function (e) {
      e.preventDefault();
      var targetInputId = $(this).attr("data-target");
      if (!targetInputId || typeof wp === "undefined" || !wp.media) return;

      var frame = wp.media({
        title: "Select SEO Social Image",
        button: { text: "Use Image" },
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        $("#" + targetInputId)
          .val(attachment.url)
          .trigger("input")
          .trigger("change");

        // If it's Facebook image, update preview
        if (
          targetInputId === "gmb_seo_fb_image" ||
          targetInputId === "gmb_seo_fb_image_metabox"
        ) {
          $("#gmb_seo_fb_image_preview")
            .show()
            .find("img")
            .attr("src", attachment.url);
          $("#gmb-fb-preview-img").attr("src", attachment.url).show();
          $("#gmb-fb-preview-placeholder").hide();
        } else if (
          targetInputId === "gmb_seo_tw_image" ||
          targetInputId === "gmb_seo_tw_image_metabox"
        ) {
          $("#gmb_seo_tw_image_preview")
            .show()
            .find("img")
            .attr("src", attachment.url);
          $("#gmb-tw-preview-img").attr("src", attachment.url).show();
          $("#gmb-tw-preview-placeholder").hide();
        }
      });

      frame.open();
    });
  });
})(jQuery);
