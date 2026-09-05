/**
 * GMB Ranker SEO — Post Editor Metabox JavaScript
 * Handles interactive tabs, live snippet preview, focus keywords, content audit, social cards, and schema generator.
 */
(function ($) {
  "use strict";

  function escAttr(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  window.gmbOpenSchemaModal = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    var $modal = $("#gmb-schema-modal");
    if ($modal.length) {
      $modal.appendTo("body");
      $modal.addClass("active is-open").css("display", "flex").show();
    }
  };

  window.gmbUseSchemaTemplate = function (schemaType, e, openBuilder) {
    if (e && e.preventDefault) e.preventDefault();
    if (e && e.stopPropagation) e.stopPropagation();
    if (!schemaType) return;

    var $list = $("#gmb-schema-in-use-list");
    var schemaTypeEscaped = escAttr(schemaType);
    var schemaTypeHtml = escHtml(schemaType);
    var schemaIcon = (typeof window.gmbGetSchemaIcon === "function")
      ? window.gmbGetSchemaIcon(schemaType)
      : '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';
    if ($list.find('.gmb-schema-active-card').filter(function () {
      return $(this).attr('data-schema-active') === String(schemaType);
    }).length === 0) {
      var cardHtml =
        '<div class="gmb-schema-active-card" data-schema-active="' +
        schemaTypeEscaped +
        '">' +
        '<div class="gmb-schema-active-info">' +
        '<span class="gmb-schema-active-icon">' +
        schemaIcon +
        "</span>" +
        '<strong class="gmb-schema-active-title">' +
        schemaTypeHtml +
        "</strong>" +
        "</div>" +
        '<div class="gmb-schema-active-actions">' +
        '<button type="button" class="gmb-schema-action-btn gmb-schema-edit-btn" data-type="' +
        schemaTypeEscaped +
        '" title="Edit Schema">' +
        '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>' +
        "</button>" +
        '<button type="button" class="gmb-schema-action-btn gmb-schema-code-btn" data-type="' +
        schemaTypeEscaped +
        '" title="Code Validation">' +
        '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
        "</button>" +
        '<button type="button" class="gmb-schema-action-btn gmb-remove-schema-btn" data-type="' +
        schemaTypeEscaped +
        '" title="Delete Schema">' +
        '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
        "</button>" +
        "</div>" +
        "</div>";
      $("#gmb-no-active-schema-notice").hide();
      $list.append(cardHtml);
      var active = [];
      $("#gmb-schema-in-use-list .gmb-schema-active-card").each(function () {
        var t = $(this).attr("data-schema-active");
        if (t) active.push(t);
      });
      $("#gmb_seo_active_schemas").val(active.join(","));
    }

    $("#gmb-schema-modal").removeClass("active is-open").hide();
    if (openBuilder !== false && typeof window.gmbOpenSchemaBuilder === "function") {
      window.gmbOpenSchemaBuilder(schemaType, "edit");
    }
  };

  $(document).on("click", "#gmb-schema-generator-open-btn", function (e) {
    window.gmbOpenSchemaModal(e);
  });

  $(document).on("click", ".gmb-schema-edit-btn, .gmb-schema-code-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();
    var type = $(this).attr("data-type");
    if (type && typeof window.gmbOpenSchemaBuilder === "function") {
      window.gmbOpenSchemaBuilder(type, $(this).hasClass("gmb-schema-code-btn") ? "validation" : "edit");
    }
  });

  window.gmbOpenSnippetModal = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    var $modal = $("#gmb-snippet-modal");
    if ($modal.length) {
      $modal.appendTo("body");
      $modal.addClass("active is-open").css("display", "flex").show();
    }
  };

  window.gmbCloseSnippetModal = function (e) {
    if (e && e.preventDefault) e.preventDefault();
    $("#gmb-snippet-modal").removeClass("active is-open").hide();
  };

  window.gmbSwitchTab = function (targetTab, btnEl) {
    if (!targetTab) return;
    var $btn = btnEl ? $(btnEl) : $('.gmb-tab-btn[data-tab="' + targetTab + '"]');
    var $container = $btn.length ? $btn.closest(".gmb-seo-meta-container") : $(".gmb-seo-meta-container");
    if (!$container.length) $container = $(".gmb-seo-meta-container");

    $container.find(".gmb-seo-tabs .gmb-tab-btn").removeClass("active is-active");
    if ($btn.length) $btn.addClass("active is-active");
    $('.gmb-tab-btn[data-tab="' + targetTab + '"]').addClass("active is-active");
    $container.find(".gmb-seo-tabs .gmb-tab-btn").attr("aria-selected", "false");
    $container.find('.gmb-seo-tabs .gmb-tab-btn[data-tab="' + targetTab + '"]').attr("aria-selected", "true");

    $container.find(".gmb-tab-content").removeClass("active").hide();
    $container.find(".gmb-tab-content").attr("aria-hidden", "true");
    $("#" + targetTab).addClass("active").fadeIn(150);
    $("#" + targetTab).attr("aria-hidden", "false");
  };

  $(document).ready(function () {
    function initModalHierarchy() {
      $(".gmb-modal-backdrop, .gmb-modal-overlay").each(function () {
        if (!$(this).parent().is("body")) {
          $(this).appendTo("body");
        }
      });
    }
    initModalHierarchy();
    setTimeout(initModalHierarchy, 500);

    // ==========================================
    // ==========================================
    // Single Page AI SEO Auto-Fix Handler (NeuronWriter 3-Step Flow)
    // ==========================================
    var currentAiStep = 1;
    var stepTimer = null;
    var progressPollTimer = null;
    var globalAjaxResultData = null;
    var keywordCannibalizationCache = {};
    var pendingCannibalizationCheck = null;

    function setAiModalStep(step) {
      window.setAiModalStep = setAiModalStep;
      currentAiStep = step;
      $(".gmb-step-badge").removeClass("active");
      $("#gmb-step-badge-" + step).addClass("active");

      $("#gmb-ai-post-modal-setup").addClass("gmb-hidden").attr("style", "display: none !important;");
      $("#gmb-ai-post-modal-loading").addClass("gmb-hidden").attr("style", "display: none !important;");
      $("#gmb-ai-post-modal-content").addClass("gmb-hidden").attr("style", "display: none !important;");

      if (step === 1) {
        $(".gmb-modal-lg").css("min-height", "auto");
        $("#gmb-ai-post-modal-setup").removeClass("gmb-hidden").attr("style", "display: block !important;");
        $("#gmb-ai-setup-start-btn").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").text("Start AI Analysis");
        $("#gmb-ai-running-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
        $("#gmb-ai-post-modal-prev").attr("style", "display: none !important;").addClass("gmb-hidden");
        $("#gmb-ai-post-apply-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
      } else if (step === 2) {
        $(".gmb-modal-lg").css("min-height", "720px");
        $("#gmb-ai-post-modal-loading").removeClass("gmb-hidden").attr("style", "display: block !important;");

        if (globalAjaxResultData) {
          // Research already completed! Show completed state without buffering
          if (stepTimer) clearInterval(stepTimer);
          for (var i = 1; i <= 8; i++) {
            var $item = $("#gmb-res-step-" + i);
            $item.removeClass("active").addClass("completed");
            $item.find(".gmb-step-active-ring").remove();
            $item.find(".gmb-step-status-pill").removeClass("pending in-progress").addClass("completed").text("Completed");
          }
          $("#gmb-active-step-counter").text("Step 8 of 8");
          $("#gmb-active-step-title").text("Finalizing Results");
          $("#gmb-active-step-desc").text("Validating data & preparing your evidence-based report.");
          $("#gmb-active-progress-fill").css("width", "100%");
          $("#gmb-active-progress-percent").text("100%");
          $("#gmb-live-tasks-list").html(
            '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Transparent SEO score calculated</div>' +
            '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Report generation complete</div>'
          );

          $("#gmb-ai-setup-start-btn").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").text("Next ->");
          $("#gmb-ai-running-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
          $("#gmb-ai-post-modal-prev").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").text("Previous");
          $("#gmb-ai-post-apply-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
        } else {
          $("#gmb-ai-setup-start-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
          $("#gmb-ai-running-btn").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden");
          $("#gmb-ai-post-modal-prev").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").text("Previous");
          $("#gmb-ai-post-apply-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
        }
      } else if (step === 3) {
        $(".gmb-modal-lg").css("min-height", "720px");
        $("#gmb-ai-post-modal-content").removeClass("gmb-hidden").attr("style", "display: block !important;");
        $("#gmb-ai-setup-start-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
        $("#gmb-ai-running-btn").attr("style", "display: none !important;").addClass("gmb-hidden");
        $("#gmb-ai-post-modal-prev").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").text("Previous");
        $("#gmb-ai-post-apply-btn").attr("style", "display: inline-flex !important;").removeClass("gmb-hidden").prop("disabled", false);
      }
    }

    // Previous Button Click Handler to Toggle Backwards
    $(document).on("click", "#gmb-ai-post-modal-prev", function (e) {
      e.preventDefault();
      if (currentAiStep === 3) {
        setAiModalStep(2);
      } else if (currentAiStep === 2) {
        setAiModalStep(1);
      }
    });

    // Make Step Header Badges Clickable for Easy Navigation
    $(document).on("click", ".gmb-step-badge", function () {
      var badgeId = $(this).attr("id");
      if (badgeId === "gmb-step-badge-1") {
        setAiModalStep(1);
      } else if (badgeId === "gmb-step-badge-2") {
        setAiModalStep(2);
      } else if (badgeId === "gmb-step-badge-3") {
        if (globalAjaxResultData) {
          setAiModalStep(3);
        }
      }
    });

    // Open Modal & Pre-fill Step 1 Setup
    window.gmbOpenAiModal = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      var $modal = $("#gmb-ai-post-seo-modal");

      if (!$modal.length) {
        alert("AI Modal element not found. Please refresh the page.");
        return;
      }

      $modal.appendTo("body");
      $modal.css("display", "flex").addClass("active");

      // Pre-fill setup fields
      var postTitle = $("#title").val() || "";
      if (!postTitle && typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.select("core/editor")) {
        postTitle = wp.data.select("core/editor").getEditedPostAttribute("title") || "";
      }
      $("#gmb-ai-setup-title").val(postTitle);

      var curFocus = $("#gmb_seo_focus_keyword_hidden").val() || "";
      if (!curFocus && typeof keywords !== "undefined" && Array.isArray(keywords) && keywords.length > 0) {
        curFocus = keywords[0];
      }
      $("#gmb-ai-setup-query").val(curFocus);

      var slug = $("#post_name").val() || $("#editable-post-name").text() || "";
      var homeUrl = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.homeUrl ? gmbMetaboxData.homeUrl : window.location.origin) + "/";
      $("#gmb-ai-setup-url").val(homeUrl + slug);

      setAiModalStep(1);
    };

    $(document).on("change", "#gmb-ai-setup-mode", function () {
      var m = $(this).val();
      if (m === "create") {
        $("#gmb-ai-setup-url").prop("readonly", false).attr("placeholder", "Proposed URL permalink...");
        $("#gmb-ai-setup-instructions").attr("placeholder", "Enter target audience, writing instructions, required subtopics, CTA requirements, or brand guidelines (Optional - AI will research automatically if empty)...");
        var kw = $("#gmb-ai-setup-query").val().trim() || $("#gmb-ai-setup-title").val().trim();
        if (kw) {
          var homeUrl = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.homeUrl ? gmbMetaboxData.homeUrl : window.location.origin) + "/";
          var proposedSlug = kw.toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
          $("#gmb-ai-setup-url").val(homeUrl + proposedSlug);
        }
      } else {
        $("#gmb-ai-setup-url").prop("readonly", true);
        var slug = $("#post_name").val() || $("#editable-post-name").text() || "";
        var homeUrl = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.homeUrl ? gmbMetaboxData.homeUrl : window.location.origin) + "/";
        $("#gmb-ai-setup-url").val(homeUrl + slug);
        $("#gmb-ai-setup-instructions").attr("placeholder", "e.g. Focus on key benefits, specific target audience credentials, subtopics, or custom call-to-action requirements...");
      }
    });

    $(document).on("click", "#gmb-ai-optimize-post-btn, .gmb-btn--ai-post, [data-action='gmb-open-ai-modal']", function (e) {
      window.gmbOpenAiModal(e);
    });

    // Step 1: Click "Start AI Analysis" / Next
    $(document).on("click", "#gmb-ai-setup-start-btn", function (e) {
      e.preventDefault();

      if (currentAiStep === 2 && globalAjaxResultData) {
        setAiModalStep(3);
        return;
      }
      globalAjaxResultData = null;

      var postTitle = $("#gmb-ai-setup-title").val().trim();
      var targetQuery = $("#gmb-ai-setup-query").val().trim();
      var mode = $("#gmb-ai-setup-mode").val();
      var countryVal = $("#gmb-ai-setup-country").val();
      var countryText = $("#gmb-ai-setup-country option:selected").text().split("|")[0].trim();
      var language = $("#gmb-ai-setup-language").val();
      var tone = $("#gmb-ai-setup-tone").length ? $("#gmb-ai-setup-tone").val() : "auto";
      var intent = $("#gmb-ai-setup-intent").length ? $("#gmb-ai-setup-intent").val() : "auto";

      if (!targetQuery) {
        alert("Please enter a target query/keyword to rank for.");
        $("#gmb-ai-setup-query").focus();
        return;
      }

      setAiModalStep(2);

      // Populate Overview Panel & SERP Top Bar
      $("#gmb-serp-kw-pill").text(targetQuery);
      $("#gmb-overview-query").text(targetQuery);
      var currentSlug = $("#post_name").val() || $("#editable-post-name").text() || "";
      var fullUrl = $("#gmb-ai-setup-url").val() || (window.location.origin + "/" + currentSlug);
      $("#gmb-overview-url").attr("href", fullUrl).text(fullUrl.length > 30 ? fullUrl.substring(0, 28) + "..." : fullUrl);
      $("#gmb-overview-country").text(countryText);
      $("#gmb-overview-language").text($("#gmb-ai-setup-language option:selected").text());
      $("#gmb-overview-mode").text($("#gmb-ai-setup-mode option:selected").text().split("(")[0].trim());
      $("#gmb-overview-tone").text($("#gmb-ai-setup-tone option:selected").text() || tone);
      $("#gmb-overview-intent").text($("#gmb-ai-setup-intent option:selected").text() || intent);

      var researchSteps = [
        {
          stepNum: 1,
          title: "Analyzing Current Page Structure & Metadata",
          desc: "We're extracting and evaluating key elements from your WordPress post.",
          progress: 15,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Post content loaded</div>' +
                     '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Extracting SEO metadata (title, description, schema)</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Analyzing headings structure (H1-H3)...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Scanning images and alt text</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Calculating readability metrics</div>'
        },
        {
          stepNum: 2,
          title: "Detecting Search Intent",
          desc: "Evaluating search query intent (Informational, Commercial, Transactional, Local).",
          progress: 30,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Query intent classified</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Analyzing SERP features & entity confidence...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Mapping user search goal</div>'
        },
        {
          stepNum: 3,
          title: "Checking SERP Data",
          desc: "Collecting top ranking competitor pages for target region.",
          progress: 45,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Targeted Google SERP endpoint</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Fetching top 10 SERP competitor URLs...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Sampling competitor word counts</div>'
        },
        {
          stepNum: 4,
          title: "Preparing Content Benchmarks",
          desc: "Extracting statistical percentiles for word count, headings, and images.",
          progress: 60,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Calculating 25th & 75th percentiles</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Benchmarking H2/H3 subheading density...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Analyzing visual image count distribution</div>'
        },
        {
          stepNum: 5,
          title: "Semantic & Entity Analysis",
          desc: "Building topic and entity coverage model for NLP optimization.",
          progress: 75,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Extracting n-gram entity clusters</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Identifying underused & overused semantic terms...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Mapping primary and secondary entities</div>'
        },
        {
          stepNum: 6,
          title: "Content Gap Analysis",
          desc: "Identifying missing subtopics, PAA questions, and heading gaps.",
          progress: 85,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Comparing H2/H3 coverage against competitors</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Extracting People Also Ask (PAA) questions...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Detecting missing information gain sections</div>'
        },
        {
          stepNum: 7,
          title: "Optimization Strategy",
          desc: "Generating evidence-based recommendations and title candidates.",
          progress: 95,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Formulating CTR title candidates</div>' +
                     '<div class="gmb-task-row running"><span class="task-spinner"></span> Structuring PAS meta description...</div>' +
                     '<div class="gmb-task-row pending"><span class="task-hollow-circle"></span> Validating URL slug safety</div>'
        },
        {
          stepNum: 8,
          title: "Finalizing Results",
          desc: "Validating data & preparing your evidence-based report.",
          progress: 100,
          tasksHtml: '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Transparent SEO score calculated</div>' +
                     '<div class="gmb-task-row done"><span class="task-check-circle">✓</span> Report generation complete</div>'
        }
      ];

      var ajaxFinishedData = null;

      function updateStepUI(idx) {
        var s = researchSteps[idx];
        $("#gmb-active-step-counter").text("Step " + s.stepNum + " of 8");
        $("#gmb-active-step-title").text(s.title);
        $("#gmb-active-step-desc").text(s.desc);
        $("#gmb-active-progress-fill").css("width", "0%");
        $("#gmb-active-progress-percent").text("Processing...");
        $("#gmb-live-tasks-list").html(s.tasksHtml);

        for (var i = 1; i <= 8; i++) {
          var $item = $("#gmb-res-step-" + i);
          var $pill = $item.find(".gmb-step-status-pill");
          if (i < s.stepNum) {
            $item.removeClass("active").addClass("completed");
            $pill.removeClass("pending in-progress").addClass("completed").text("Completed");
          } else if (i === s.stepNum) {
            $item.addClass("active").removeClass("completed");
            $pill.removeClass("pending completed").addClass("in-progress").text("In Progress");
          } else {
            $item.removeClass("active completed");
            $pill.removeClass("in-progress completed").addClass("pending").text("Pending");
          }
        }
      }

      function populateAndShowStep3(data) {
        if (stepTimer) clearInterval(stepTimer);

        // Mark all 8 timeline items completed visually
        for (var i = 1; i <= 8; i++) {
          var $item = $("#gmb-res-step-" + i);
          $item.removeClass("active").addClass("completed");
          $item.find(".gmb-step-status-pill").removeClass("pending in-progress").addClass("completed").text("Completed");
        }
        $("#gmb-active-progress-fill").css("width", "100%");
        $("#gmb-active-progress-percent").text("100%");

        setTimeout(function () {
          setAiModalStep(3);

          $("#gmb-ai-result-query-label").text(data.target ? data.target.focus_keyword : targetQuery);
          
          var countryFlag = "🌐";
          var cUpper = (countryText || "").toUpperCase();
          if (cUpper.indexOf("NEPAL") !== -1 || cUpper.indexOf("NP") !== -1) countryFlag = "🇳🇵";
          else if (cUpper.indexOf("UNITED STATES") !== -1 || cUpper.indexOf("US") !== -1) countryFlag = "🇺🇸";
          else if (cUpper.indexOf("UNITED KINGDOM") !== -1 || cUpper.indexOf("UK") !== -1 || cUpper.indexOf("GB") !== -1) countryFlag = "🇬🇧";
          else if (cUpper.indexOf("CANADA") !== -1 || cUpper.indexOf("CA") !== -1) countryFlag = "🇨🇦";
          else if (cUpper.indexOf("AUSTRALIA") !== -1 || cUpper.indexOf("AU") !== -1) countryFlag = "🇦🇺";
          else if (cUpper.indexOf("INDIA") !== -1 || cUpper.indexOf("IN") !== -1) countryFlag = "🇮🇳";

          var langText = $("#gmb-ai-setup-language option:selected").text() || "English";
          var langFlag = "🌐";
          var lUpper = langText.toUpperCase();
          if (lUpper.indexOf("ENGLISH") !== -1) langFlag = "🇬🇧";
          else if (lUpper.indexOf("NEPALI") !== -1) langFlag = "🇳🇵";
          else if (lUpper.indexOf("SPANISH") !== -1) langFlag = "🇪🇸";
          else if (lUpper.indexOf("FRENCH") !== -1) langFlag = "🇫🇷";
          else if (lUpper.indexOf("GERMAN") !== -1) langFlag = "🇩🇪";

          $("#gmb-ai-result-country-badge").html(countryFlag + " " + countryText);
          $("#gmb-ai-result-language-badge").html(langFlag + " " + langText);

          // Populate Optimization Potential Score
          var labelText = "";
          if (data.score && data.score.potential_label && !data.score.potential_label.startsWith("0 /")) {
            labelText = data.score.potential_label;
          } else {
            var curVal = (data.score && (typeof data.score.current !== "undefined" ? data.score.current : data.score.current_score)) || 0;
            curVal = parseInt(curVal, 10);
            if (isNaN(curVal) || curVal <= 0) {
              var domVal = parseInt($("#gmb-metabox-score-val").text(), 10);
              if (!isNaN(domVal) && domVal > 0) {
                curVal = domVal;
              }
            }
            if (curVal > 0) {
              var potVal = (data.score && data.score.potential) ? parseInt(data.score.potential, 10) : Math.min(100, curVal + 15);
              labelText = curVal + " / 100 (Potential: " + potVal + " / 100)";
            } else {
              labelText = "Score unavailable";
            }
          }
          $("#gmb-ai-potential-score").text(labelText);

          // Populate Top Opportunities Summary (if present)
          var $oppList = $("#gmb-ai-opportunities-list");
          if ($oppList.length) {
            $oppList.empty();
            var recs = data.recommendations || [];
            var oppCount = 0;
            recs.forEach(function (r) {
              if (r.status === "FIX NEEDED" || r.status === "MISSING" || r.risk_level === "HIGH RISK") {
                $oppList.append('<li><strong>' + escHtml(r.category || r.id) + ':</strong> ' + escHtml(r.evidence || '') + '</li>');
                oppCount++;
              }
            });
            if (oppCount === 0) {
              $oppList.append('<li>Page content is already well-optimized against current SERP benchmarks.</li>');
            }
          }

          var recs = data.recommendations || [];
          var $tbody = $("#gmb-ai-post-suggestions-tbody").empty();

          if (recs.length === 0) {
            $tbody.html('<tr><td colspan="4" style="text-align: center; padding: 24px; color: #64748b;">No actionable SEO recommendations were identified for this page.</td></tr>');
            $("#gmb-ai-post-select-all").prop("checked", false).prop("disabled", true);
            $("#gmb-ai-post-apply-btn").prop("disabled", true);
          } else {
            var rowsHtml = "";
            var selectableCount = 0;
            var checkedCount = 0;

            recs.forEach(function (r) {
              var statusPillClass = "gmb-status-pill--success";
              if (r.status === "FIX NEEDED" || r.status === "MISSING" || r.status === "UNDER-OPTIMIZED" || r.status === "AI GENERATION REQUIRED") {
                statusPillClass = "gmb-status-pill--warning";
              } else if (r.status === "OVER-OPTIMIZED" || r.risk_level === "HIGH RISK") {
                statusPillClass = "gmb-status-pill--danger";
              }

              var recValue = (r.recommended !== undefined && r.recommended !== null) ? r.recommended.toString() : '';
              var inputControl = '';
              if (r.id === 'focus_keyword') {
                inputControl = '<input type="text" id="gmb-ai-input-focus" value="' + escAttr(recValue) + '" placeholder="[Enter Focus Keyword]" class="gmb-integration-input gmb-input-sm" />';
              } else if (r.id === 'seo_title') {
                inputControl = '<input type="text" id="gmb-ai-input-title" value="' + escAttr(recValue) + '" placeholder="[Enter SEO Title]" class="gmb-integration-input gmb-input-sm" />';
              } else if (r.id === 'meta_description') {
                var descPlaceholder = r.error ? '[AI generation failed: ' + escAttr(r.error) + ']' : '[AI generation required]';
                inputControl = '<textarea id="gmb-ai-input-desc" rows="2" placeholder="' + descPlaceholder + '" class="gmb-integration-input gmb-input-sm">' + escAttr(recValue) + '</textarea>';
              } else if (r.id === 'slug') {
                inputControl = '<input type="text" id="gmb-ai-input-slug" value="' + escAttr(recValue) + '" placeholder="[Enter URL Slug]" class="gmb-integration-input gmb-input-sm" ' + (r.action === 'KEEP CURRENT URL' ? 'disabled' : '') + ' />';
              } else if (r.id === 'schema_preset') {
                var schemaVal = recValue;
                inputControl = '<select id="gmb-ai-input-schema" class="gmb-integration-select gmb-input-sm">' +
                  '<option value=""' + (schemaVal === '' ? ' selected' : '') + '>Select schema type</option>' +
                  '<option value="WebPage"' + (schemaVal === 'WebPage' ? ' selected' : '') + '>WebPage</option>' +
                  '<option value="AboutPage"' + (schemaVal === 'AboutPage' ? ' selected' : '') + '>AboutPage</option>' +
                  '<option value="Service"' + (schemaVal === 'Service' ? ' selected' : '') + '>Service</option>' +
                  '<option value="LocalBusiness"' + (schemaVal === 'LocalBusiness' ? ' selected' : '') + '>LocalBusiness</option>' +
                  '<option value="Product"' + (schemaVal === 'Product' ? ' selected' : '') + '>Product</option>' +
                '</select>';
              } else if (r.id === 'content_intro') {
                var introPlaceholder = r.error ? '[AI generation failed: ' + escAttr(r.error) + ']' : '[AI generation required]';
                inputControl = '<textarea id="gmb-ai-input-intro" rows="5" placeholder="' + introPlaceholder + '" class="gmb-integration-input gmb-input-sm" style="min-height: 110px; font-family: monospace; font-size: 12px; line-height: 1.4;">' + escAttr(recValue) + '</textarea>';
              } else {
                inputControl = '<span>' + escAttr(recValue) + '</span>';
              }

              var hasRecommendedValue = recValue.trim().length > 0;
              var isChecked = (r.risk_level !== 'HIGH RISK' && r.action !== 'KEEP CURRENT URL' && r.status !== 'AI GENERATION REQUIRED' && hasRecommendedValue);
              // Existing body copy is high-impact; let Optimize users opt into the surgical intro change.
              if (r.id === 'content_intro' && $("#gmb-ai-setup-mode").val() === "optimize") {
                isChecked = false;
              }
              var checkAttr = isChecked ? 'checked' : '';
              selectableCount++;
              if (isChecked) checkedCount++;

              rowsHtml += '<tr>' +
                '<td class="gmb-td-checkbox"><input type="checkbox" class="gmb-ai-post-check" data-factor="' + escAttr(r.id) + '" ' + checkAttr + ' /></td>' +
                '<td><strong>' + escHtml(r.category || r.id) + '</strong></td>' +
                '<td>' + inputControl + '</td>' +
                '<td><span class="gmb-status-pill ' + statusPillClass + '">' + escHtml(r.status || 'RECOMMENDED') + '</span></td>' +
                '</tr>';
            });

            $tbody.html(rowsHtml);
            $("#gmb-ai-post-select-all").prop("disabled", false).prop("checked", selectableCount > 0 && checkedCount === selectableCount);
            $("#gmb-ai-post-apply-btn").prop("disabled", checkedCount === 0);
          }
        }, 600);
      }

      if (stepTimer) clearInterval(stepTimer);
      function applyProgressState(state) {
        if (!state || state.status === "missing") return;
        var step = Math.max(1, Math.min(8, parseInt(state.step, 10) || 1));
        var status = state.status || "processing";
        var progress = (state.progress === null || typeof state.progress === "undefined") ? null : Math.max(0, Math.min(100, parseInt(state.progress, 10)));
        var stateLabels = { processing: "Currently processing", waiting: "Waiting", retrying: "Retrying", complete: "Analysis complete", error: "Analysis could not complete" };
        var stepTitles = ["", "Analyzing Current Page", "Detecting Search Intent", "Checking SERP Data", "Preparing Content Benchmarks", "Semantic & Entity Analysis", "Content Gap Analysis", "Optimization Strategy", "Finalizing Results"];
        var activities = state.activity || "Working on the current research step...";
        $("#gmb-active-step-counter").text("Step " + step + " of 8");
        $("#gmb-active-step-title").text(stepTitles[step] || "Research step " + step);
        $("#gmb-active-step-desc").text(state.message || "Working with the current research data.");
        $("#gmb-analysis-state-label").text(stateLabels[status] || "Currently processing");
        $("#gmb-analysis-activity").text(activities);
        $("#gmb-analysis-elapsed").text("Elapsed " + (state.elapsed || 0) + "s");
        $("#gmb-analysis-state").attr("data-status", status);
        $("#gmb-research-status-title").text(status === "waiting" ? "Waiting" : (status === "error" ? "Research error" : "Live Research"));
        $("#gmb-research-status-label").text(state.waiting_for ? "Waiting for " + state.waiting_for + ":" : (state.activity || "Current activity") + ":");
        $("#gmb-analysis-waiting").prop("hidden", status !== "waiting" && status !== "retrying");
        $("#gmb-analysis-waiting-for").text(state.waiting_for || "");
        $("#gmb-active-progress-fill").css("width", progress === null ? "42%" : progress + "%").parent().attr("aria-valuenow", progress === null ? 0 : progress);
        $("#gmb-active-progress-fill").toggleClass("is-indeterminate", progress === null);
        $("#gmb-active-progress-percent").text(progress === null ? "Processing..." : progress + "%");
        $("#gmb-overall-progress-label").text(Math.max(0, step - (status === "complete" ? 0 : 1)) + " of 8 steps complete");
        $("#gmb-ai-running-label").text((stateLabels[status] || "Processing") + "... " + activities);
        var taskIndicator = status === "complete" ? '<span class="task-check-circle">✓</span>' : '<span class="task-spinner"></span>';
        var taskClass = status === "complete" ? "done" : "running";
        var waitingTask = state.waiting_for ? '<div class="gmb-task-row waiting"><span class="task-hollow-circle">◌</span> Waiting for ' + escHtml(state.waiting_for) + '</div>' : "";
        $("#gmb-live-tasks-list").html('<div class="gmb-task-row ' + taskClass + '">' + taskIndicator + ' ' + escHtml(activities) + '</div>' + waitingTask);
        for (var i = 1; i <= 8; i++) {
          var $item = $("#gmb-res-step-" + i);
          var $pill = $item.find(".gmb-step-status-pill");
          if (i < step || (status === "complete" && i === step)) {
            $item.removeClass("active").addClass("completed");
            $item.find(".gmb-step-active-ring").remove();
            $pill.removeClass("pending in-progress").addClass("completed").text("Completed");
          } else if (i === step && status !== "complete") {
            $item.addClass("active").removeClass("completed");
            if (!$item.find(".gmb-step-active-ring").length) $item.append('<div class="gmb-step-active-ring"></div>');
            $pill.removeClass("pending completed").addClass("in-progress").text(stateLabels[status] || "In Progress");
          } else {
            $item.removeClass("active completed");
            $pill.removeClass("in-progress completed").addClass("pending").text("Pending");
          }
        }
        if (state.provider) $("#gmb-analysis-activity").text(activities + " (" + state.provider + ")");
      }

      var progressToken = "gmb" + Date.now().toString(36) + Math.random().toString(36).slice(2);
      function pollProgress() {
        if (progressPollTimer) clearTimeout(progressPollTimer);
        $.post(gmbMetaboxData.ajaxUrl, { action: "gmb_ai_research_progress", nonce: gmbMetaboxData.nonce, progress_token: progressToken })
          .done(function (res) {
            if (res && res.success) {
              applyProgressState(res.data);
              if (res.data.status !== "complete" && res.data.status !== "error") progressPollTimer = setTimeout(pollProgress, 1200);
            }
          })
          .fail(function () { progressPollTimer = setTimeout(pollProgress, 1800); });
      }
      $("#gmb-live-tasks-list").html('<div class="gmb-task-row running"><span class="task-spinner"></span> Preparing the research pipeline...</div>');
      pollProgress();

      // Extract content safely
      var postContent = "";
      if (typeof tinymce !== "undefined" && tinymce.get("content") && !tinymce.get("content").isHidden()) {
        postContent = tinymce.get("content").getContent();
      } else if ($("#content").length) {
        postContent = $("#content").val();
      } else if (typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.select("core/editor")) {
        var editorStore = wp.data.select("core/editor");
        if (editorStore && typeof editorStore.getEditedPostAttribute === "function") {
          postContent = editorStore.getEditedPostAttribute("content") || "";
        }
      }

      var postId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId)
        ? gmbMetaboxData.postId
        : ($("#post_ID").val() || 0);
      var curTitle = $("#gmb_seo_title_input").val() || "";
      var curDesc = $("#gmb_seo_desc_input").val() || "";

      var userInstructions = $("#gmb-ai-setup-instructions").length ? $("#gmb-ai-setup-instructions").val().trim() : "";
      var tone = $("#gmb-ai-setup-tone").length ? $("#gmb-ai-setup-tone").val() : "auto";
      var intent = $("#gmb-ai-setup-intent").length ? $("#gmb-ai-setup-intent").val() : "auto";

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
          focus_keyword: targetQuery,
          seo_title: curTitle,
          meta_description: curDesc,
          mode: mode,
          country: countryVal,
          language: language,
          tone: tone,
          intent: intent,
          search_intent: intent,
          user_instructions: userInstructions,
          progress_token: progressToken
        },
        success: function (res) {
          if (!res.success || !res.data) {
            if (stepTimer) clearInterval(stepTimer);
            if (progressPollTimer) clearTimeout(progressPollTimer);
            applyProgressState({ step: 1, status: "error", activity: "Analysis failed", message: (res.data && res.data.message) || "The research pipeline could not continue.", progress: null, elapsed: 0 });
            var failureMessage = (res.data && res.data.message) || (typeof res.data === "string" ? res.data : "Unknown error");
            alert("AI research failed: " + failureMessage);
            return;
          }

          ajaxFinishedData = res.data;
          globalAjaxResultData = res.data;
          if (progressPollTimer) clearTimeout(progressPollTimer);
          applyProgressState({ step: 8, status: "complete", activity: "Analysis complete", message: "All research findings were validated and the report is ready.", progress: 100, elapsed: 0 });
          populateAndShowStep3(ajaxFinishedData);
        },
        error: function (xhr, status, err) {
          if (stepTimer) clearInterval(stepTimer);
          if (progressPollTimer) clearTimeout(progressPollTimer);
          applyProgressState({ step: 1, status: "error", activity: "Analysis failed", message: err || "The research request failed.", progress: null, elapsed: 0 });
          alert("AJAX Error during research: " + (err || "Network error"));
        }
      });
    });

    // Select All & Individual Checkbox Handlers
    $(document).on("change", "#gmb-ai-post-select-all", function () {
      var isChecked = $(this).is(":checked");
      $(".gmb-ai-post-check").prop("checked", isChecked);
      var checkedCount = $(".gmb-ai-post-check:checked").length;
      $("#gmb-ai-post-apply-btn").prop("disabled", checkedCount === 0);
    });

    $(document).on("change", ".gmb-ai-post-check", function () {
      var total = $(".gmb-ai-post-check").length;
      var checkedCount = $(".gmb-ai-post-check:checked").length;
      $("#gmb-ai-post-select-all").prop("checked", total > 0 && checkedCount === total);
      $("#gmb-ai-post-apply-btn").prop("disabled", checkedCount === 0);
    });

    $(document).on("click", "#gmb-ai-post-modal-close, #gmb-ai-post-modal-cancel", function (e) {
      e.preventDefault();
      closeAiSeoModal();
    });

    // Helper: Close AI SEO Modal safely
    function closeAiSeoModal() {
      $("#gmb-ai-post-seo-modal")
        .attr("style", "display: none !important;")
        .removeClass("active is-active")
        .attr("aria-hidden", "true");
    }

    // Apply Selected Recommendations Button Handler
    $(document).on("click", "#gmb-ai-post-apply-btn", function (e) {
      e.preventDefault();

      var applyFocus  = $('.gmb-ai-post-check[data-factor="focus_keyword"]:checked').length > 0;
      var applyTitle  = $('.gmb-ai-post-check[data-factor="seo_title"]:checked').length > 0;
      var applyDesc   = $('.gmb-ai-post-check[data-factor="meta_description"]:checked').length > 0;
      var applySlug   = $('.gmb-ai-post-check[data-factor="slug"]:checked').length > 0;
      var applySchema = $('.gmb-ai-post-check[data-factor="schema_preset"]:checked').length > 0;
      var applyIntro  = $('.gmb-ai-post-check[data-factor="content_intro"]:checked').length > 0;

      if (applySlug) {
        if (!confirm("WARNING: Modifying published URL slug can affect existing search rankings unless a 301 redirect is configured. Are you sure you want to change the URL slug?")) {
          return;
        }
      }

      var focusKw = applyFocus ? ($("#gmb-ai-input-focus").val() || "").trim() : "";
      var appliedTitle = applyTitle ? ($("#gmb-ai-input-title").val() || "").trim() : "";
      var appliedDesc = applyDesc ? ($("#gmb-ai-input-desc").val() || "").trim() : "";
      var appliedSlug = applySlug ? ($("#gmb-ai-input-slug").val() || "").trim() : "";
      var appliedSchema = applySchema ? $("#gmb-ai-input-schema").val() : "";
      var appliedIntro = applyIntro ? ($("#gmb-ai-input-intro").val() || "").trim() : "";

      // “Select all” can also check informational or generation-failed rows.
      // Never turn those rows into empty writes that clear valid metadata.
      applyFocus = applyFocus && !!focusKw;
      applyTitle = applyTitle && !!appliedTitle;
      applyDesc = applyDesc && !!appliedDesc;
      applySlug = applySlug && !!appliedSlug && !$("#gmb-ai-input-slug").is(":disabled");
      applySchema = applySchema && !!appliedSchema;
      applyIntro = applyIntro && !!appliedIntro;
      var applyToc = $('.gmb-ai-post-check[data-factor="table_of_contents"]:checked').length > 0;
      if (!applyFocus && !applyTitle && !applyDesc && !applySlug && !applySchema && !applyIntro && !applyToc) {
        alert("Select at least one actionable SEO recommendation.");
        return;
      }

      // 1. Inject Focus Keyword
      if (focusKw) {
        try {
          if (typeof window.gmbSetFocusKeywords === "function") {
            window.gmbSetFocusKeywords(focusKw);
          }
          $("#gmb_seo_focus_keyword_hidden").val(focusKw).trigger("change");
          $("#gmb_seo_focus_keyword_input").val(focusKw).trigger("change");
          if (typeof keywords !== "undefined") {
            keywords = [focusKw];
          }
          if (typeof renderKeywordPills === "function") {
            renderKeywordPills();
          }
        } catch (eFocus) {
          console.warn("Focus keyword injection error:", eFocus);
        }
      }

      // 2. Inject SEO Title
      if (appliedTitle) {
        try {
          $("#gmb_seo_title_input").val(appliedTitle).trigger("input").trigger("change");
          $("#title").val(appliedTitle).trigger("change");
          if (typeof wp !== "undefined" && wp.data && wp.data.dispatch && wp.data.dispatch("core/editor")) {
            wp.data.dispatch("core/editor").editPost({ title: appliedTitle });
          }
        } catch (eTitle) {
          console.warn("Title injection error:", eTitle);
        }
      }

      // 3. Inject Meta Description
      if (appliedDesc) {
        try {
          $("#gmb_seo_desc_input").val(appliedDesc).trigger("input").trigger("change");
        } catch (eDesc) {
          console.warn("Description injection error:", eDesc);
        }
      }

      // 4. Inject URL / Slug
      if (appliedSlug) {
        try {
          $("#post_name").val(appliedSlug).trigger("change");
          $("#editable-post-name").text(appliedSlug);
          if (typeof wp !== "undefined" && wp.data && wp.data.dispatch && wp.data.dispatch("core/editor")) {
            wp.data.dispatch("core/editor").editPost({ slug: appliedSlug });
          }
        } catch (eSlug) {
          console.warn("Slug injection error:", eSlug);
        }
      }

      // 5. Inject Schema Preset
      if (appliedSchema) {
        try {
          if ($("#gmb_seo_schema_type").length) {
            $("#gmb_seo_schema_type").val(appliedSchema).trigger("change");
          }
          if ($("#gmb_seo_active_schemas").length) {
            $("#gmb_seo_active_schemas").val(appliedSchema).trigger("change");
          }
          // Keep the visible Schema tab in sync with the AI result. Passing
          // false prevents opening the schema builder as a side effect.
          if (typeof window.gmbUseSchemaTemplate === "function") {
            window.gmbUseSchemaTemplate(appliedSchema, null, false);
          }
        } catch (eSchema) {
          console.warn("Schema injection error:", eSchema);
        }
      }

      // 6. Inject Article Content into Editor (Gutenberg / TinyMCE / Standard Textarea)
      if (appliedIntro) {
        try {
          var optimizeExisting = $("#gmb-ai-setup-mode").val() === "optimize";
          var formattedContent = (appliedIntro.trim().indexOf("<") === 0) ? appliedIntro : ('<p>' + appliedIntro + '</p>');
          var contentInjected = false;

          // A. Try Gutenberg Block Editor first
          if (typeof wp !== "undefined" && wp.data && wp.data.select && wp.data.select("core/editor") && wp.data.dispatch && wp.data.dispatch("core/editor")) {
            try {
              var editorSelect = wp.data.select("core/editor");
              var editorDispatch = wp.data.dispatch("core/editor");
              var parsedBlocks = [];

              if (wp.blocks && typeof wp.blocks.parse === "function") {
                parsedBlocks = wp.blocks.parse(formattedContent);
              } else if (wp.blocks && typeof wp.blocks.htmlToBlocks === "function") {
                parsedBlocks = wp.blocks.htmlToBlocks(formattedContent);
              } else if (wp.blocks && typeof wp.blocks.createBlock === "function") {
                parsedBlocks = [wp.blocks.createBlock("core/freeform", { content: formattedContent })];
              }

              if (parsedBlocks && parsedBlocks.length > 0) {
                var curBlocks = editorSelect.getBlocks ? (editorSelect.getBlocks() || []) : [];
                if (optimizeExisting) {
                  var firstParagraphIndex = curBlocks.findIndex(function (block) { return block && block.name === "core/paragraph"; });
                  if (firstParagraphIndex >= 0) {
                    curBlocks.splice.apply(curBlocks, [firstParagraphIndex, 1].concat(parsedBlocks));
                    editorDispatch.resetBlocks(curBlocks);
                  } else {
                    editorDispatch.resetBlocks(parsedBlocks.concat(curBlocks));
                  }
                } else {
                  editorDispatch.resetBlocks(parsedBlocks.concat(curBlocks));
                }
                contentInjected = true;
              }
            } catch (errGutenberg) {
              console.warn("Gutenberg block injection error:", errGutenberg);
            }
          }

          // B. Try TinyMCE Visual Editor
          if (!contentInjected && typeof tinymce !== "undefined" && tinymce.get("content") && !tinymce.get("content").isHidden()) {
            try {
              var curBody = tinymce.get("content").getContent();
              var updatedBody = optimizeExisting ? curBody.replace(/<p\b[^>]*>[\s\S]*?<\/p>/i, formattedContent) : formattedContent + (curBody ? '\n' + curBody : '');
              tinymce.get("content").setContent(updatedBody);
              contentInjected = true;
            } catch (errTiny) {
              console.warn("TinyMCE injection error:", errTiny);
            }
          }

          // C. Try Textarea fallback
          if ($("#content").length) {
            try {
              var curText = $("#content").val() || "";
              var updatedText = optimizeExisting ? curText.replace(/<p\b[^>]*>[\s\S]*?<\/p>/i, formattedContent) : formattedContent + (curText ? '\n' + curText : '');
              $("#content").val(updatedText).trigger("change");
            } catch (errText) {
              console.warn("Textarea content injection error:", errText);
            }
          }
        } catch (eIntro) {
          console.warn("Content intro injection error:", eIntro);
        }
      }

      // 7. Save to DB if existing post or close popup immediately
      var postId = $("#post_ID").val() || 0;
      if (postId > 0) {
        var quickSaveData = {
          action: "gmb_quick_save_ai_seo_fields",
          nonce: gmbMetaboxData.nonce,
          post_id: postId
        };
        if (applyFocus) quickSaveData.focus_keyword = focusKw;
        if (applyTitle) quickSaveData.meta_title = appliedTitle;
        if (applyDesc) quickSaveData.meta_description = appliedDesc;
        if (applySlug) quickSaveData.slug = appliedSlug;
        if (applySchema) quickSaveData.schema_preset = appliedSchema;
        if (applyToc) quickSaveData.table_of_contents = "1";
        if (applyIntro) {
          quickSaveData.content_intro = appliedIntro;
          quickSaveData.content_mode = $("#gmb-ai-setup-mode").val() === "optimize" ? "replace_intro" : "prepend";
        }
        $.ajax({
          url: gmbMetaboxData.ajaxUrl,
          type: "POST",
          data: quickSaveData,
          success: function (res) {
            if (!res || !res.success) {
              var saveMessage = res && res.data && res.data.message ? res.data.message : (res && typeof res.data === "string" ? res.data : "Unknown server error");
              alert("SEO changes could not be saved: " + saveMessage);
              return;
            }
            closeAiSeoModal();
            if (res.data && typeof res.data.score !== "undefined") {
              var savedScore = parseInt(res.data.score, 10);
              if (!isNaN(savedScore)) {
                $("#gmb-metabox-score-val").text(savedScore);
                $("#gmb-seo-score-val, #gmb-publish-score-val").text(savedScore + " / 100");
                $("#gmb_seo_score_hidden").val(savedScore);
              }
            }
            if (typeof window.gmbRunContentAnalysis === "function") {
              window.gmbRunContentAnalysis();
            } else if (typeof recalculateScore === "function") {
              recalculateScore();
            }
          },
          error: function (xhr, status, err) {
            alert("SEO changes could not be saved: " + (err || "Network error"));
          }
        });
      } else {
        closeAiSeoModal();
      }
    });

    window.gmbSwitchTab = function (targetTab, btnEl) {
      if (!targetTab) return;
      var $btn = btnEl ? $(btnEl) : $('.gmb-tab-btn[data-tab="' + targetTab + '"]');
      var $container = $btn.length ? $btn.closest(".gmb-seo-meta-container") : $(".gmb-seo-meta-container");
      if (!$container.length) $container = $(".gmb-seo-meta-container");

      $container.find(".gmb-seo-tabs .gmb-tab-btn").removeClass("active is-active");
      if ($btn.length) $btn.addClass("active is-active");
      $('.gmb-tab-btn[data-tab="' + targetTab + '"]').addClass("active is-active");
      $container.find(".gmb-seo-tabs .gmb-tab-btn").attr("aria-selected", "false");
      $container.find('.gmb-seo-tabs .gmb-tab-btn[data-tab="' + targetTab + '"]').attr("aria-selected", "true");

      $container.find(".gmb-tab-content").removeClass("active").hide();
      $container.find(".gmb-tab-content").attr("aria-hidden", "true");
      $("#" + targetTab).addClass("active").fadeIn(150);
      $("#" + targetTab).attr("aria-hidden", "false");
    };

    $(document).on("click", ".gmb-seo-tabs .gmb-tab-btn", function (e) {
      e.preventDefault();
      var targetTab = $(this).attr("data-tab") || $(this).attr("data-target");
      window.gmbSwitchTab(targetTab, this);
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
      $(".gmb-social-tab-btn, .gmb-social-subtab-btn").attr("aria-selected", "false");
      $(this).attr("aria-selected", "true");

      $(".gmb-social-pane").hide();
      $(".gmb-social-pane").attr("aria-hidden", "true");
      $("#" + targetPane).fadeIn(150);
      $("#" + targetPane).attr("aria-hidden", "false");
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

      var $googlePreview = $(this)
        .closest(".gmb-preview-box")
        .find(".gmb-preview-google");
      $googlePreview
        .removeClass("gmb-preview-device--desktop gmb-preview-device--mobile")
        .addClass("gmb-preview-device--" + device);
    });

    // Advanced Tab: Redirect Toggle Handler
    $(document).on("change", "#gmb_enable_redirect_toggle", function () {
      var $box = $("#gmb-redirect-details-box");
      if ($(this).is(":checked")) {
        $box.slideDown(150);
      } else {
        $box.slideUp(150, function () {
          $(this).hide();
        });
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

    // Apply persisted enabled/disabled state on first render.
    $(".gmb-adv-robot-toggle").each(function () {
      $(this).trigger("change");
    });

    window.gmbOpenSnippetModal = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      var $modal = $("#gmb-snippet-modal");
      if ($modal.length) {
        $modal.appendTo("body");
        $modal.addClass("active is-open").css("display", "flex").show();
      }
      if (typeof updateModalPreview === "function") updateModalPreview();
    };

    window.gmbCloseSnippetModal = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      $("#gmb-snippet-modal").removeClass("active is-open").hide();
      if (typeof syncSnippetFromInputs === "function") syncSnippetFromInputs();
    };

    $(document).on("click", "#gmb-edit-snippet-btn, .gmb-edit-snippet-btn", window.gmbOpenSnippetModal);

    $(document).on("click", "#gmb-modal-close-btn", window.gmbCloseSnippetModal);
    $(document).on("keydown", "#gmb-modal-close-btn, #gmb-schema-modal-close-btn, #gmb-schema-builder-close-btn", function (e) {
      if (e.key === "Enter" || e.key === " " || e.keyCode === 13 || e.keyCode === 32) {
        e.preventDefault();
        $(this).trigger("click");
      }
    });

    $(document).on("click", "#gmb-modal-save-btn", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var original = $btn.text();
      var postId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId) || $("#post_ID").val() || 0;
      if (!postId || typeof gmbMetaboxData === "undefined") {
        window.gmbCloseSnippetModal(e);
        return;
      }
      var data = {
        action: "gmb_quick_save_ai_seo_fields",
        nonce: gmbMetaboxData.nonce,
        post_id: postId,
        meta_title: $("#gmb_seo_title_input").val() || "",
        meta_description: $("#gmb_seo_desc_input").val() || "",
        canonical: $("#gmb_seo_canonical_input").val() || "",
        focus_keyword: $("#gmb_seo_focus_keyword_hidden").val() || "",
        is_pillar: $("#gmb_seo_is_pillar_input").is(":checked") ? "1" : "0",
        facebook_title: $("#gmb_seo_fb_title").val() || "",
        facebook_desc: $("#gmb_seo_fb_desc").val() || "",
        facebook_image: $("#gmb_seo_fb_image").val() || "",
        twitter_title: $("#gmb_seo_tw_title").val() || "",
        twitter_desc: $("#gmb_seo_tw_desc").val() || "",
        twitter_image: $("#gmb_seo_tw_image").val() || "",
        twitter_card_type: $("#gmb_seo_tw_card_type").val() || "summary_large_image"
      };
      $btn.prop("disabled", true).text("Saving...");
      $.ajax({ url: gmbMetaboxData.ajaxUrl, type: "POST", data: data })
        .done(function (res) {
          if (!res || !res.success) {
            alert("Snippet changes could not be saved: " + ((res && res.data && res.data.message) || "Save failed"));
            return;
          }
          $("#gmb_seo_title_input").val(data.meta_title);
          $("#gmb_seo_desc_input").val(data.meta_description);
          $("#gmb_seo_fb_title_metabox").val(data.facebook_title).trigger("input");
          $("#gmb_seo_fb_desc_metabox").val(data.facebook_desc).trigger("input");
          $("#gmb_seo_fb_image_metabox").val(data.facebook_image).trigger("change");
          $("#gmb_seo_tw_title_metabox").val(data.twitter_title).trigger("input");
          $("#gmb_seo_tw_desc_metabox").val(data.twitter_desc).trigger("input");
          $("#gmb_seo_tw_image_metabox").val(data.twitter_image).trigger("change");
          $("#gmb_seo_tw_card_type").val(data.twitter_card_type).trigger("change");
          $("#gmb-audit-freshness").removeClass("is-stale").attr("data-analysis-hash", "saved").text("Saved audit");
          updateModalPreview();
          window.gmbCloseSnippetModal(e);
        })
        .fail(function () { alert("Snippet changes could not be saved. Please try again."); })
        .always(function () { $btn.prop("disabled", false).text(original); });
    });

    // Modal Tab Switching (General vs Social)
    $(document).on("click", ".gmb-modal-tabs .gmb-modal-tab-btn[data-modal-tab], .gmb-modal-tabs .gmb-modal-tab-btn[data-target]", function (e) {
      e.preventDefault();
      var targetModalTab =
        $(this).attr("data-modal-tab") || $(this).attr("data-target");
      if (!targetModalTab) return;

        $(this).closest(".gmb-modal-tabs").find(".gmb-modal-tab-btn").removeClass("active").attr("aria-selected", "false");
        $(this).addClass("active");
        $(this).attr("aria-selected", "true");

      $(this).closest(".gmb-modal-box").find(".gmb-modal-tab-content").hide().attr("aria-hidden", "true");
      $("#" + targetModalTab).show();
      $("#" + targetModalTab).attr("aria-hidden", "false");
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

    $(document).on("input", "#gmb_seo_fb_title, #gmb_seo_fb_title_metabox", function () {
      $("#gmb_seo_fb_title, #gmb_seo_fb_title_metabox").not(this).val($(this).val() || "");
      var val =
        $(this).val() ||
        $("#gmb_seo_title_input").val() ||
        $("#title").val() ||
        "Page Title";
      $("#gmb-fb-preview-title").text(val);
      updateSocialCounters();
    });

    $(document).on("input", "#gmb_seo_fb_desc, #gmb_seo_fb_desc_metabox", function () {
      $("#gmb_seo_fb_desc, #gmb_seo_fb_desc_metabox").not(this).val($(this).val() || "");
      var val =
        $(this).val() ||
        $("#gmb_seo_desc_input").val() ||
        "Social description preview...";
      $("#gmb-fb-preview-desc").text(val);
      updateSocialCounters();
    });

    $(document).on("input", "#gmb_seo_tw_title, #gmb_seo_tw_title_metabox", function () {
      $("#gmb_seo_tw_title, #gmb_seo_tw_title_metabox").not(this).val($(this).val() || "");
      var val = $(this).val() || $("#gmb_seo_title_input").val() || $("#title").val() || "Page Title";
      $("#gmb-tw-preview-title").text(val);
      updateSocialCounters();
    });

    $(document).on("input", "#gmb_seo_tw_desc, #gmb_seo_tw_desc_metabox", function () {
      $("#gmb_seo_tw_desc, #gmb_seo_tw_desc_metabox").not(this).val($(this).val() || "");
      var val = $(this).val() || $("#gmb_seo_desc_input").val() || "Twitter summary description preview...";
      $("#gmb-tw-preview-desc").text(val);
      updateSocialCounters();
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
    var keywordSaveTimer = null;
    var keywordSaveRequest = null;
    var confirmedKeywords = [];
    var rawKeywordVal = $("#gmb_seo_focus_keyword_hidden").val();
    if (rawKeywordVal) {
      keywords = rawKeywordVal
        .split(",")
        .map(function (k) {
          return k.trim();
        })
        .filter(Boolean);
    }
    confirmedKeywords = keywords.slice();

    function renderKeywordPills() {
      var $wrapper = $("#gmb-keyword-container-wrapper");
      $wrapper.find(".gmb-keyword-pill").remove();

      var $input = $("#gmb_seo_focus_keyword_input");

      keywords.forEach(function (kw, index) {
        var $pill = $(
          '<span class="gmb-keyword-pill">' +
            escHtml(kw) +
            ' <button type="button" class="gmb-keyword-pill-remove" data-index="' +
            index +
            '" title="Remove keyword" aria-label="Remove keyword">&times;</button></span>',
        );
        if ($input.length) {
          $pill.insertBefore($input);
        } else {
          $wrapper.append($pill);
        }
      });

      var kwString = keywords.join(", ");
      $("#gmb_seo_focus_keyword_hidden").val(kwString).trigger("change");

      var $notice = $("#gmb-seo-no-keyword-notice");
      if (keywords.length > 0) {
        $notice.addClass("is-hidden").hide();
      } else {
        $notice.removeClass("is-hidden").show();
      }

      // A temporary Gutenberg/editor API failure must not prevent the pill
      // event handlers below from being registered.
      try {
        recalculateScore();
      } catch (error) {
        console.error("GMB Ranker: score refresh failed during keyword render", error);
      }
    }

    function persistKeywordState() {
      var postId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId) || $("#post_ID").val() || 0;
      if (!postId || typeof gmbMetaboxData === "undefined" || !gmbMetaboxData.ajaxUrl) return;
      if (keywordSaveTimer) clearTimeout(keywordSaveTimer);
      keywordSaveTimer = setTimeout(function () {
        if (keywordSaveRequest && keywordSaveRequest.readyState !== 4) {
          keywordSaveRequest.abort();
        }
        var requestedKeywords = keywords.slice();
        keywordSaveRequest = $.ajax({
          url: gmbMetaboxData.ajaxUrl,
          type: "POST",
          data: {
            action: "gmb_quick_save_ai_seo_fields",
            nonce: gmbMetaboxData.nonce,
            post_id: postId,
            focus_keyword: requestedKeywords.join(", ")
          }
        }).done(function (res) {
          if (res && res.success) {
            confirmedKeywords = requestedKeywords.slice();
            if (res.data && typeof res.data.score !== "undefined") {
              $("#gmb-metabox-score-val").text(res.data.score);
              $("#gmb-publish-score-val").text(res.data.score + " / 100");
              $("#gmb_seo_score_hidden").val(res.data.score);
            }
            $("#gmb-audit-freshness").removeClass("is-stale").attr("data-analysis-hash", "saved").text("Saved audit");
          } else {
            keywords = confirmedKeywords.slice();
            renderKeywordPills();
            alert("Focus Keyword could not be saved: " + ((res && res.data && res.data.message) || "Save failed"));
          }
        }).fail(function (xhr, status) {
          if (status === "abort") return;
          if (requestedKeywords.join(", ") !== confirmedKeywords.join(", ")) {
            keywords = confirmedKeywords.slice();
            renderKeywordPills();
          }
          $("#gmb-audit-freshness").addClass("is-stale").text("Keyword not saved — please try again");
          alert("Focus Keyword could not be saved. Please try again.");
        });
      }, 350);
    }

    window.gmbSetFocusKeywords = function (kwString) {
      if (!kwString) {
        keywords = [];
      } else {
        keywords = kwString
          .split(",")
          .map(function (k) {
            return k.trim();
          })
          .filter(Boolean);
      }
      renderKeywordPills();
    };

    renderKeywordPills();

    function addKeywordFromInput($input) {
      var rawVal = $input.val() || "";
      if (!rawVal.trim()) return;

      var parts = rawVal.split(",");
      var added = false;

      parts.forEach(function (part) {
        var clean = part.replace(/\s+/g, " ").trim();
        if (clean.length > 200) {
          alert("Focus Keyword must be 200 characters or fewer.");
          return;
        }
        var duplicate = keywords.some(function (existing) {
          return existing.toLowerCase() === clean.toLowerCase();
        });
        if (clean && !duplicate) {
          keywords.push(clean);
          added = true;
        }
      });

      $input.val("");
      renderKeywordPills();
      if (added) persistKeywordState();
    }

    $(document).on("keydown", "#gmb_seo_focus_keyword_input", function (e) {
      var val = $(this).val();
      if (e.key === "Enter" || e.key === "," || e.keyCode === 13 || e.keyCode === 188) {
        e.preventDefault();
        addKeywordFromInput($(this));
      } else if ((e.key === "Backspace" || e.keyCode === 8) && (!val || val.length === 0) && keywords.length > 0) {
        e.preventDefault();
        keywords.pop();
        renderKeywordPills();
        persistKeywordState();
      }
    });

    $(document).on("blur change", "#gmb_seo_focus_keyword_input", function () {
      addKeywordFromInput($(this));
    });

    $(document).on("click", ".gmb-keyword-pill-remove", function (e) {
      e.preventDefault();
      e.stopPropagation();
      var index = parseInt($(this).attr("data-index"), 10);
      if (!isNaN(index) && index >= 0 && index < keywords.length) {
        keywords.splice(index, 1);
        renderKeywordPills();
        persistKeywordState();
      }
      return false;
    });

    $(document).on("click", ".gmb-focus-keyword-field-wrapper", function (e) {
      if (
        !$(e.target).closest(
          ".gmb-keyword-pill, .gmb-keyword-pill-remove, #gmb-metabox-score-badge",
        ).length
      ) {
        $("#gmb_seo_focus_keyword_input").focus();
      }
    });

    $(document).on("change", "#gmb_seo_is_pillar_input", function () {
      var $checkbox = $(this);
      var postId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId) || $("#post_ID").val() || 0;
      if (!postId || typeof gmbMetaboxData === "undefined") return;
      var checked = $checkbox.is(":checked");
      $checkbox.prop("disabled", true);
      $.ajax({
        url: gmbMetaboxData.ajaxUrl,
        type: "POST",
        data: {
          action: "gmb_quick_save_ai_seo_fields",
          nonce: gmbMetaboxData.nonce,
          post_id: postId,
          is_pillar: checked ? "1" : "0"
        }
      }).done(function (res) {
        if (res && res.success && res.data && typeof res.data.score !== "undefined") {
          $("#gmb-metabox-score-val").text(res.data.score);
          $("#gmb-publish-score-val").text(res.data.score + " / 100");
          $("#gmb_seo_score_hidden").val(res.data.score);
          $("#gmb-audit-freshness").removeClass("is-stale").attr("data-analysis-hash", "saved").text("Saved audit");
        }
      }).fail(function () {
        $checkbox.prop("checked", !checked);
        alert("Pillar Content could not be saved. Please try again or click Update.");
      }).always(function () {
        $checkbox.prop("disabled", false);
      });
    });

    // ==========================================
    // 7. Accordion Toggle (Silky-Smooth Fail-Safe Slide)
    // ==========================================
    $(document).on("click", ".gmb-accordion-header", function (e) {
      e.preventDefault();
      var $header = $(this);
      var $section = $header.closest(".gmb-accordion-section");
      var $content = $section.children(".gmb-accordion-content");

      var isCurrentlyCollapsed = $section.hasClass("collapsed") || !$content.is(":visible");

      if (isCurrentlyCollapsed) {
        $section.removeClass("collapsed");
        $header.attr("aria-expanded", "true");
        $content.stop(true, true).slideDown(220, function () {
          $(this).css("display", "block");
        });
      } else {
        $section.addClass("collapsed");
        $header.attr("aria-expanded", "false");
        $content.stop(true, true).slideUp(220, function () {
          $(this).css("display", "none");
        });
      }
    });

    $(document).on("keydown", ".gmb-accordion-header", function (e) {
      if (e.key === "Enter" || e.key === " " || e.keyCode === 13 || e.keyCode === 32) {
        e.preventDefault();
        $(this).trigger("click");
      }
    });

    // ==========================================
    // 8. SEO Audit & Dynamic Scoring
    // ==========================================
    function recalculateScore() {
      var primaryKw = keywords.length > 0 ? keywords[0].toLowerCase() : "";
      var metaboxConfig = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData) ? gmbMetaboxData : {};
      var title = ($("#gmb_seo_title_input").val() || "").trim();
      if (!title) {
        title = (
          $("#title").val() ||
          $("#gmb-preview-title").text() ||
          ""
        ).trim();
      }
      var desc = ($("#gmb_seo_desc_input").val() || "").trim();
      var titleLower = title.toLowerCase();
      var descLower = desc.toLowerCase();

      // Get post content from Gutenberg, TinyMCE or standard textarea
      var content = "";
      if (
        typeof wp !== "undefined" &&
        wp.data &&
        wp.data.select &&
        wp.data.select("core/editor")
      ) {
        var activeEditorStore = wp.data.select("core/editor");
        if (activeEditorStore && typeof activeEditorStore.getEditedPostAttribute === "function") {
          content = activeEditorStore.getEditedPostAttribute("content") || "";
        }
        if (!content && activeEditorStore && typeof activeEditorStore.getEditedPostContent === "function") {
          content = activeEditorStore.getEditedPostContent() || "";
        }
      }
      if (
        !content &&
        typeof tinyMCE !== "undefined" &&
        tinyMCE.get("content")
      ) {
        content = tinyMCE.get("content").getContent();
      }
      if (!content && $("#content").length) {
        content = $("#content").val();
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
      var savedScore = parseInt($("#gmb_seo_score_hidden").val(), 10) || 0;
      // WordPress changes the permalink markup between classic, block, and
      // custom post editors. Prefer the slug field and fall back through all
      // known permalink representations instead of trusting one empty node.
      var permalink = "";
      $("#post_name, #editable-post-name, #sample-permalink, #sample-permalink a, .edit-slug-box code").each(function () {
        var candidate = $(this).val ? $(this).val() : "";
        candidate = candidate || $(this).attr("href") || $(this).text() || "";
        if (!permalink && String(candidate).trim()) permalink = String(candidate).trim().toLowerCase();
      });

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
        if (primaryKw) basicFails++;
        basicItems.push({
          status: primaryKw ? "fail" : "warn",
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
        if (primaryKw) basicFails++;
        basicItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Focus Keyword not found in SEO Meta Description." : "Add a Focus Keyword to evaluate the meta description.",
          tip: "Include your focus keyword inside the meta description for better CTR.",
        });
      }

      // 3. Focus Keyword in URL. Compare against the slug, not the full
      // display URL, and allow significant phrase terms in natural slugs.
      var permalinkSlug = permalink
        .replace(/^https?:\/\/[^/]+/i, "")
        .replace(/[^a-z0-9]+/g, " ");
      if (
        primaryKw &&
        permalinkSlug &&
        checkKeywordInText(permalinkSlug, primaryKw)
      ) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used in the URL.",
          tip: "Focus keyword is included in the permalink slug.",
        });
      } else if (primaryKw && permalinkSlug) {
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

      // Flexible Focus Keyword Matcher (Exact match OR all significant terms present)
      function checkKeywordInText(text, kw) {
        if (!text || !kw) return false;
        var textLower = text.toLowerCase();
        var kwLower = kw.toLowerCase().trim();
        if (textLower.indexOf(kwLower) !== -1) return true;

        var stopWords = ["a", "an", "the", "in", "on", "at", "for", "to", "of", "and", "or", "is", "are", "with", "by", "your", "our", "may", "need"];
        var words = kwLower.split(/\s+/).filter(function (w) {
          return w.length >= 3 && stopWords.indexOf(w) === -1;
        });

        if (words.length === 0) return false;
        return words.every(function (w) {
          return textLower.indexOf(w) !== -1;
        });
      }

      // 4. Focus Keyword at the beginning of content (First 100 words)
      var first100Words = textOnlyLower.split(/\s+/).slice(0, 100).join(" ");
      if (primaryKw && checkKeywordInText(first100Words, primaryKw)) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword used at the beginning of your content.",
          tip: "Focus keyword appears in the first 10% of content.",
        });
      } else {
        if (primaryKw) basicFails++;
        basicItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Use Focus Keyword at the beginning of your content." : "Assign a Focus Keyword to check the content opening.",
          tip: "Include your focus keyword within the first paragraph or first 100 words.",
        });
      }

      // 5. Focus Keyword in the content
      if (primaryKw && checkKeywordInText(textOnlyLower, primaryKw)) {
        basicPasses++;
        basicItems.push({
          status: "pass",
          text: "Focus Keyword found in the content.",
          tip: "Focus keyword is present in the main content.",
        });
      } else {
        if (primaryKw) basicFails++;
        basicItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Use Focus Keyword in the content." : "Assign a Focus Keyword to check content usage.",
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
            : item.status === "warn"
              ? '<span class="gmb-audit-icon warn">&#33;</span>'
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
          !primaryKw && basicFails === 0
            ? "Setup needed"
            : basicFails === 0
              ? "All Good"
            : basicFails + " " + (basicFails === 1 ? "Error" : "Errors"),
        )
        .removeClass("error success warning")
        .addClass(!primaryKw && basicFails === 0 ? "warning" : (basicFails === 0 ? "success" : "error"));

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
        if (primaryKw) addFails++;
        addItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Use Focus Keyword in subheading(s) like H2, H3, H4, etc.." : "Assign a Focus Keyword to check subheadings.",
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
        if (primaryKw) addFails++;
        addItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Add an image with your Focus Keyword as alt text." : "Assign a Focus Keyword to check image alt text.",
          tip: "Add images to your content and set alt text containing the focus keyword.",
        });
      } else {
        if (primaryKw) addFails++;
        addItems.push({
          status: primaryKw ? "fail" : "warn",
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
        if (primaryKw) addFails++;
        addItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw
            ? "Keyword Density is " + density + "%. Aim for around 1% Keyword Density."
            : "Assign a Focus Keyword to measure keyword density.",
          tip: "Keep keyword density around 1% to avoid keyword stuffing.",
        });
      }

      // 4. URL Length (< 75 characters)
      var urlLength = permalinkSlug ? permalinkSlug.trim().length : title.length;
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

      // 8. Focus Keyword Uniqueness & Keyword Cannibalization Check
      var currentPostId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId) ? gmbMetaboxData.postId : ($("#post_ID").val() || 0);
      var kwKey = (primaryKw || "").toLowerCase().trim();
      var cannibalInfo = kwKey ? keywordCannibalizationCache[kwKey] : null;

      if (!kwKey) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Assign a Focus Keyword to check uniqueness.",
          tip: "Each post targets a unique primary keyword.",
        });
      } else if (cannibalInfo && cannibalInfo.is_cannibalized) {
        addFails++;
        var conflictTitles = (cannibalInfo.conflicts || []).map(function(c) {
          return c.title + " (" + c.post_type + ")";
        }).join(", ");
        addItems.push({
          status: "fail",
          text: "Keyword Cannibalization detected! Used in: " + (conflictTitles || "another published page"),
          tip: "Avoid using the exact same Focus Keyword across multiple published posts or pages.",
        });
      } else {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "You haven't used this Focus Keyword before.",
          tip: "Focus keyword is unique across all published posts and pages.",
        });

        if (kwKey && typeof keywordCannibalizationCache[kwKey] === "undefined" && typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.ajaxUrl) {
          keywordCannibalizationCache[kwKey] = { is_cannibalized: false, checking: true };
          if (pendingCannibalizationCheck) clearTimeout(pendingCannibalizationCheck);
          pendingCannibalizationCheck = setTimeout(function() {
            $.ajax({
              url: gmbMetaboxData.ajaxUrl,
              type: "POST",
              data: {
                action: "gmb_check_focus_keyword_uniqueness",
                nonce: gmbMetaboxData.nonce,
                post_id: currentPostId,
                focus_keyword: primaryKw
              },
              success: function(res) {
                if (res && res.success && res.data) {
                  keywordCannibalizationCache[kwKey] = res.data;
                  if (res.data.is_cannibalized) {
                    recalculateScore();
                  }
                }
              }
            });
          }, 300);
        }
      }

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
            : item.status === "warn"
              ? '<span class="gmb-audit-icon warn">&#33;</span>'
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
        .removeClass("error success warning")
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
        if (primaryKw) titleFails++;
        titleItems.push({
          status: primaryKw ? "fail" : "warn",
          text: primaryKw ? "Focus Keyword used at the beginning of SEO title." : "Assign a Focus Keyword to check title placement.",
          tip: "Place your focus keyword within the first few words of the title.",
        });
      }

      // 2. Sentiment in Title (Positive or Negative words)
      var sentimentWords = [
        "essential",
        "important",
        "vital",
        "proven",
        "key",
        "critical",
        "expert",
        "trusted",
        "reliable",
        "complete",
        "effective",
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
        "happy",
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
        "guide",
        "care",
        "support",
        "solution",
        "solutions",
        "tips",
        "insights",
        "help",
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
            : item.status === "warn"
              ? '<span class="gmb-audit-icon warn">&#33;</span>'
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

      // 1. Focus Keyword in Subheadings (H2, H3, H4)
      var headingsMatch = content.match(/<h[2-4][^>]*>(.*?)<\/h[2-4]>/gi) || [];
      if (headingsMatch.length === 0) {
        headingsMatch = content.match(/wp:heading/gi) || [];
      }
      var headingHasKw = false;
      if (primaryKw) {
        var kwParts = primaryKw.split(/\s+/).filter(Boolean);
        for (var h = 0; h < headingsMatch.length; h++) {
          var hText = headingsMatch[h].toLowerCase();
          if (hText.indexOf(primaryKw) !== -1) {
            headingHasKw = true;
            break;
          }
          if (kwParts.length > 1 && kwParts.every(function(part){ return hText.indexOf(part) !== -1; })) {
            headingHasKw = true;
            break;
          }
        }
      }
      if (headingHasKw || headingsMatch.length >= 2) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Focus Keyword / Topic intent found in subheading(s) like H2, H3, H4.",
          tip: "Focus keyword and key sub-topics are structured inside headings.",
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
        typeof gmbMetaboxData === "undefined" || gmbMetaboxData.moduleImageSeo;
      if (imgHasKw) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Focus Keyword found in image alt attribute(s).",
          tip: "Image alt tag contains the focus keyword.",
        });
      } else if (imageSeoActive) {
        addPasses++;
        addItems.push({
          status: "pass",
          text: "Images are optimized with descriptive alt attributes (enhanced by GMB Ranker Image SEO automation).",
          tip: "GMB Ranker Image SEO module automatically injects keyword-rich alt tags on frontend render.",
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

      // 3. Keyword Density (0.3% - 2.5%)
      var kwCount = 0;
      if (primaryKw && wordCount > 0) {
        var re = new RegExp(
          primaryKw.replace(/[-/\\^$*+?.()|[\]{}]/g, "\\$&"),
          "gi",
        );
        var matches = textOnlyLower.match(re);
        kwCount = matches ? matches.length : 0;
      }
      var density = wordCount > 0 ? (kwCount / wordCount) * 100 : 0;
      if (density >= 0.3 && density <= 2.5) {
        addPasses++;
        addItems.push({
          status: "pass",
          text:
            "Focus Keyword Density is ideal (" +
            density.toFixed(2) +
            "%). Aim for 0.3% - 2.5%.",
          tip: "Optimal keyword density avoids search engine over-optimization penalties.",
        });
      } else if (wordCount > 400 && kwCount >= 2) {
        addPasses++;
        addItems.push({
          status: "pass",
          text:
            "Focus Keyword is naturally integrated (" +
            kwCount +
            " occurrences, " +
            density.toFixed(2) +
            "%).",
          tip: "Keyword is well-distributed throughout long-form content.",
        });
      } else {
        addFails++;
        addItems.push({
          status: "fail",
          text:
            "Keyword Density is " +
            density.toFixed(2) +
            "%. Aim for around 0.5% - 2% Keyword Density.",
          tip: "Repeat your focus keyword naturally throughout the post body.",
        });
      }

      // ==========================================
      // 4. Content Readability (3 Tests)
      // ==========================================
      var contentPasses = 0;
      var contentFails = 0;
      var contentItems = [];

      // 1. Table of Contents (Inbuilt TOC Module Integration)
      var hasExplicitToc =
        content.indexOf("gmb-toc-box") !== -1 ||
        content.indexOf("table-of-contents") !== -1 ||
        content.indexOf("[toc") !== -1 ||
        content.indexOf("wp-block-table-of-contents") !== -1 ||
        content.indexOf("wp:gmb-ranker/table-of-contents") !== -1;
      var tocModuleEnabled =
        typeof gmbMetaboxData === "undefined" || !!gmbMetaboxData.moduleToc;
      var minHeadings = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.tocMinHeadings) ? parseInt(gmbMetaboxData.tocMinHeadings, 10) : 2;
      var headingsMatches = content.match(/<h[1-6][^>]*>(.*?)<\/h[1-6]>/gi) || [];
      var headingsCount = headingsMatches.length;

      var isTocActive = hasExplicitToc || (tocModuleEnabled && (headingsCount >= minHeadings || wordCount >= 250));

      if (isTocActive) {
        contentPasses++;
        var tocMsg = hasExplicitToc
          ? "You are using a Table of Contents to break down your text."
          : "Table of Contents is active (automatically generated and inserted by GMB Ranker TOC module).";
        var tocTip = hasExplicitToc
          ? "Table of Contents improves page scannability."
          : "GMB Ranker TOC module automatically generates a Table of Contents on frontend render.";
        contentItems.push({ status: "pass", text: tocMsg, tip: tocTip });
      } else if (tocModuleEnabled && headingsCount < minHeadings) {
        contentFails++;
        contentItems.push({
          status: "fail",
          text: "Add subheadings (H2, H3) for Table of Contents to automatically generate.",
          tip: "GMB Ranker TOC module requires subheadings to generate the Table of Contents index.",
        });
      } else {
        contentFails++;
        contentItems.push({
          status: "fail",
          text: "Use Table of Content to break-down your text.",
          tip: "Enable GMB Ranker TOC module or add a Table of Contents block.",
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
        if (pWords > 130) {
          longParagraph = true;
          break;
        }
      }
      if ((paragraphs.length > 0 && !longParagraph) || wordCount > 50 || content.indexOf("wp:paragraph") !== -1) {
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

      // 3. Media (Images and/or Videos & Image SEO Automation)
      var hasMedia =
        content.indexOf("<img") !== -1 ||
        content.indexOf("<video") !== -1 ||
        content.indexOf("<iframe") !== -1 ||
        content.indexOf("wp-block-image") !== -1 ||
        content.indexOf("wp:image") !== -1 ||
        $("#set-post-thumbnail img").length > 0 ||
        $("#postimagediv img").length > 0 ||
        imageSeoActive;
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
      // Live editor preview only. The server-side analysis service remains
      // authoritative for saved scores and post-list values.
      // ==========================================
      var weightedScore = 0;

      // 1. Title Length (10 pts)
      if (metaTitleLen >= 40 && metaTitleLen <= 75) {
        weightedScore += 10;
      }

      // 2. Meta Description (10 pts)
      if (metaDescLen >= 100 && metaDescLen <= 170) {
        weightedScore += 10;
      } else if (metaDescLen > 0) {
        weightedScore += 5;
      }

      // 3. Word Count (15 pts)
      if (wordCount >= 500) {
        weightedScore += 15;
      } else if (wordCount >= 250) {
        weightedScore += 10;
      } else if (wordCount > 0) {
        weightedScore += 5;
      }

      // 4. Focus Keyword Optimization (35 pts)
      if (primaryKw) {
        if (titleHasKw) weightedScore += 10;
        if (descHasKw) weightedScore += 10;
        if (kwCount > 0) {
          weightedScore += 10;
          if (kwDensity >= 0.5 && kwDensity <= 2.5) {
            weightedScore += 5;
          }
        }
      } else {
        if (wordCount >= 200) weightedScore += 15;
      }

      // 5. Table of Contents (10 pts)
      if (hasExplicitToc || (metaboxConfig.moduleToc && headingsCount >= (metaboxConfig.tocMinHeadings || 2))) {
        weightedScore += 10;
      }

      // 6. Schema (10 pts)
      if (metaboxConfig.moduleSchema !== false) {
        weightedScore += 10;
      }

      // 7. Media & Alt (5 pts)
      if (hasMedia) {
        if (metaboxConfig.moduleImageSeo) {
          weightedScore += 5;
        } else {
          weightedScore += 2;
        }
      }

      // 8. Links (5 pts)
      if (hasLinks || (metaboxConfig.moduleLinks && (hasInternalLinks || hasExternalLinks))) {
        weightedScore += 5;
      }

      // Gutenberg/TinyMCE can be unavailable during initial editor hydration; do not show a false zero.
      var score = (!content && savedScore > 0) ? savedScore : Math.min(100, Math.max(0, weightedScore));

      $("#gmb-metabox-score-val").text(score);
      $("#gmb_seo_score_hidden").val(score);

      var $freshness = $("#gmb-audit-freshness");
      if ($freshness.length && !$freshness.hasClass("is-stale") && !$freshness.attr("data-analysis-hash")) {
        $freshness.text("Live preview — save to run the server audit");
      }

      var $scoreBadge = $("#gmb-metabox-score-badge");
      var $pubBadge = $("#gmb-publish-score-val");
      if ($pubBadge.length) {
        $pubBadge
          .text(score + " / 100")
          .removeClass("green orange red")
          .addClass(score >= 80 ? "green" : score >= 40 ? "orange" : "red");
      }

      var scoreClass = "score-poor";
      if (!primaryKw) {
        scoreClass = "score-unconfigured";
      } else if (score >= 80) {
        scoreClass = "score-good";
      } else if (score >= 40) {
        scoreClass = "score-ok";
      }

      $scoreBadge
        .removeClass("score-good score-ok score-poor score-unconfigured")
        .addClass(scoreClass)
        .css("background-color", "");
    }

    // The browser score is only a live preview until WordPress saves and audits
    // the post. Never present it as the latest server-side audit after edits.
    function markAuditStale() {
      var $freshness = $("#gmb-audit-freshness");
      if (!$freshness.length || $freshness.hasClass("is-stale")) {
        return;
      }
      $freshness
        .addClass("is-stale")
        .text("Unsaved changes - save to refresh the audit score");
    }

    $(document).on(
      "input change",
      "#title, #content, #gmb_seo_title_input, #gmb_seo_desc_input, #gmb_seo_focus_keyword_input, #gmb_seo_focus_keyword_hidden, #gmb_seo_canonical_input, #gmb_seo_robots_input",
      markAuditStale,
    );

    recalculateScore();

    // Initialize ARIA state to match the server-rendered visible panels.
    $(".gmb-tab-content").each(function () {
      $(this).attr("aria-hidden", $(this).hasClass("active") ? "false" : "true");
    });
    $(".gmb-social-pane").each(function () {
      $(this).attr("aria-hidden", $(this).hasClass("active") ? "false" : "true");
    });

    // ==========================================
    // 9. Schema Generator Modal
    // ==========================================
    window.gmbOpenSchemaModal = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      var $modal = $("#gmb-schema-modal");
      if ($modal.length) {
        $modal.appendTo("body");
        $modal.addClass("active is-open").css("display", "flex").show();
      }
    };

    function saveSchemaViaAjax(activeSchemas, schemaJson, callback) {
      var postId = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.postId) ? gmbMetaboxData.postId : ($("#post_ID").val() || 0);
      var nonce = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.nonce) ? gmbMetaboxData.nonce : ($("#gmb_seo_nonce").val() || "");
      var ajaxUrl = (typeof gmbMetaboxData !== "undefined" && gmbMetaboxData.ajaxUrl) ? gmbMetaboxData.ajaxUrl : (window.ajaxurl || "/wp-admin/admin-ajax.php");

      $.ajax({
        url: ajaxUrl,
        type: "POST",
        dataType: "json",
        data: {
          action: "gmb_save_post_schema",
          nonce: nonce,
          post_id: postId,
          active_schemas: activeSchemas || $("#gmb_seo_active_schemas").val() || "",
          schema_json: schemaJson !== undefined ? schemaJson : ($("#gmb_seo_schema_input").val() || "")
        },
        success: function (res) {
          if (res && res.success) {
            if (typeof callback === "function") callback(res.data);
          } else {
            $("#gmb-schema-modal-save-btn, #gmb-builder-save-post-btn, #gmb-builder-save-template-btn").prop("disabled", false);
            alert("Error saving schema: " + (res && res.data && res.data.message ? res.data.message : "Save failed"));
          }
        },
        error: function (xhr, status, err) {
          $("#gmb-schema-modal-save-btn, #gmb-builder-save-post-btn, #gmb-builder-save-template-btn").prop("disabled", false);
          alert("Network error while saving schema: " + (err || "Save failed"));
        }
      });
    }

    $(document).on("click", "#gmb-schema-modal-close-btn", function (e) {
      e.preventDefault();
      $("#gmb-schema-modal").removeClass("active is-open").hide();
    });

    $(document).on("click", "#gmb-schema-modal", function (e) {
      if (e.target === this) {
        $(this).removeClass("active is-open").hide();
      }
    });

    $(document).on("keydown", function (e) {
      if (e.key !== "Escape" && e.keyCode !== 27) return;
      $("#gmb-schema-modal, #gmb-schema-builder-modal").removeClass("active is-open").hide();
    });

    $(document).on("click", "#gmb-schema-modal-save-btn", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var origText = $btn.text();
      $btn.prop("disabled", true).text("Saving...");

      var activeSchemas = $("#gmb_seo_active_schemas").val() || "";
      var customSchema = $("#gmb_seo_schema_input").val() || "";

      if (customSchema.trim()) {
        var parsedSchema;
        try {
          parsedSchema = JSON.parse(customSchema);
        } catch (err) {
          $btn.prop("disabled", false).text(origText);
          alert("Cannot save schema: invalid JSON. Please correct the syntax first.");
          return;
        }
        if (!parsedSchema || typeof parsedSchema !== "object" || (!parsedSchema["@type"] && !parsedSchema["@graph"])) {
          $btn.prop("disabled", false).text(origText);
          alert("Cannot save schema: include an @type or @graph property.");
          return;
        }
      }

      saveSchemaViaAjax(activeSchemas, customSchema, function (data) {
        $btn.prop("disabled", false).text("Saved!");
        setTimeout(function () {
          $btn.text(origText);
          $("#gmb-schema-modal").removeClass("active is-open").hide();
        }, 600);
      });
    });

    // Schema Generator Tab Switcher. Expose a direct function as well as the
    // delegated handler below so tab switching remains reliable if another
    // plugin rebinds document click handlers or moves the modal in the DOM.
    window.gmbSwitchSchemaTab = function (targetTab, e) {
      if (e && e.preventDefault) e.preventDefault();
      if (!targetTab || !/^schema-tab-[a-z-]+$/.test(targetTab)) return false;

      var $modal = $("#gmb-schema-modal");
      var $button = $modal.find(".gmb-modal-tab-btn[data-schema-tab='" + targetTab + "']");
      var $panel = $modal.find("#" + targetTab);
      if (!$button.length || !$panel.length) return false;

      $modal.find(".gmb-modal-tab-btn[data-schema-tab]").removeClass("active").attr("aria-selected", "false");
      $button.addClass("active").attr("aria-selected", "true");
      $modal.find(".gmb-schema-tab-content").hide().removeClass("active").attr("aria-hidden", "true");
      $panel.show().addClass("active").attr("aria-hidden", "false");
      return false;
    };

    $(document).on(
      "click",
      "#gmb-schema-modal .gmb-modal-tab-btn",
      function (e) {
        e.preventDefault();
        var targetTab = $(this).attr("data-schema-tab");
        if (targetTab) window.gmbSwitchSchemaTab(targetTab, e);
      },
    );

    // Import Tab Submit Handler
    $(document).on("click", "#gmb-schema-import-submit-btn", function (e) {
      e.preventDefault();
      var rawInput = ($("#gmb_seo_schema_import_input").val() || "").trim();
      if (!rawInput) {
        alert("Please paste JSON-LD markup or a script block to import.");
        return;
      }

      // Strip <script> tags if present
      var cleanJson = rawInput.replace(/<script[^>]*>/gi, "").replace(/<\/script>/gi, "").trim();

      var parsedObj = null;
      try {
        parsedObj = JSON.parse(cleanJson);
      } catch (errJson) {
        alert("Invalid JSON-LD format. Please verify syntax and try again: " + errJson.message);
        return;
      }

      if (!parsedObj || typeof parsedObj !== "object") {
        alert("Imported content must be a valid JSON-LD object or array.");
        return;
      }

      if (!parsedObj["@type"] && !parsedObj["@graph"]) {
        alert("Imported JSON-LD must include an @type or @graph property.");
        return;
      }

      var detectedType = parsedObj["@type"] || "Article";
      if (Array.isArray(detectedType)) detectedType = detectedType[0];
      detectedType = String(detectedType || "Article");
      if (!/^[A-Za-z][A-Za-z0-9_-]*$/.test(detectedType)) {
        alert("The imported schema contains an unsupported schema type.");
        return;
      }

      $("#gmb_seo_schema_input").val(JSON.stringify(parsedObj, null, 2));

      // Add to active schema list
      var $list = $("#gmb-schema-in-use-list");
      if ($list.find('[data-schema-active="' + detectedType + '"]').length === 0) {
        var iconSvg = getSchemaIcon(detectedType);
        var cardHtml =
          '<div class="gmb-schema-active-card" data-schema-active="' +
          detectedType +
          '">' +
          '<div class="gmb-schema-active-info">' +
          '<span class="gmb-schema-active-icon">' +
          iconSvg +
          "</span>" +
          '<strong class="gmb-schema-active-title">' +
          detectedType +
          "</strong>" +
          "</div>" +
          '<div class="gmb-schema-active-actions">' +
          '<button type="button" class="gmb-schema-action-btn gmb-schema-edit-btn" data-type="' +
          detectedType +
          '" title="Edit Schema">' +
          '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>' +
          "</button>" +
          '<button type="button" class="gmb-schema-action-btn gmb-schema-code-btn" data-type="' +
          detectedType +
          '" title="Code Validation">' +
          '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>' +
          "</button>" +
          '<button type="button" class="gmb-schema-action-btn gmb-remove-schema-btn" data-type="' +
          detectedType +
          '" title="Delete Schema">' +
          '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>' +
          "</button>" +
          "</div>" +
          "</div>";
        $("#gmb-no-active-schema-notice").hide();
        $list.append(cardHtml);
        updateActiveSchemasHidden();
      }

      var activeSchemas = $("#gmb_seo_active_schemas").val() || detectedType;
      saveSchemaViaAjax(activeSchemas, JSON.stringify(parsedObj), function () {
        alert("Imported " + detectedType + " schema successfully!");
        $("#gmb-schema-modal .gmb-modal-tab-btn[data-schema-tab='schema-tab-templates']").trigger("click");
      });
    });

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
    window.gmbGetSchemaIcon = getSchemaIcon;

    // Repair cards that were created by an older handler without an icon.
    $("#gmb-schema-in-use-list .gmb-schema-active-card").each(function () {
      var $card = $(this);
      var type = $card.attr("data-schema-active") || "Article";
      var $icon = $card.children(".gmb-schema-active-info").find(".gmb-schema-active-icon");
      if (!$icon.length) {
        $icon = $('<span class="gmb-schema-active-icon"></span>');
        $card.children(".gmb-schema-active-info").prepend($icon);
      }
      if (!$icon.find("svg").length) {
        $icon.html(getSchemaIcon(type));
      }
    });

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
    var currentBuilderMode = "simple";

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

      if (k === "article" || k === "blogposting" || k === "newsarticle") {
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
          name: $("#gmb_schema_field_author").val() || "Author",
        };
        schema["publisher"] = {
          "@type": "Organization",
          name: siteName,
          url: homeUrl,
        };
      } else if (k === "book") {
        schema["@type"] = "Book";
        schema["name"] = $("#gmb_schema_field_book_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_book_desc").val() || description || desc;
        schema["author"] = {
          "@type": "Person",
          name: $("#gmb_schema_field_book_author").val() || "Author Name"
        };
        var isbn = $("#gmb_schema_field_book_isbn").val();
        if (isbn) schema["isbn"] = isbn;
      } else if (k === "course") {
        schema["@type"] = "Course";
        schema["name"] = $("#gmb_schema_field_course_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_course_desc").val() || description || desc;
        schema["provider"] = {
          "@type": "Organization",
          name: $("#gmb_schema_field_course_provider").val() || siteName,
          sameAs: homeUrl
        };
      } else if (k === "dataset") {
        schema["@type"] = "Dataset";
        schema["name"] = $("#gmb_schema_field_dataset_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_dataset_desc").val() || description || desc;
        schema["license"] = homeUrl;
      } else if (k === "factcheck") {
        schema["@type"] = "FactCheck";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
        schema["claimReviewed"] = $("#gmb_schema_field_claim").val() || headline || title;
        schema["reviewRating"] = {
          "@type": "Rating",
          ratingValue: $("#gmb_schema_field_rating").val() || "5",
          bestRating: "5",
          worstRating: "1"
        };
      } else if (k === "movie") {
        schema["@type"] = "Movie";
        schema["name"] = $("#gmb_schema_field_movie_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_movie_desc").val() || description || desc;
        schema["director"] = {
          "@type": "Person",
          name: $("#gmb_schema_field_movie_director").val() || "Director Name"
        };
      } else if (k === "music") {
        schema["@type"] = "MusicAlbum";
        schema["name"] = $("#gmb_schema_field_music_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_music_desc").val() || description || desc;
        schema["byArtist"] = {
          "@type": "MusicGroup",
          name: $("#gmb_schema_field_music_artist").val() || "Artist Name"
        };
      } else if (k === "person") {
        schema["@type"] = "Person";
        schema["name"] = $("#gmb_schema_field_person_name").val() || siteName;
        schema["jobTitle"] = $("#gmb_schema_field_person_job").val() || "Specialist";
        schema["worksFor"] = {
          "@type": "Organization",
          name: siteName
        };
      } else if (k === "recipe") {
        schema["@type"] = "Recipe";
        schema["name"] = $("#gmb_schema_field_recipe_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_recipe_desc").val() || description || desc;
        schema["author"] = {
          "@type": "Person",
          name: siteName
        };
      } else if (k === "restaurant") {
        schema["@type"] = "Restaurant";
        schema["name"] = $("#gmb_schema_field_rest_name").val() || siteName;
        schema["description"] = $("#gmb_schema_field_rest_desc").val() || description || desc;
        schema["servesCuisine"] = $("#gmb_schema_field_rest_cuisine").val() || "General";
      } else if (k === "software") {
        schema["@type"] = "SoftwareApplication";
        schema["name"] = $("#gmb_schema_field_soft_name").val() || headline || title;
        schema["operatingSystem"] = $("#gmb_schema_field_soft_os").val() || "All";
        schema["applicationCategory"] = $("#gmb_schema_field_soft_cat").val() || "BusinessApplication";
      } else if (k === "video") {
        schema["@type"] = "VideoObject";
        schema["name"] = $("#gmb_schema_field_vid_name").val() || headline || title;
        schema["description"] = $("#gmb_schema_field_vid_desc").val() || description || desc;
        schema["uploadDate"] = new Date().toISOString().split("T")[0];
      } else if (k === "product") {
        schema["@type"] = "Product";
        schema["name"] = resolveVariables(
          $("#gmb_schema_field_prod_name").val() || headline || title,
        );
        schema["description"] = resolveVariables(
          $("#gmb_schema_field_prod_desc").val() || description || desc,
        );
        var prodSku = $("#gmb_schema_field_prod_sku").val();
        if (prodSku) schema["sku"] = prodSku;
        schema["brand"] = {
          "@type": "Brand",
          name: $("#gmb_schema_field_prod_brand").val() || siteName,
        };
        var prodPrice = $("#gmb_schema_field_prod_price").val();
        if (prodPrice) {
          schema["offers"] = {
            "@type": "Offer",
            price: prodPrice,
            priceCurrency: $("#gmb_schema_field_prod_currency").val() || "USD",
            availability:
              "https://schema.org/" +
              ($("#gmb_schema_field_prod_avail").val() || "InStock"),
            url: currentUrl,
          };
        }
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
        if (faqs.length > 0) {
          schema["mainEntity"] = faqs;
        }
      } else if (k === "howto") {
        schema["@type"] = "HowTo";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
      } else if (k === "event") {
        schema["@type"] = "Event";
        schema["name"] = headline || title;
        schema["description"] = description || desc;
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

      if (k === "article" || k === "blogposting" || k === "newsarticle") {
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
          '<label class="gmb-builder-label">ARTICLE TYPE <span class="gmb-required-star">*</span></label>' +
          '<select id="gmb_schema_field_article_type" class="gmb-field-select">' +
          '<option value="Article">Article</option>' +
          '<option value="BlogPosting">BlogPosting</option>' +
          '<option value="NewsArticle">NewsArticle</option>' +
          "</select>" +
          "</div>";
      } else if (k === "book") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">BOOK TITLE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_book_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">AUTHOR NAME</label>' +
          '<input type="text" id="gmb_schema_field_book_author" class="gmb-field-input" placeholder="Author Name" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">ISBN</label>' +
          '<input type="text" id="gmb_schema_field_book_isbn" class="gmb-field-input" placeholder="e.g. 978-3-16-148410-0" />' +
          "</div>";
      } else if (k === "course") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">COURSE TITLE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_course_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">PROVIDER / ORGANIZATION</label>' +
          '<input type="text" id="gmb_schema_field_course_provider" class="gmb-field-input" value="%site_title%" placeholder="%site_title%" />' +
          "</div>";
      } else if (k === "dataset") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DATASET NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_dataset_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_dataset_desc" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
          "</div>";
      } else if (k === "factcheck") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">CLAIM REVIEWED <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_claim" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">RATING (1 to 5)</label>' +
          '<input type="text" id="gmb_schema_field_rating" class="gmb-field-input" value="5" placeholder="5" />' +
          "</div>";
      } else if (k === "movie") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">MOVIE TITLE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_movie_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DIRECTOR</label>' +
          '<input type="text" id="gmb_schema_field_movie_director" class="gmb-field-input" placeholder="Director Name" />' +
          "</div>";
      } else if (k === "music") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">ALBUM / TRACK NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_music_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">ARTIST / BAND</label>' +
          '<input type="text" id="gmb_schema_field_music_artist" class="gmb-field-input" placeholder="Artist Name" />' +
          "</div>";
      } else if (k === "person") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">PERSON FULL NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_person_name" class="gmb-field-input" value="%site_title%" placeholder="%site_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">JOB TITLE</label>' +
          '<input type="text" id="gmb_schema_field_person_job" class="gmb-field-input" placeholder="Specialist" />' +
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
          '<input type="text" id="gmb_schema_field_prod_brand" class="gmb-field-input" placeholder="e.g. Brand Name" />' +
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
      } else if (k === "recipe") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">RECIPE NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_recipe_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_recipe_desc" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
          "</div>";
      } else if (k === "restaurant") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">RESTAURANT NAME <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_rest_name" class="gmb-field-input" value="%site_title%" placeholder="%site_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">CUISINE TYPE</label>' +
          '<input type="text" id="gmb_schema_field_rest_cuisine" class="gmb-field-input" placeholder="e.g. Italian, Nepalese, Asian" />' +
          "</div>";
      } else if (k === "software") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">SOFTWARE TITLE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_soft_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">OPERATING SYSTEM</label>' +
          '<input type="text" id="gmb_schema_field_soft_os" class="gmb-field-input" value="All" placeholder="e.g. Windows, macOS, Web" />' +
          "</div>";
      } else if (k === "video") {
        html +=
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">VIDEO TITLE <span class="gmb-required-star">*</span></label>' +
          '<input type="text" id="gmb_schema_field_vid_name" class="gmb-field-input" value="%seo_title%" placeholder="%seo_title%" />' +
          "</div>" +
          '<div class="gmb-builder-field-row">' +
          '<label class="gmb-builder-label">DESCRIPTION</label>' +
          '<textarea id="gmb_schema_field_vid_desc" rows="4" class="gmb-field-textarea" placeholder="%seo_description%">%seo_description%</textarea>' +
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

      // Rehydrate the builder from saved JSON-LD when editing an existing
      // schema. This prevents Edit from replacing custom properties with
      // freshly generated defaults.
      var currentSchema = null;
      try {
        var persistedSchema = JSON.parse($("#gmb_seo_schema_input").val() || "");
        var persistedType = persistedSchema && persistedSchema["@type"] ? String(persistedSchema["@type"]) : "";
        if (persistedSchema && typeof persistedSchema === "object" &&
            (!persistedType || persistedType.toLowerCase().replace(/[\s\-_]/g, "") === k)) {
          currentSchema = persistedSchema;
        }
      } catch (e) {
        currentSchema = null;
      }
      currentSchema = currentSchema || generateSchemaJsonLd(type);

      var simpleFieldMap = {
        headline: "headline", description: "description", keywords: "keywords",
        article_type: "@type", book_name: "name", book_desc: "description",
        book_author: "author", book_isbn: "isbn", course_name: "name",
        course_desc: "description", course_provider: "provider", dataset_name: "name",
        dataset_desc: "description", claim: "claimReviewed", rating: "ratingValue",
        movie_name: "name", movie_desc: "description", movie_director: "director",
        music_name: "name", music_desc: "description", music_artist: "byArtist",
        person_name: "name", person_job: "jobTitle", prod_name: "name",
        prod_desc: "description", prod_sku: "sku", prod_brand: "brand",
        prod_price: "price", prod_currency: "priceCurrency", prod_avail: "availability",
        recipe_name: "name", recipe_desc: "description", rest_name: "name",
        rest_desc: "description", rest_cuisine: "servesCuisine", soft_name: "name",
        soft_os: "operatingSystem", soft_cat: "applicationCategory", vid_name: "name",
        vid_desc: "description"
      };
      Object.keys(simpleFieldMap).forEach(function (suffix) {
        var $field = $("#gmb_schema_field_" + suffix);
        if (!$field.length) return;
        var value = currentSchema[simpleFieldMap[suffix]];
        if (value && typeof value === "object") {
          value = value.name || value["@type"] || value.url || "";
        }
        if (value !== undefined && value !== null && value !== "") {
          $field.val(value);
        }
      });

      // Populate Advanced property tree
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

    window.gmbOpenSchemaBuilder = function (schemaType, activeTab) {
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

      var $builderModal = $("#gmb-schema-builder-modal");
      if ($builderModal.length) {
        $builderModal.appendTo("body");
        $builderModal.addClass("active is-open").css("display", "flex").show();
      }
    };

    function openSchemaBuilder(schemaType, activeTab) {
      window.gmbOpenSchemaBuilder(schemaType, activeTab);
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
      $("#gmb-schema-builder-modal").removeClass("active is-open").hide();
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
        $btn.prop("disabled", true).text("Saving...");

        // "Save as Template" has a different persistence target from
        // "Save for this Post". Do not silently save it only to post meta.
        if ($btn.is("#gmb-builder-save-template-btn")) {
          var templateName = window.prompt("Template name:", currentBuilderType + " - SEO Template");
          if (!templateName || !templateName.trim()) {
            $btn.prop("disabled", false).text(origText);
            return;
          }
          $.ajax({
            url: gmbMetaboxData.ajaxUrl,
            type: "POST",
            data: {
              action: "gmb_save_schema_template",
              nonce: gmbMetaboxData.nonce,
              title: templateName.trim(),
              name: templateName.trim(),
              type: currentBuilderType,
              post_type: gmbMetaboxData.postType || "post",
              schema_json: code,
              enabled: 1
            }
          }).done(function (res) {
            if (res && res.success) {
              $btn.text("Saved!");
              setTimeout(function () { $btn.prop("disabled", false).text(origText); }, 900);
            } else {
              alert((res && res.data && res.data.message) || "Template could not be saved.");
              $btn.prop("disabled", false).text(origText);
            }
          }).fail(function () {
            alert("Template could not be saved. Please try again.");
            $btn.prop("disabled", false).text(origText);
          });
          return;
        }

        var activeSchemas = $("#gmb_seo_active_schemas").val() || currentBuilderType;
        saveSchemaViaAjax(activeSchemas, code, function () {
          $btn.prop("disabled", false).text("Saved!");
          setTimeout(function () {
            $btn.text(origText);
            $("#gmb-schema-builder-modal").removeClass("active is-open").hide();
          }, 600);
        });
      },
    );

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
          var schemaTypeEscaped = escAttr(schemaType);
          var schemaTypeHtml = escHtml(schemaType);
          var cardHtml =
            '<div class="gmb-schema-active-card" data-schema-active="' +
        schemaTypeEscaped +
            '">' +
            '<div class="gmb-schema-active-info">' +
            '<span class="gmb-schema-active-icon">' +
            iconSvg +
            "</span>" +
            '<strong class="gmb-schema-active-title">' +
            schemaTypeHtml +
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
    // 10. Media Picker & Social Image Management (WordPress wp.media)
    // ==========================================
    function updateSocialPreviewImage(targetInputId, imgUrl) {
      var isFb = (targetInputId === "gmb_seo_fb_image" || targetInputId === "gmb_seo_fb_image_metabox");
      var isTw = (targetInputId === "gmb_seo_tw_image" || targetInputId === "gmb_seo_tw_image_metabox");

      if (isFb) {
        // The snippet modal and Social tab use separate fields. Keep both
        // previews in sync regardless of which field initiated the change.
        var $generalFbPreview = $("#gmb_seo_fb_image_preview");
        var $generalFbImg = $generalFbPreview.find("img");
        if ($generalFbImg.length) {
          $generalFbImg.attr("src", imgUrl || "");
          $generalFbPreview.toggleClass("is-active", !!imgUrl);
        }
        var $img = $("#gmb-fb-preview-img");
        var $placeholder = $("#gmb-fb-preview-placeholder");
        var $clearBtn = $('.gmb-social-clear-img-btn[data-target="' + targetInputId + '"]');

        if (imgUrl) {
          $img.attr("src", imgUrl).addClass("is-active").show();
          $placeholder.addClass("is-hidden").hide();
          $clearBtn.addClass("is-active");
        } else {
          $img.attr("src", "").removeClass("is-active").hide();
          $placeholder.removeClass("is-hidden").show();
          $clearBtn.removeClass("is-active");
        }
      } else if (isTw) {
        var $generalTwPreview = $("#gmb_seo_tw_image_preview");
        var $generalTwImg = $generalTwPreview.find("img");
        if ($generalTwImg.length) {
          $generalTwImg.attr("src", imgUrl || "");
          $generalTwPreview.toggleClass("is-active", !!imgUrl);
        }
        var $imgTw = $("#gmb-tw-preview-img");
        var $placeholderTw = $("#gmb-tw-preview-placeholder");
        var $clearBtnTw = $('.gmb-social-clear-img-btn[data-target="' + targetInputId + '"]');

        if (imgUrl) {
          $imgTw.attr("src", imgUrl).addClass("is-active").show();
          $placeholderTw.addClass("is-hidden").hide();
          $clearBtnTw.addClass("is-active");
        } else {
          $imgTw.attr("src", "").removeClass("is-active").hide();
          $placeholderTw.removeClass("is-hidden").show();
          $clearBtnTw.removeClass("is-active");
        }
      }
    }

    // Media Picker Click Handler (Buttons & Preview Click Box)
    $(document).on("click", ".gmb-media-upload-btn, .gmb-media-upload-trigger", function (e) {
      e.preventDefault();
      var targetInputId = $(this).attr("data-target");
      if (!targetInputId) return;

      if (typeof wp === "undefined" || !wp.media) {
        if (typeof wp !== "undefined" && wp.media && typeof wp.media.editor !== "undefined") {
          wp.media.editor.open(targetInputId);
          return;
        }
        alert("WordPress Media Library is not available on this screen. Please refresh the page.");
        return;
      }

      var frame = wp.media({
        title: "Select SEO Social Image",
        button: { text: "Use Image" },
        multiple: false,
      });

      frame.on("select", function () {
        var attachment = frame.state().get("selection").first().toJSON();
        if (attachment && attachment.url) {
          $("#" + targetInputId)
            .val(attachment.url)
            .trigger("input")
            .trigger("change");

          updateSocialPreviewImage(targetInputId, attachment.url);
        }
      });

      frame.open();
    });

    // Handle Manual Input Change for Social Images
    $(document).on("input change", "#gmb_seo_fb_image_metabox, #gmb_seo_tw_image_metabox, #gmb_seo_fb_image, #gmb_seo_tw_image", function () {
      var targetInputId = $(this).attr("id");
      var imgUrl = ($(this).val() || "").trim();
      if (targetInputId === "gmb_seo_fb_image" || targetInputId === "gmb_seo_fb_image_metabox") {
        $("#gmb_seo_fb_image, #gmb_seo_fb_image_metabox").not(this).val(imgUrl);
      } else if (targetInputId === "gmb_seo_tw_image" || targetInputId === "gmb_seo_tw_image_metabox") {
        $("#gmb_seo_tw_image, #gmb_seo_tw_image_metabox").not(this).val(imgUrl);
      }
      updateSocialPreviewImage(targetInputId, imgUrl);
    });

    // Handle Clear / Remove Image Button
    $(document).on("click", ".gmb-social-clear-img-btn", function (e) {
      e.preventDefault();
      var targetInputId = $(this).attr("data-target");
      if (targetInputId) {
        $("#" + targetInputId).val("").trigger("input").trigger("change");
        updateSocialPreviewImage(targetInputId, "");
      }
    });
  });
})(jQuery);
