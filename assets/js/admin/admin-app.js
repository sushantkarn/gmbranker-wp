// Intercept global fetch to automatically inject CSRF nonce for all GMB Ranker AJAX requests
const originalFetch = window.fetch;
window.fetch = function (input, init) {
  if (
    typeof input === "string" &&
    input.includes("admin-ajax.php") &&
    init &&
    init.body
  ) {
    let bodyStr = "";
    if (init.body instanceof URLSearchParams) {
      bodyStr = init.body.toString();
      if (bodyStr.includes("action=gmb_") && !bodyStr.includes("nonce=")) {
        init.body.append("nonce", window.gmb_ranker_admin.nonce);
      }
    } else if (typeof init.body === "string") {
      bodyStr = init.body;
      if (bodyStr.includes("action=gmb_") && !bodyStr.includes("nonce=")) {
        init.body +=
          "&nonce=" + encodeURIComponent(window.gmb_ranker_admin.nonce);
      }
    }
  }
  return originalFetch.apply(this, arguments);
};

// Intercept jQuery AJAX requests to inject nonce automatically
if (typeof jQuery !== "undefined") {
  jQuery(document).ajaxSend(function (event, xhr, settings) {
    if (settings.data) {
      if (typeof settings.data === "string") {
        if (
          settings.data.indexOf("action=gmb_") !== -1 &&
          settings.data.indexOf("nonce=") === -1
        ) {
          settings.data +=
            "&nonce=" + encodeURIComponent(window.gmb_ranker_admin.nonce);
        }
      } else if (typeof settings.data === "object") {
        if (
          settings.data.action &&
          settings.data.action.indexOf("gmb_") === 0 &&
          !settings.data.nonce
        ) {
          settings.data.nonce = window.gmb_ranker_admin.nonce;
        }
      }
    }
  });
}

function initGmbAdminApp() {
  // Initialize Sidebar Navigation Switching for all admin screens
  function initGmbSidebarNavigation() {
    const sidebarItems = document.querySelectorAll(".gmb-sidebar-nav-item");
    sidebarItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        e.preventDefault();
        const targetPanelId = item.getAttribute("data-subtab");
        if (!targetPanelId) return;

        const container =
          item.closest(".gmb-sidebar-layout-container") || document;
        const navGroup = item.closest(".gmb-sidebar-nav") || container;
        const siblingItems = navGroup.querySelectorAll(".gmb-sidebar-nav-item");
        const subtabPanels = container.querySelectorAll(".gmb-subtab-panel");

        // Reset sibling nav items
        siblingItems.forEach((i) => {
          i.classList.remove("active");
          i.style.borderLeftColor = "";
          i.style.background = "";
          i.style.color = "";
          i.style.fontWeight = "";
        });

        // Set active nav item
        item.classList.add("active");

        // Hide all panels
        subtabPanels.forEach((panel) => {
          panel.classList.remove("active", "is-active");
          panel.style.display = "none";
        });

        // Display target panel
        const targetPanel = document.getElementById(targetPanelId);
        if (targetPanel) {
          targetPanel.classList.add("active");
          targetPanel.style.display = "block";
        }

        // Sync URL parameter & storage
        const cleanSub = targetPanelId
          .replace("gmb-subtab-sitemap-", "")
          .replace("gmb-subtab-schema-", "")
          .replace("gmb-subtab-", "");
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set("tab", cleanSub);

        if (window.history && window.history.replaceState) {
          window.history.replaceState({}, "", currentUrl.toString());
        }

        const pageParam =
          currentUrl.searchParams.get("page") || "gmb-ranker-settings";
        try {
          sessionStorage.setItem("gmb_active_subtab_" + pageParam, cleanSub);
        } catch (e) {}

        // Update active subtab hidden inputs in forms
        const subtabInput =
          container.querySelector('input[name="gmb_active_subtab"]') ||
          document.querySelector('input[name="gmb_active_subtab"]');
        if (subtabInput) {
          subtabInput.value = cleanSub;
        }

        // Update _wp_http_referer to preserve active subtab on redirect after options.php save
        const refererInputs = document.querySelectorAll(
          'input[name="_wp_http_referer"]',
        );
        refererInputs.forEach((refInput) => {
          try {
            const refUrl = new URL(refInput.value, window.location.origin);
            refUrl.searchParams.set("tab", cleanSub);
            refInput.value = refUrl.pathname + refUrl.search;
          } catch (err) {
            refInput.value = currentUrl.pathname + currentUrl.search;
          }
        });
      });
    });
  }

  initGmbSidebarNavigation();

  // Sync active subtab to _wp_http_referer and sessionStorage on form submit
  document.querySelectorAll('form[action="options.php"]').forEach((form) => {
    form.addEventListener("submit", function () {
      const activeNav =
        form.querySelector(".gmb-sidebar-nav-item.active") ||
        document.querySelector(".gmb-sidebar-nav-item.active");
      if (activeNav) {
        const targetPanelId = activeNav.getAttribute("data-subtab");
        if (targetPanelId) {
          const cleanSub = targetPanelId
            .replace("gmb-subtab-sitemap-", "")
            .replace("gmb-subtab-schema-", "")
            .replace("gmb-subtab-", "");
          const pageParam =
            new URLSearchParams(window.location.search).get("page") ||
            "gmb-ranker-settings";
          try {
            sessionStorage.setItem("gmb_active_subtab_" + pageParam, cleanSub);
          } catch (e) {}

          const refInputs = form.querySelectorAll(
            'input[name="_wp_http_referer"]',
          );
          refInputs.forEach((refInput) => {
            try {
              const refUrl = new URL(refInput.value, window.location.origin);
              refUrl.searchParams.set("tab", cleanSub);
              refInput.value = refUrl.pathname + refUrl.search;
            } catch (err) {
              const curUrl = new URL(window.location.href);
              curUrl.searchParams.set("tab", cleanSub);
              refInput.value = curUrl.pathname + curUrl.search;
            }
          });
        }
      }
    });
  });


  // URL Tab Auto-clicker trigger
  const urlParams = new URLSearchParams(window.location.search);
  const pageParam = urlParams.get("page");
  let activeTabParam = urlParams.get("tab") || urlParams.get("subtab");

  if (!activeTabParam && pageParam) {
    try {
      activeTabParam = sessionStorage.getItem("gmb_active_subtab_" + pageParam);
    } catch (e) {}
  }

  if (pageParam === "gmb-ranker-settings") {
    let subtabSelector = "gmb-subtab-links";
    if (activeTabParam) {
      if (activeTabParam === "settings") {
        subtabSelector = "gmb-subtab-links";
      } else {
        subtabSelector = `gmb-subtab-${activeTabParam}`;
      }
    }
    const subtabBtn = document.querySelector(
      `.gmb-sidebar-nav-item[data-subtab="${subtabSelector}"]`,
    );
    if (subtabBtn) {
      subtabBtn.click();
    }
  } else if (pageParam === "gmb-ranker-metadata") {
    let subtabSelector = "gmb-subtab-metadata";
    if (activeTabParam) {
      if (activeTabParam === "settings") {
        subtabSelector = "gmb-subtab-metadata";
      } else {
        subtabSelector = `gmb-subtab-${activeTabParam}`;
      }
    }
    const subtabBtn = document.querySelector(
      `.gmb-sidebar-nav-item[data-subtab="${subtabSelector}"]`,
    );
    if (subtabBtn) {
      subtabBtn.click();
    }
  } else if (pageParam === "gmb-ranker-sitemaps") {
    let subtabSelector = "gmb-subtab-sitemap-general";
    if (activeTabParam) {
      if (activeTabParam === "settings" || activeTabParam === "general") {
        subtabSelector = "gmb-subtab-sitemap-general";
      } else {
        subtabSelector = `gmb-subtab-sitemap-${activeTabParam}`;
      }
    }
    const subtabBtn = document.querySelector(
      `.gmb-sidebar-nav-item[data-subtab="${subtabSelector}"]`,
    );
    if (subtabBtn) {
      subtabBtn.click();
    }
  } else if (pageParam === "gmb-ranker-schema") {
    let subtabSelector = "gmb-subtab-schema-general";
    if (activeTabParam) {
      if (activeTabParam === "settings" || activeTabParam === "general") {
        subtabSelector = "gmb-subtab-schema-general";
      } else {
        subtabSelector = `gmb-subtab-schema-${activeTabParam}`;
      }
    }
    const subtabBtn = document.querySelector(
      `.gmb-sidebar-nav-item[data-subtab="${subtabSelector}"]`,
    );
    if (subtabBtn) {
      subtabBtn.click();
    }
  }

  // Schema Publisher Logo Media Uploader
  const schemaLogoBtn = document.getElementById("gmb_schema_upload_logo_btn");
  const schemaLogoInput = document.getElementById("gmb_schema_logo_input");
  const schemaLogoPreview = document.getElementById("gmb_schema_logo_preview");
  const schemaLogoPreviewWrap = document.getElementById(
    "gmb_schema_logo_preview_wrap",
  );

  if (schemaLogoBtn && schemaLogoInput) {
    schemaLogoBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (typeof wp !== "undefined" && wp.media) {
        const mediaUploader = wp.media({
          title: "Select Publisher Logo",
          button: { text: "Use this Logo" },
          multiple: false,
        });
        mediaUploader.on("select", function () {
          const attachment = mediaUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          schemaLogoInput.value = attachment.url;
          if (schemaLogoPreview) {
            schemaLogoPreview.src = attachment.url;
          }
          if (schemaLogoPreviewWrap) {
            schemaLogoPreviewWrap.style.display = "block";
          }
        });
        mediaUploader.open();
      }
    });
  }

  // Default Fallback Schema Image Media Uploader
  const schemaDefImgBtn = document.getElementById(
    "gmb_schema_upload_default_img_btn",
  );
  const schemaDefImgInput = document.getElementById(
    "gmb_schema_default_img_input",
  );
  const schemaDefImgPreview = document.getElementById(
    "gmb_schema_default_img_preview",
  );
  const schemaDefImgPreviewWrap = document.getElementById(
    "gmb_schema_default_img_preview_wrap",
  );

  if (schemaDefImgBtn && schemaDefImgInput) {
    schemaDefImgBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (typeof wp !== "undefined" && wp.media) {
        const mediaUploader = wp.media({
          title: "Select Default Schema Image",
          button: { text: "Use this Image" },
          multiple: false,
        });
        mediaUploader.on("select", function () {
          const attachment = mediaUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          schemaDefImgInput.value = attachment.url;
          if (schemaDefImgPreview) {
            schemaDefImgPreview.src = attachment.url;
          }
          if (schemaDefImgPreviewWrap) {
            schemaDefImgPreviewWrap.style.display = "block";
          }
        });
        mediaUploader.open();
      }
    });
  }

  // Schema Live Validator Buttons
  const testGoogleBtn = document.getElementById("gmb_btn_test_google");
  const testSchemaOrgBtn = document.getElementById("gmb_btn_test_schema_org");
  const testUrlInput = document.getElementById("gmb_test_schema_url");

  if (testGoogleBtn && testUrlInput) {
    testGoogleBtn.addEventListener("click", function () {
      const targetUrl = encodeURIComponent(
        testUrlInput.value.trim() || window.location.origin,
      );
      window.open(
        `https://search.google.com/test/rich-results?url=${targetUrl}`,
        "_blank",
      );
    });
  }

  if (testSchemaOrgBtn && testUrlInput) {
    testSchemaOrgBtn.addEventListener("click", function () {
      const targetUrl = encodeURIComponent(
        testUrlInput.value.trim() || window.location.origin,
      );
      window.open(`https://validator.schema.org/#url=${targetUrl}`, "_blank");
    });
  }

  // ==========================================
  // SCHEMA GENERATOR & BUILDER JS ENGINE
  // ==========================================
  const schemaTplModal = document.getElementById("gmb-template-builder-modal");
  const catalogView = document.getElementById("gmb-modal-view-catalog");
  const builderView = document.getElementById("gmb-modal-view-builder");

  // Modal Triggers
  const schemaTplOpenBtn = document.getElementById(
    "gmb-open-new-template-modal-btn",
  );
  const schemaTplEmptyBtn = document.getElementById(
    "gmb-empty-create-template-btn",
  );
  const schemaTplCancelBtn = document.getElementById(
    "gmb-cancel-template-modal-btn",
  );
  const schemaTplSaveBtn = document.getElementById(
    "gmb-save-template-modal-btn",
  );
  const builderBackBtn = document.getElementById("gmb-builder-back-btn");

  // Hidden & Identification Inputs
  const tplIdInput = document.getElementById("gmb-modal-tpl-id");
  const tplTitleInput = document.getElementById("gmb-modal-tpl-title");
  const tplTypeSelect = document.getElementById("gmb-modal-tpl-type");
  const tplStatusSelect = document.getElementById("gmb-modal-tpl-status");
  const tplStatusToggle = document.getElementById("gmb-modal-status-toggle");
  const tplStatusLabel = document.getElementById("gmb-modal-status-label");
  const tplActiveBadge = document.getElementById(
    "gmb-builder-active-type-badge",
  );
  const tplVisTypeVal = document.getElementById("gmb-vis-type-val");

  // Code & Conditions Elements
  const tplJsonArea = document.getElementById("gmb-modal-tpl-json");
  const tplSyntaxIndicator = document.getElementById(
    "gmb-json-syntax-indicator",
  );
  const tplConditionsContainer = document.getElementById(
    "gmb-modal-conditions-container",
  );
  const tplAddCondBtn = document.getElementById("gmb-add-condition-row-btn");
  const tplLoadPresetBtn = document.getElementById("gmb-tpl-load-preset-btn");
  const tplFormatJsonBtn = document.getElementById("gmb-tpl-format-json-btn");
  const tplCopyJsonBtn = document.getElementById("gmb-tpl-copy-json-btn");

  // Catalog Controls
  const catalogSearchInput = document.getElementById(
    "gmb-catalog-search-input",
  );
  const catalogCardsGrid = document.getElementById("gmb-catalog-cards-grid");
  const savedTemplatesGrid = document.getElementById(
    "gmb-saved-templates-grid",
  );
  const catalogNoResults = document.getElementById("gmb-catalog-no-results");
  const catalogRadioCatalog = document.getElementById(
    "gmb-catalog-radio-catalog",
  );
  const catalogRadioSaved = document.getElementById("gmb-catalog-radio-saved");
  const catalogTabTemplates = document.getElementById("gmb-cat-tab-templates");
  const catalogTabImport = document.getElementById("gmb-cat-tab-import");
  const catalogTabCustom = document.getElementById("gmb-cat-tab-custom");

  // Builder Sub-Tabs
  const builderTabEdit = document.getElementById("gmb-builder-tab-btn-edit");
  const builderTabCode = document.getElementById("gmb-builder-tab-btn-code");
  const builderTabConditions = document.getElementById(
    "gmb-builder-tab-btn-conditions",
  );

  const builderPanelEdit = document.getElementById("gmb-builder-panel-edit");
  const builderPanelCode = document.getElementById("gmb-builder-panel-code");
  const builderPanelConditions = document.getElementById(
    "gmb-builder-panel-conditions",
  );

  // Visual Builder Elements
  const visualContent = document.getElementById("gmb-visual-fields-content");
  const btnAddProperty = document.getElementById("gmb-btn-add-property");
  const btnAddPropertyGroup = document.getElementById(
    "gmb-btn-add-property-group",
  );

  // Complete 20-Type Schema.org Blueprints
  const gmb_schema_blueprints = {
    Review: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Review",
        itemReviewed: {
          "@type": "Product",
          name: "%title%",
          image: "%featured_image%",
          description: "%excerpt%",
          offers: {
            "@type": "Offer",
            priceCurrency: "USD",
            price: "99.00",
            availability: "https://schema.org/InStock",
            url: "%url%",
          },
        },
        reviewRating: {
          "@type": "Rating",
          ratingValue: "5",
          bestRating: "5",
          worstRating: "1",
        },
        author: {
          "@type": "Person",
          name: "%author%",
        },
        reviewBody: "%excerpt%",
      },
      null,
      2,
    ),
    AggregateRating: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "AggregateRating",
        itemReviewed: {
          "@type": "Product",
          name: "%title%",
          image: "%featured_image%",
          description: "%excerpt%",
          offers: {
            "@type": "Offer",
            priceCurrency: "USD",
            price: "99.00",
            availability: "https://schema.org/InStock",
            url: "%url%",
          },
        },
        ratingValue: "4.9",
        bestRating: "5",
        worstRating: "1",
        ratingCount: "128",
        reviewCount: "94",
      },
      null,
      2,
    ),
    Organization: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Organization",
        name: "%sitename%",
        url: "%siteurl%",
        logo: "%featured_image%",
        sameAs: ["%siteurl%"],
      },
      null,
      2,
    ),
    WebPage: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "WebPage",
        name: "%title%",
        description: "%excerpt%",
        url: "%url%",
      },
      null,
      2,
    ),
    BreadcrumbList: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        itemListElement: [
          {
            "@type": "ListItem",
            position: 1,
            name: "Home",
            item: "%siteurl%",
          },
          {
            "@type": "ListItem",
            position: 2,
            name: "%title%",
            item: "%url%",
          },
        ],
      },
      null,
      2,
    ),
    MedicalClinic: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "MedicalClinic",
        name: "%sitename%",
        url: "%siteurl%",
        telephone: "%phone%",
        address: {
          "@type": "PostalAddress",
          streetAddress: "%street%",
          addressLocality: "%locality%",
        },
      },
      null,
      2,
    ),
    Article: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Article",
        headline: "%title%",
        image: "%featured_image%",
        datePublished: "%date%",
        author: { "@type": "Person", name: "%author%" },
        publisher: { "@type": "Organization", name: "%sitename%" },
      },
      null,
      2,
    ),
    Book: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Book",
        name: "%title%",
        author: { "@type": "Person", name: "%author%" },
        isbn: "978-0-123456-78-9",
        url: "%url%",
      },
      null,
      2,
    ),
    Carousel: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "ItemList",
        itemListElement: [
          { "@type": "ListItem", position: 1, url: "%url%", name: "%title%" },
        ],
      },
      null,
      2,
    ),
    Course: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Course",
        name: "%title%",
        description: "%excerpt%",
        provider: {
          "@type": "Organization",
          name: "%sitename%",
          sameAs: "%siteurl%",
        },
      },
      null,
      2,
    ),
    Dataset: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Dataset",
        name: "%title%",
        description: "%excerpt%",
        license: "https://creativecommons.org/licenses/by/4.0/",
        creator: { "@type": "Organization", name: "%sitename%" },
      },
      null,
      2,
    ),
    Event: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Event",
        name: "%title%",
        description: "%excerpt%",
        startDate: "%date%",
        location: {
          "@type": "Place",
          name: "%sitename%",
          address: "%street%, %locality%",
        },
      },
      null,
      2,
    ),
    FAQPage: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        mainEntity: [
          {
            "@type": "Question",
            name: "What services do you provide?",
            acceptedAnswer: { "@type": "Answer", text: "%excerpt%" },
          },
          {
            "@type": "Question",
            name: "How can I book an appointment?",
            acceptedAnswer: {
              "@type": "Answer",
              text: "Contact us at %phone% or visit our website at %siteurl%.",
            },
          },
        ],
      },
      null,
      2,
    ),
    FactCheck: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "ClaimReview",
        claimReviewed: "%title%",
        reviewRating: {
          "@type": "Rating",
          ratingValue: "5",
          bestRating: "5",
          worstRating: "1",
          alternateName: "True",
        },
        author: {
          "@type": "Organization",
          name: "%sitename%",
          url: "%siteurl%",
        },
      },
      null,
      2,
    ),
    HowTo: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "HowTo",
        name: "%title%",
        description: "%excerpt%",
        step: [
          {
            "@type": "HowToStep",
            name: "Initial Assessment",
            text: "Schedule an initial evaluation and consultation.",
          },
          {
            "@type": "HowToStep",
            name: "Treatment or Delivery",
            text: "Receive specialized care or services tailored to your needs.",
          },
        ],
      },
      null,
      2,
    ),
    JobPosting: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "JobPosting",
        title: "%title%",
        description: "%excerpt%",
        hiringOrganization: { "@type": "Organization", name: "%sitename%" },
      },
      null,
      2,
    ),
    LocalBusiness: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        name: "%sitename%",
        url: "%siteurl%",
        telephone: "%phone%",
        email: "%email%",
        address: {
          "@type": "PostalAddress",
          streetAddress: "%street%",
          addressLocality: "%locality%",
        },
      },
      null,
      2,
    ),
    Movie: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Movie",
        name: "%title%",
        image: "%featured_image%",
        director: { "@type": "Person", name: "%author%" },
        dateCreated: "%date%",
      },
      null,
      2,
    ),
    Music: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "MusicRecording",
        name: "%title%",
        byArtist: { "@type": "MusicGroup", name: "%author%" },
        url: "%url%",
      },
      null,
      2,
    ),
    Person: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Person",
        name: "%author%",
        url: "%url%",
        jobTitle: "Specialist",
        worksFor: { "@type": "Organization", name: "%sitename%" },
      },
      null,
      2,
    ),
    Product: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Product",
        name: "%title%",
        image: "%featured_image%",
        description: "%excerpt%",
        offers: {
          "@type": "Offer",
          priceCurrency: "USD",
          price: "99.00",
          availability: "https://schema.org/InStock",
          url: "%url%",
        },
      },
      null,
      2,
    ),
    Recipe: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Recipe",
        name: "%title%",
        image: "%featured_image%",
        author: { "@type": "Person", name: "%author%" },
        description: "%excerpt%",
      },
      null,
      2,
    ),
    Restaurant: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Restaurant",
        name: "%sitename%",
        image: "%featured_image%",
        telephone: "%phone%",
        servesCuisine: "International",
        address: {
          "@type": "PostalAddress",
          streetAddress: "%street%",
          addressLocality: "%locality%",
        },
      },
      null,
      2,
    ),
    Service: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Service",
        name: "%title%",
        description: "%excerpt%",
        url: "%url%",
        provider: {
          "@type": "Organization",
          name: "%sitename%",
          url: "%siteurl%",
          telephone: "%phone%",
        },
        areaServed: "%locality%",
      },
      null,
      2,
    ),
    SoftwareApplication: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        name: "%title%",
        operatingSystem: "All",
        applicationCategory: "BusinessApplication",
      },
      null,
      2,
    ),
    Video: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "VideoObject",
        name: "%title%",
        description: "%excerpt%",
        thumbnailUrl: "%featured_image%",
        uploadDate: "%date%",
      },
      null,
      2,
    ),
    Custom: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Thing",
        name: "%title%",
        url: "%url%",
      },
      null,
      2,
    ),
  };

  // Custom Schema Blueprints Starter Presets
  const gmb_custom_schema_presets = {
    blank: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Custom",
        name: "%title%",
        description: "%excerpt%",
        url: "%url%",
      },
      null,
      2,
    ),
    organization: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Organization",
        name: "%sitename%",
        url: "%siteurl%",
        logo: "%siteurl%favicon.ico",
        telephone: "%phone%",
        email: "%email%",
        address: {
          "@type": "PostalAddress",
          streetAddress: "%street%",
          addressLocality: "%locality%",
        },
      },
      null,
      2,
    ),
    healthcare: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "MedicalClinic",
        name: "%sitename%",
        description: "%excerpt%",
        url: "%url%",
        telephone: "%phone%",
        medicalSpecialty: "ElderlyCare",
        availableService: "%title%",
        address: {
          "@type": "PostalAddress",
          streetAddress: "%street%",
          addressLocality: "%locality%",
        },
      },
      null,
      2,
    ),
    service: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "Service",
        name: "%title%",
        serviceType: "Healthcare & Assistance",
        provider: {
          "@type": "Organization",
          name: "%sitename%",
          url: "%siteurl%",
        },
        areaServed: "%locality%",
        description: "%excerpt%",
      },
      null,
      2,
    ),
    creative: JSON.stringify(
      {
        "@context": "https://schema.org",
        "@type": "CreativeWork",
        name: "%title%",
        headline: "%title%",
        author: {
          "@type": "Person",
          name: "%author%",
        },
        publisher: {
          "@type": "Organization",
          name: "%sitename%",
        },
        datePublished: "%date%",
        description: "%excerpt%",
      },
      null,
      2,
    ),
  };

  // Catalog Panels & Tabs
  const catPanelTemplates = document.getElementById("gmb-cat-panel-templates");
  const catPanelImport = document.getElementById("gmb-cat-panel-import");
  const catPanelCustom = document.getElementById("gmb-cat-panel-custom");

  function switchCatalogTab(tabKey) {
    const tabs = [
      { key: "templates", btn: catalogTabTemplates, panel: catPanelTemplates },
      { key: "import", btn: catalogTabImport, panel: catPanelImport },
      { key: "custom", btn: catalogTabCustom, panel: catPanelCustom },
    ];

    tabs.forEach((t) => {
      if (!t.btn || !t.panel) return;
      if (t.key === tabKey) {
        t.btn.classList.add("active");
        t.panel.style.display = "flex";
      } else {
        t.btn.classList.remove("active");
        t.panel.style.display = "none";
      }
    });

    if (tabKey === "custom") {
      const previewArea = document.getElementById(
        "gmb-custom-preview-textarea",
      );
      const presetSelect = document.getElementById("gmb-custom-preset-select");
      if (
        previewArea &&
        (!previewArea.value.trim() || previewArea.value === "")
      ) {
        const pKey = presetSelect ? presetSelect.value : "blank";
        previewArea.value =
          gmb_custom_schema_presets[pKey] || gmb_custom_schema_presets["blank"];
      }
    }
  }

  if (catalogTabTemplates)
    catalogTabTemplates.addEventListener("click", () =>
      switchCatalogTab("templates"),
    );
  if (catalogTabImport)
    catalogTabImport.addEventListener("click", () =>
      switchCatalogTab("import"),
    );
  if (catalogTabCustom)
    catalogTabCustom.addEventListener("click", () =>
      switchCatalogTab("custom"),
    );

  // Import Panel Controls
  const importSourceSelect = document.getElementById(
    "gmb-catalog-import-source",
  );
  const importCodeLabel = document.getElementById(
    "gmb-catalog-import-code-label",
  );
  const importCodeWrap = document.getElementById(
    "gmb-catalog-import-code-wrap",
  );
  const importTextarea = document.getElementById("gmb-catalog-import-textarea");
  const importUrlWrap = document.getElementById("gmb-catalog-import-url-wrap");
  const importUrlInput = document.getElementById(
    "gmb-catalog-import-url-input",
  );
  const importError = document.getElementById("gmb-catalog-import-error");
  const importProcessBtn = document.getElementById(
    "gmb-catalog-import-process-btn",
  );

  if (importSourceSelect) {
    importSourceSelect.addEventListener("change", function () {
      if (importError) importError.style.display = "none";
      if (this.value === "jsonld") {
        if (importCodeWrap) importCodeWrap.style.display = "block";
        if (importCodeLabel)
          importCodeLabel.textContent = "Custom JSON-LD Code";
        if (importTextarea)
          importTextarea.placeholder =
            '{"@context": "https://schema.org", "@type": "MedicalClinic", ...}';
        if (importUrlWrap) importUrlWrap.style.display = "none";
      } else if (this.value === "html") {
        if (importCodeWrap) importCodeWrap.style.display = "block";
        if (importCodeLabel)
          importCodeLabel.textContent =
            "HTML / Webpage Source Code (containing JSON-LD)";
        if (importTextarea)
          importTextarea.placeholder =
            '<html><head><script type="application/ld+json">{"@context":"https://schema.org", "@type":"FAQPage", ...}<' +
            "/script></head>...";
        if (importUrlWrap) importUrlWrap.style.display = "none";
      } else if (this.value === "url") {
        if (importCodeWrap) importCodeWrap.style.display = "none";
        if (importUrlWrap) importUrlWrap.style.display = "block";
      }
    });
  }

  if (importProcessBtn) {
    importProcessBtn.addEventListener("click", function () {
      if (importError) importError.style.display = "none";
      const source = importSourceSelect ? importSourceSelect.value : "jsonld";
      let rawCode = "";

      if (source === "url") {
        const urlVal = importUrlInput ? importUrlInput.value.trim() : "";
        if (!urlVal) {
          if (importError) {
            importError.textContent =
              "Please enter a valid webpage URL to extract Schema.";
            importError.style.display = "block";
          }
          return;
        }
        if (importError) {
          importError.textContent =
            "To import schema directly, please view page source in your browser and paste the JSON-LD or HTML code into the editor above.";
          importError.style.display = "block";
        }
        return;
      }

      rawCode = importTextarea ? importTextarea.value.trim() : "";
      if (!rawCode) {
        if (importError) {
          importError.textContent =
            "Please paste your Schema JSON-LD or HTML source code.";
          importError.style.display = "block";
        }
        return;
      }

      let jsonStr = rawCode;
      if (source === "html" || rawCode.includes("<" + "script")) {
        const scriptMatch = rawCode.match(
          new RegExp(
            "<" +
              "script[^>]*type=[\"']application\\/ld\\+json[\"'][^>]*>([\\s\\S]*?)<" +
              "\\/script>",
            "i",
          ),
        );
        if (scriptMatch && scriptMatch[1]) {
          jsonStr = scriptMatch[1].trim();
        } else {
          jsonStr = rawCode
            .replace(new RegExp("<" + "script[^>]*>", "gi"), "")
            .replace(new RegExp("<" + "\\/script>", "gi"), "")
            .trim();
        }
      }

      try {
        const parsed = JSON.parse(jsonStr);
        let schemaObj = parsed;
        if (
          parsed["@graph"] &&
          Array.isArray(parsed["@graph"]) &&
          parsed["@graph"].length > 0
        ) {
          schemaObj = parsed["@graph"][0];
        } else if (Array.isArray(parsed) && parsed.length > 0) {
          schemaObj = parsed[0];
        }

        const detectedType = schemaObj["@type"] || "Custom";
        const detectedName =
          schemaObj.name || schemaObj.headline || detectedType + " Schema";
        const title = detectedName + " (Imported)";
        const formattedJson = JSON.stringify(schemaObj, null, 2);

        openBuilderModal(detectedType, false, null, title, formattedJson);
      } catch (err) {
        if (importError) {
          importError.textContent =
            "Could not parse JSON-LD: " +
            err.message +
            ". Please ensure valid JSON format.";
          importError.style.display = "block";
        }
      }
    });
  }

  // Custom Schema Blueprint Panel Controls
  const customTypeInput = document.getElementById("gmb-custom-type-input");
  const customTitleInput = document.getElementById("gmb-custom-title-input");
  const customPresetSelect = document.getElementById(
    "gmb-custom-preset-select",
  );
  const customPreviewTextarea = document.getElementById(
    "gmb-custom-preview-textarea",
  );
  const customCreateBtn = document.getElementById("gmb-custom-create-btn");

  if (customPresetSelect && customPreviewTextarea) {
    customPresetSelect.addEventListener("change", function () {
      const presetKey = this.value;
      const starter =
        gmb_custom_schema_presets[presetKey] ||
        gmb_custom_schema_presets["blank"];
      customPreviewTextarea.value = starter;
      try {
        const parsed = JSON.parse(starter);
        if (customTypeInput && parsed["@type"]) {
          customTypeInput.value = parsed["@type"];
        }
        if (customTitleInput) {
          customTitleInput.value =
            (parsed["@type"] || "Custom") + " Schema Blueprint";
        }
      } catch (e) {}
    });
  }

  if (customTypeInput && customPreviewTextarea) {
    customTypeInput.addEventListener("input", function () {
      const newType = this.value.trim() || "Custom";
      try {
        const parsed = JSON.parse(customPreviewTextarea.value.trim());
        parsed["@type"] = newType;
        customPreviewTextarea.value = JSON.stringify(parsed, null, 2);
      } catch (e) {}
    });
  }

  if (customCreateBtn) {
    customCreateBtn.addEventListener("click", function () {
      const customType =
        (customTypeInput ? customTypeInput.value.trim() : "") || "Custom";
      const customTitle =
        (customTitleInput ? customTitleInput.value.trim() : "") ||
        customType + " Schema Blueprint";
      let customJson = customPreviewTextarea
        ? customPreviewTextarea.value.trim()
        : "";

      if (!customJson) {
        customJson = JSON.stringify(
          {
            "@context": "https://schema.org",
            "@type": customType,
            name: "%title%",
            description: "%excerpt%",
            url: "%url%",
          },
          null,
          2,
        );
      } else {
        try {
          const parsed = JSON.parse(customJson);
          parsed["@type"] = customType;
          customJson = JSON.stringify(parsed, null, 2);
        } catch (e) {}
      }

      openBuilderModal(customType, false, null, customTitle, customJson);
    });
  }

  // Builder Tab Switching
  function switchBuilderTab(tabKey) {
    const tabs = [
      { key: "edit", btn: builderTabEdit, panel: builderPanelEdit },
      { key: "code", btn: builderTabCode, panel: builderPanelCode },
      {
        key: "conditions",
        btn: builderTabConditions,
        panel: builderPanelConditions,
      },
    ];

    tabs.forEach((t) => {
      if (!t.btn || !t.panel) return;
      if (t.key === tabKey) {
        t.btn.classList.add("active");
        t.panel.classList.add("active");
        t.panel.style.setProperty("display", "flex", "important");
      } else {
        t.btn.classList.remove("active");
        t.panel.classList.remove("active");
        t.panel.style.setProperty("display", "none", "important");
      }
    });

    if (tabKey === "edit") {
      syncJsonToVisualBuilder();
    } else if (tabKey === "code") {
      validateSchemaTplJson();
    }
  }

  if (builderTabEdit)
    builderTabEdit.addEventListener("click", () => switchBuilderTab("edit"));
  if (builderTabCode)
    builderTabCode.addEventListener("click", () => switchBuilderTab("code"));
  if (builderTabConditions)
    builderTabConditions.addEventListener("click", () =>
      switchBuilderTab("conditions"),
    );

  // Back to Catalog Trigger
  if (builderBackBtn) {
    builderBackBtn.addEventListener("click", function () {
      if (builderView) builderView.style.display = "none";
      if (catalogView) catalogView.style.display = "flex";
    });
  }

  // Modal Close Triggers
  document.querySelectorAll(".gmb-modal-close-trigger").forEach((btn) => {
    btn.addEventListener("click", closeSchemaModal);
  });
  if (schemaTplCancelBtn)
    schemaTplCancelBtn.addEventListener("click", closeSchemaModal);

  function closeSchemaModal() {
    if (schemaTplModal) schemaTplModal.style.display = "none";
  }

  // Open Catalog View
  function openCatalogModal() {
    if (!schemaTplModal) return;
    schemaTplModal.style.display = "flex";
    if (catalogView) catalogView.style.display = "flex";
    if (builderView) builderView.style.display = "none";

    switchCatalogTab("templates");

    if (catalogSearchInput) {
      catalogSearchInput.value = "";
    }
    document
      .querySelectorAll(".gmb-schema-template-card, .gmb-schema-card")
      .forEach((c) => (c.style.display = "flex"));
    if (catalogNoResults) catalogNoResults.style.display = "none";
    if (catalogRadioCatalog) catalogRadioCatalog.checked = true;
    if (catalogCardsGrid) catalogCardsGrid.style.display = "grid";
    if (savedTemplatesGrid) savedTemplatesGrid.style.display = "none";
  }

  if (schemaTplOpenBtn)
    schemaTplOpenBtn.addEventListener("click", openCatalogModal);
  if (schemaTplEmptyBtn)
    schemaTplEmptyBtn.addEventListener("click", openCatalogModal);

  // Open Builder View
  function openBuilderModal(type, isEdit, data, customTitle, customJson) {
    if (!schemaTplModal) return;
    schemaTplModal.style.display = "flex";
    if (catalogView) catalogView.style.display = "none";
    if (builderView) builderView.style.display = "flex";

    if (tplConditionsContainer) tplConditionsContainer.innerHTML = "";
    switchBuilderTab("edit");

    if (tplVisTypeVal) {
      tplVisTypeVal.readOnly = false;
      tplVisTypeVal.style.background = "#ffffff";
      tplVisTypeVal.style.cursor = "text";
    }

    if (customJson) {
      document.getElementById("gmb-builder-modal-title").textContent =
        "Schema Builder";
      tplIdInput.value = "";
      tplTitleInput.value = customTitle || type + " Schema Blueprint";
      tplTypeSelect.value = type;
      if (tplVisTypeVal) tplVisTypeVal.value = type;
      if (tplActiveBadge) tplActiveBadge.textContent = type;

      tplStatusSelect.value = "active";
      if (tplStatusToggle) tplStatusToggle.checked = true;
      if (tplStatusLabel) {
        tplStatusLabel.textContent = "Active Template";
        tplStatusLabel.style.color = "#0f172a";
      }

      tplJsonArea.value = customJson;
      renderConditionRow({ type: "include", target: "entire_site", value: "" });
    } else if (isEdit && data) {
      document.getElementById("gmb-builder-modal-title").textContent =
        "Edit Schema: " + (data.title || "Untitled");
      tplIdInput.value = data.id || "";
      tplTitleInput.value = data.title || "";
      tplTypeSelect.value = data.type || "Custom";
      if (tplVisTypeVal) tplVisTypeVal.value = data.type || "Custom";
      if (tplActiveBadge) tplActiveBadge.textContent = data.type || "Custom";

      const isActive = data.status === "active";
      tplStatusSelect.value = isActive ? "active" : "inactive";
      if (tplStatusToggle) tplStatusToggle.checked = isActive;
      if (tplStatusLabel) {
        tplStatusLabel.textContent = isActive
          ? "Active Template"
          : "Inactive Template";
        tplStatusLabel.style.color = isActive ? "#0f172a" : "#94a3b8";
      }

      tplJsonArea.value = data.schema_json || "";
      if (data.conditions && data.conditions.length) {
        data.conditions.forEach((c) => renderConditionRow(c));
      } else {
        renderConditionRow();
      }
    } else {
      const displayType = type === "FAQPage" ? "FAQ" : type;
      document.getElementById("gmb-builder-modal-title").textContent =
        "Schema Builder";
      tplIdInput.value = "";
      tplTitleInput.value = displayType + " Schema Blueprint";
      tplTypeSelect.value = type;
      if (tplVisTypeVal) tplVisTypeVal.value = type;
      if (tplActiveBadge) tplActiveBadge.textContent = type;

      tplStatusSelect.value = "active";
      if (tplStatusToggle) tplStatusToggle.checked = true;
      if (tplStatusLabel) {
        tplStatusLabel.textContent = "Active Template";
        tplStatusLabel.style.color = "#0f172a";
      }

      tplJsonArea.value =
        gmb_schema_blueprints[type] || gmb_schema_blueprints["Custom"];
      renderConditionRow({ type: "include", target: "entire_site", value: "" });
    }

    validateSchemaTplJson();
    syncJsonToVisualBuilder();
  }

  if (tplVisTypeVal) {
    tplVisTypeVal.addEventListener("input", function () {
      const newType = this.value.trim();
      if (tplTypeSelect) tplTypeSelect.value = newType;
      if (tplActiveBadge) tplActiveBadge.textContent = newType;
      try {
        const parsed = JSON.parse(tplJsonArea.value.trim());
        parsed["@type"] = newType;
        tplJsonArea.value = JSON.stringify(parsed, null, 2);
        validateSchemaTplJson();
      } catch (e) {}
    });
  }

  // Catalog Card "+ Use" Click Handler
  document.querySelectorAll(".gmb-use-schema-btn").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const type = this.getAttribute("data-type") || "Article";
      openBuilderModal(type, false);
    });
  });

  // Clicking anywhere on the template card also opens the builder
  document
    .querySelectorAll(".gmb-schema-template-card, .gmb-schema-card")
    .forEach((card) => {
      card.addEventListener("click", function (e) {
        if (e.target.closest(".gmb-use-schema-btn")) return;
        const type = this.getAttribute("data-type") || "Article";
        openBuilderModal(type, false);
      });
    });

  // Instant Search Filter for Catalog Cards
  if (catalogSearchInput) {
    catalogSearchInput.addEventListener("input", function () {
      const query = this.value.toLowerCase().trim();
      const cards = document.querySelectorAll(
        ".gmb-schema-template-card, .gmb-schema-card",
      );
      let matchCount = 0;
      cards.forEach((c) => {
        const title = (c.getAttribute("data-title") || "").toLowerCase();
        const type = (c.getAttribute("data-type") || "").toLowerCase();
        if (!query || title.includes(query) || type.includes(query)) {
          c.style.display = "flex";
          matchCount++;
        } else {
          c.style.display = "none";
        }
      });
      if (catalogNoResults) {
        catalogNoResults.style.display = matchCount === 0 ? "block" : "none";
      }
    });
  }

  // Radio Toggle: Schema Catalog vs Your Templates
  if (catalogRadioCatalog && catalogRadioSaved) {
    catalogRadioCatalog.addEventListener("change", function () {
      if (this.checked) {
        if (catalogCardsGrid) catalogCardsGrid.style.display = "grid";
        if (savedTemplatesGrid) savedTemplatesGrid.style.display = "none";
        if (catalogNoResults) catalogNoResults.style.display = "none";
      }
    });
    catalogRadioSaved.addEventListener("change", function () {
      if (this.checked) {
        if (catalogCardsGrid) catalogCardsGrid.style.display = "none";
        renderSavedTemplatesInCatalog();
        if (savedTemplatesGrid) savedTemplatesGrid.style.display = "grid";
      }
    });
  }

  function renderSavedTemplatesInCatalog() {
    if (!savedTemplatesGrid) return;
    savedTemplatesGrid.innerHTML = "";
    const rows = document.querySelectorAll(
      '#gmb-schema-templates-table tbody tr[id^="gmb-tpl-row-"]',
    );
    if (!rows.length) {
      savedTemplatesGrid.innerHTML =
        '<div class="gmb-cat-empty-state gmb-col-span-2">No saved templates yet. Click on any card in the Schema Catalog to create one.</div>';
      return;
    }
    rows.forEach((r) => {
      const id = r.id.replace("gmb-tpl-row-", "");
      const titleEl = r.querySelector("strong");
      const title = titleEl ? titleEl.textContent.trim() : "Template #" + id;
      const badgeEl = r.querySelector(".gmb-tag");
      const type = badgeEl ? badgeEl.textContent.trim() : "Custom";

      const card = document.createElement("div");
      card.className = "gmb-saved-template-card";
      card.innerHTML = `
                            <div class="gmb-flex-center-gap-sm">
                                <span class="gmb-tag gmb-tag--blue">${type}</span>
                                <span class="gmb-cat-card-title">${title}</span>
                            </div>
                            <button type="button" class="button button-small gmb-btn-action-edit">Edit</button>
                        `;
      card.querySelector("button").addEventListener("click", () => {
        const editBtn = r.querySelector(".gmb-edit-template-btn");
        if (editBtn) editBtn.click();
      });
      savedTemplatesGrid.appendChild(card);
    });
  }

  // Status Toggle logic
  if (tplStatusToggle && tplStatusSelect && tplStatusLabel) {
    tplStatusToggle.addEventListener("change", function () {
      if (this.checked) {
        tplStatusSelect.value = "active";
        tplStatusLabel.textContent = "Active Template";
        tplStatusLabel.className = "gmb-status-label--active";
      } else {
        tplStatusSelect.value = "inactive";
        tplStatusLabel.textContent = "Inactive Template";
        tplStatusLabel.className = "gmb-status-label--inactive";
      }
    });
  }

  // Copy JSON button
  if (tplCopyJsonBtn && tplJsonArea) {
    tplCopyJsonBtn.addEventListener("click", function () {
      if (!tplJsonArea.value.trim()) return;
      navigator.clipboard
        .writeText(tplJsonArea.value)
        .then(() => {
          const original = tplCopyJsonBtn.textContent;
          tplCopyJsonBtn.textContent = "✓ Copied!";
          setTimeout(() => {
            tplCopyJsonBtn.textContent = original;
          }, 1800);
        })
        .catch(() => {
          tplJsonArea.select();
          document.execCommand("copy");
          alert("JSON copied to clipboard!");
        });
    });
  }

  function validateSchemaTplJson() {
    if (!tplJsonArea || !tplSyntaxIndicator) return true;
    const val = tplJsonArea.value.trim();
    if (!val) {
      tplSyntaxIndicator.textContent = "JSON is empty";
      tplSyntaxIndicator.className =
        "gmb-ide-badge gmb-syntax-indicator--empty";
      return false;
    }
    try {
      JSON.parse(val);
      tplSyntaxIndicator.textContent = "✓ Valid JSON-LD Syntax";
      tplSyntaxIndicator.className =
        "gmb-ide-badge gmb-syntax-indicator--valid";
      return true;
    } catch (err) {
      tplSyntaxIndicator.textContent = "✕ Syntax Error: " + err.message;
      tplSyntaxIndicator.className =
        "gmb-ide-badge gmb-syntax-indicator--invalid";
      return false;
    }
  }

  if (tplJsonArea) {
    tplJsonArea.addEventListener("input", validateSchemaTplJson);
  }

  if (tplFormatJsonBtn && tplJsonArea) {
    tplFormatJsonBtn.addEventListener("click", function () {
      try {
        const parsed = JSON.parse(tplJsonArea.value.trim());
        tplJsonArea.value = JSON.stringify(parsed, null, 2);
        validateSchemaTplJson();
      } catch (err) {
        alert("Cannot format invalid JSON: " + err.message);
      }
    });
  }

  if (tplLoadPresetBtn && tplJsonArea && tplTypeSelect) {
    tplLoadPresetBtn.addEventListener("click", function () {
      const type = tplTypeSelect.value;
      if (gmb_schema_blueprints[type]) {
        if (
          tplJsonArea.value.trim() &&
          !confirm(
            "Reset current structured data to default " + type + " blueprint?",
          )
        ) {
          return;
        }
        tplJsonArea.value = gmb_schema_blueprints[type];
        validateSchemaTplJson();
        syncJsonToVisualBuilder();
      }
    });
  }

  // ==========================================
  // VISUAL BUILDER SYNC ENGINE
  // ==========================================
  function syncJsonToVisualBuilder() {
    if (!visualContent || !tplTypeSelect) return;
    const type = tplTypeSelect.value;
    let parsedJson = null;

    try {
      parsedJson = JSON.parse(tplJsonArea.value.trim());
    } catch (e) {
      parsedJson = null;
    }

    visualContent.innerHTML = "";

    if (type === "FAQPage") {
      let mainEntity =
        parsedJson && Array.isArray(parsedJson.mainEntity)
          ? parsedJson.mainEntity
          : [];
      if (!mainEntity.length && parsedJson && parsedJson.mainEntity) {
        mainEntity = [parsedJson.mainEntity];
      }

      function renderFaqList() {
        visualContent.innerHTML = "";
        if (!mainEntity.length) {
          visualContent.innerHTML =
            '<div class="gmb-faq-empty-state">No FAQ items yet. Click "+ Add Property" below to add a question.</div>';
          return;
        }

        mainEntity.forEach((faq, index) => {
          const qName = faq.name || "";
          const aText =
            faq.acceptedAnswer && faq.acceptedAnswer.text
              ? faq.acceptedAnswer.text
              : "";

          const card = document.createElement("div");
          card.className = "gmb-faq-card";

          card.innerHTML = `
                                    <div class="gmb-faq-card-header">
                                        <span class="gmb-faq-card-title">Question #${index + 1}</span>
                                        <button type="button" class="gmb-del-faq-btn">Remove</button>
                                    </div>
                                    <input type="text" class="gmb-faq-q-inp gmb-input" placeholder="Question text..." value="${qName.replace(/"/g, "&quot;")}" />
                                    <textarea class="gmb-faq-a-inp gmb-input" rows="2" placeholder="Answer text...">${aText}</textarea>
                                `;

          card
            .querySelector(".gmb-del-faq-btn")
            .addEventListener("click", function () {
              mainEntity.splice(index, 1);
              updateFaqJson();
              renderFaqList();
            });

          const qInp = card.querySelector(".gmb-faq-q-inp");
          const aInp = card.querySelector(".gmb-faq-a-inp");

          function onFaqChange() {
            mainEntity[index] = {
              "@type": "Question",
              name: qInp.value,
              acceptedAnswer: {
                "@type": "Answer",
                text: aInp.value,
              },
            };
            updateFaqJson();
          }

          qInp.addEventListener("input", onFaqChange);
          aInp.addEventListener("input", onFaqChange);

          visualContent.appendChild(card);
        });
      }

      function updateFaqJson() {
        const newObj = {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          mainEntity: mainEntity,
        };
        tplJsonArea.value = JSON.stringify(newObj, null, 2);
        validateSchemaTplJson();
      }

      renderFaqList();
    } else if (type === "Service") {
      const sName = parsedJson && parsedJson.name ? parsedJson.name : "%title%";
      const sDesc =
        parsedJson && parsedJson.description
          ? parsedJson.description
          : "%excerpt%";
      const sProvider =
        parsedJson && parsedJson.provider && parsedJson.provider.name
          ? parsedJson.provider.name
          : "%sitename%";
      const sPhone =
        parsedJson && parsedJson.provider && parsedJson.provider.telephone
          ? parsedJson.provider.telephone
          : "%phone%";
      const sArea =
        parsedJson && parsedJson.areaServed
          ? parsedJson.areaServed
          : "%locality%";

      visualContent.innerHTML = `
                            <div class="gmb-grid-2">
                                <div>
                                    <label class="gmb-form-label">Service Name</label>
                                    <input type="text" id="gmb-vis-svc-name" value="${sName.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                                <div>
                                    <label class="gmb-form-label">Provider Name</label>
                                    <input type="text" id="gmb-vis-svc-provider" value="${sProvider.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                            </div>
                            <div class="gmb-grid-2">
                                <div>
                                    <label class="gmb-form-label">Provider Telephone</label>
                                    <input type="text" id="gmb-vis-svc-phone" value="${sPhone.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                                <div>
                                    <label class="gmb-form-label">Area Served</label>
                                    <input type="text" id="gmb-vis-svc-area" value="${sArea.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                            </div>
                            <div>
                                <label class="gmb-form-label">Description</label>
                                <textarea id="gmb-vis-svc-desc" rows="2" class="gmb-input">${sDesc}</textarea>
                            </div>
                        `;

      function updateServiceJson() {
        const newObj = {
          "@context": "https://schema.org",
          "@type": "Service",
          name: document.getElementById("gmb-vis-svc-name").value,
          description: document.getElementById("gmb-vis-svc-desc").value,
          url: "%url%",
          provider: {
            "@type": "Organization",
            name: document.getElementById("gmb-vis-svc-provider").value,
            url: "%siteurl%",
            telephone: document.getElementById("gmb-vis-svc-phone").value,
          },
          areaServed: document.getElementById("gmb-vis-svc-area").value,
        };
        tplJsonArea.value = JSON.stringify(newObj, null, 2);
        validateSchemaTplJson();
      }

      visualContent
        .querySelectorAll("input, textarea")
        .forEach((el) => el.addEventListener("input", updateServiceJson));
    } else if (type === "LocalBusiness" || type === "Restaurant") {
      const bName =
        parsedJson && parsedJson.name ? parsedJson.name : "%sitename%";
      const bPhone =
        parsedJson && parsedJson.telephone ? parsedJson.telephone : "%phone%";
      const bEmail =
        parsedJson && parsedJson.email ? parsedJson.email : "%email%";
      const bStreet =
        parsedJson && parsedJson.address && parsedJson.address.streetAddress
          ? parsedJson.address.streetAddress
          : "%street%";
      const bLocality =
        parsedJson && parsedJson.address && parsedJson.address.addressLocality
          ? parsedJson.address.addressLocality
          : "%locality%";

      visualContent.innerHTML = `
                            <div class="gmb-grid-2">
                                <div>
                                    <label class="gmb-form-label">Business Name</label>
                                    <input type="text" id="gmb-vis-biz-name" value="${bName.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                                <div>
                                    <label class="gmb-form-label">Phone Number</label>
                                    <input type="text" id="gmb-vis-biz-phone" value="${bPhone.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                            </div>
                            <div class="gmb-grid-2">
                                <div>
                                    <label class="gmb-form-label">Street Address</label>
                                    <input type="text" id="gmb-vis-biz-street" value="${bStreet.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                                <div>
                                    <label class="gmb-form-label">City / Locality</label>
                                    <input type="text" id="gmb-vis-biz-locality" value="${bLocality.replace(/"/g, "&quot;")}" class="gmb-input" />
                                </div>
                            </div>
                        `;

      function updateBizJson() {
        const newObj = {
          "@context": "https://schema.org",
          "@type": type,
          name: document.getElementById("gmb-vis-biz-name").value,
          url: "%siteurl%",
          telephone: document.getElementById("gmb-vis-biz-phone").value,
          email: bEmail,
          address: {
            "@type": "PostalAddress",
            streetAddress: document.getElementById("gmb-vis-biz-street").value,
            addressLocality: document.getElementById("gmb-vis-biz-locality")
              .value,
          },
        };
        tplJsonArea.value = JSON.stringify(newObj, null, 2);
        validateSchemaTplJson();
      }

      visualContent
        .querySelectorAll("input")
        .forEach((el) => el.addEventListener("input", updateBizJson));
    } else {
      // General Property List for Article, Dataset, Product, Course, Event, Custom, etc.
      if (parsedJson && typeof parsedJson === "object") {
        const entries = Object.entries(parsedJson).filter(
          ([k]) => k !== "@context" && k !== "@type",
        );
        if (entries.length) {
          entries.forEach(([k, v]) => {
            if (typeof v === "object" && v !== null && !Array.isArray(v)) {
              addVisualPropertyGroup(k, v);
            } else if (Array.isArray(v)) {
              if (v.length > 0 && typeof v[0] === "object" && v[0] !== null) {
                addVisualPropertyGroup(k, v[0]);
              } else {
                addVisualPropertyRow(k, v.join(", "));
              }
            } else {
              addVisualPropertyRow(k, v);
            }
          });
        } else {
          visualContent.innerHTML =
            '<div class="gmb-faq-empty-state">No properties added yet. Click "+ Add Property" or "+ Add Property Group" below.</div>';
        }
      }
    }
  }

  // Create Single Property Row Element
  function createPropRowElement(key = "", val = "") {
    const row = document.createElement("div");
    row.className = "gmb-custom-prop-row";
    row.innerHTML = `
                        <input type="text" class="gmb-prop-key gmb-input gmb-input--w-130" placeholder="property (e.g. name)" value="${String(key).replace(/"/g, "&quot;")}" />
                        <input type="text" class="gmb-prop-val gmb-input gmb-flex-1" placeholder="value or token (e.g. %title%)" value="${String(val).replace(/"/g, "&quot;")}" />
                        <button type="button" class="gmb-del-prop-btn" title="Remove property">✕</button>
                    `;

    row.querySelector(".gmb-del-prop-btn").addEventListener("click", () => {
      row.remove();
      syncCustomPropsToJson();
    });

    row
      .querySelectorAll("input")
      .forEach((inp) => inp.addEventListener("input", syncCustomPropsToJson));
    return row;
  }

  // Add Property Row Helper
  function addVisualPropertyRow(key = "", val = "") {
    if (!visualContent) return;
    const emptyState = visualContent.querySelector(".gmb-faq-empty-state");
    if (emptyState) emptyState.remove();

    const row = createPropRowElement(key, val);
    visualContent.appendChild(row);
  }

  // Add Property Group Helper (Nested Schema Object)
  function addVisualPropertyGroup(groupKey = "", groupObj = {}) {
    if (!visualContent) return;
    const emptyState = visualContent.querySelector(".gmb-faq-empty-state");
    if (emptyState) emptyState.remove();

    const groupType =
      groupObj && groupObj["@type"] ? groupObj["@type"] : "Thing";
    const card = document.createElement("div");
    card.className = "gmb-prop-group-card";
    card.innerHTML = `
                        <div class="gmb-prop-group-header">
                            <div class="gmb-prop-group-title-row">
                                <span class="gmb-prop-group-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                </span>
                                <input type="text" class="gmb-prop-group-key gmb-input" placeholder="group name (e.g. creator, author)" value="${String(groupKey).replace(/"/g, "&quot;")}" />
                                <span class="gmb-prop-group-type-label">@type:</span>
                                <input type="text" class="gmb-prop-group-type gmb-input" placeholder="Type (e.g. Person, Organization)" value="${String(groupType).replace(/"/g, "&quot;")}" />
                            </div>
                            <button type="button" class="gmb-del-group-btn" title="Remove Property Group">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                        <div class="gmb-prop-group-fields gmb-flex-col-gap-sm"></div>
                        <div class="gmb-prop-group-actions">
                            <button type="button" class="gmb-btn-add-group-prop gmb-builder-action-btn">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                Add Property to Group
                            </button>
                        </div>
                    `;

    const fieldsContainer = card.querySelector(".gmb-prop-group-fields");
    const groupKeyInp = card.querySelector(".gmb-prop-group-key");
    const groupTypeInp = card.querySelector(".gmb-prop-group-type");
    const delGroupBtn = card.querySelector(".gmb-del-group-btn");
    const addPropToGroupBtn = card.querySelector(".gmb-btn-add-group-prop");

    // Populate child properties
    if (groupObj && typeof groupObj === "object") {
      const childEntries = Object.entries(groupObj).filter(
        ([k]) => k !== "@type" && k !== "@context",
      );
      if (childEntries.length) {
        childEntries.forEach(([ck, cv]) => {
          const row = createPropRowElement(
            ck,
            typeof cv === "object" && cv !== null ? JSON.stringify(cv) : cv,
          );
          fieldsContainer.appendChild(row);
        });
      } else {
        const row = createPropRowElement("name", "%sitename%");
        fieldsContainer.appendChild(row);
      }
    } else {
      const row = createPropRowElement("name", "");
      fieldsContainer.appendChild(row);
    }

    delGroupBtn.addEventListener("click", () => {
      card.remove();
      syncCustomPropsToJson();
    });

    addPropToGroupBtn.addEventListener("click", () => {
      const row = createPropRowElement("", "");
      fieldsContainer.appendChild(row);
      syncCustomPropsToJson();
    });

    groupKeyInp.addEventListener("input", syncCustomPropsToJson);
    groupTypeInp.addEventListener("input", syncCustomPropsToJson);

    visualContent.appendChild(card);
  }

  function syncCustomPropsToJson() {
    let base = {};
    try {
      base = JSON.parse(tplJsonArea.value.trim());
    } catch (e) {
      base = { "@context": "https://schema.org", "@type": tplTypeSelect.value };
    }

    const newObj = {
      "@context": base["@context"] || "https://schema.org",
      "@type": tplTypeSelect.value || base["@type"] || "Thing",
    };

    // 1. Direct top-level property rows (direct children only)
    const directRows = visualContent.querySelectorAll(
      ":scope > .gmb-custom-prop-row",
    );
    directRows.forEach((r) => {
      const k = r.querySelector(".gmb-prop-key").value.trim();
      const v = r.querySelector(".gmb-prop-val").value.trim();
      if (k) newObj[k] = v;
    });

    // 2. Property Group cards
    const groupCards = visualContent.querySelectorAll(
      ":scope > .gmb-prop-group-card",
    );
    groupCards.forEach((card) => {
      const gKey = card.querySelector(".gmb-prop-group-key").value.trim();
      const gType = card.querySelector(".gmb-prop-group-type").value.trim();
      if (gKey) {
        const gObj = {};
        if (gType) gObj["@type"] = gType;
        card
          .querySelectorAll(".gmb-prop-group-fields .gmb-custom-prop-row")
          .forEach((cr) => {
            const ck = cr.querySelector(".gmb-prop-key").value.trim();
            const cv = cr.querySelector(".gmb-prop-val").value.trim();
            if (ck) gObj[ck] = cv;
          });
        newObj[gKey] = gObj;
      }
    });

    tplJsonArea.value = JSON.stringify(newObj, null, 2);
    validateSchemaTplJson();
  }

  if (btnAddProperty) {
    btnAddProperty.addEventListener("click", function () {
      const type = tplTypeSelect.value;
      if (type === "FAQPage") {
        let parsed = {};
        try {
          parsed = JSON.parse(tplJsonArea.value.trim());
        } catch (e) {}
        if (!Array.isArray(parsed.mainEntity)) parsed.mainEntity = [];
        parsed.mainEntity.push({
          "@type": "Question",
          name: "New Question",
          acceptedAnswer: { "@type": "Answer", text: "Answer text here." },
        });
        tplJsonArea.value = JSON.stringify(parsed, null, 2);
        validateSchemaTplJson();
        syncJsonToVisualBuilder();
      } else {
        addVisualPropertyRow("", "");
      }
    });
  }

  if (btnAddPropertyGroup) {
    btnAddPropertyGroup.addEventListener("click", function () {
      const type = tplTypeSelect.value;
      let defaultGroupKey = "author";
      let defaultGroupType = "Person";

      if (type === "Dataset") {
        defaultGroupKey = "creator";
        defaultGroupType = "Organization";
      } else if (type === "Service" || type === "Course") {
        defaultGroupKey = "provider";
        defaultGroupType = "Organization";
      } else if (type === "Product") {
        defaultGroupKey = "offers";
        defaultGroupType = "Offer";
      } else if (type === "Event") {
        defaultGroupKey = "location";
        defaultGroupType = "Place";
      }

      addVisualPropertyGroup(defaultGroupKey, {
        "@type": defaultGroupType,
        name: "%sitename%",
      });
      syncCustomPropsToJson();
    });
  }

  // Tag insert pills
  document.querySelectorAll(".gmb-tag-insert-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      if (!tplJsonArea) return;
      const tag = this.getAttribute("data-tag");
      const start = tplJsonArea.selectionStart || 0;
      const end = tplJsonArea.selectionEnd || 0;
      const text = tplJsonArea.value;
      tplJsonArea.value = text.substring(0, start) + tag + text.substring(end);
      tplJsonArea.focus();
      tplJsonArea.setSelectionRange(start + tag.length, start + tag.length);
      validateSchemaTplJson();
      syncJsonToVisualBuilder();
    });
  });

  // Display Conditions Repeater
  function renderConditionRow(cond) {
    if (!tplConditionsContainer) return;
    cond = cond || { type: "include", target: "entire_site", value: "" };

    const row = document.createElement("div");
    row.className = "gmb-condition-rule-row";

    const typeSelect = document.createElement("select");
    typeSelect.className = "gmb-cond-type gmb-select";
    typeSelect.innerHTML =
      '<option value="include"' +
      (cond.type === "include" ? " selected" : "") +
      '>Include</option><option value="exclude"' +
      (cond.type === "exclude" ? " selected" : "") +
      ">Exclude</option>";

    function updateTypeSelectStyle() {
      typeSelect.className =
        "gmb-cond-type gmb-select gmb-cond-type--" + typeSelect.value;
    }
    typeSelect.addEventListener("change", updateTypeSelectStyle);
    updateTypeSelectStyle();

    const targetSelect = document.createElement("select");
    targetSelect.className = "gmb-cond-target gmb-select";
    targetSelect.innerHTML = `
                        <option value="entire_site"${cond.target === "entire_site" ? " selected" : ""}>Entire Website</option>
                        <option value="homepage"${cond.target === "homepage" ? " selected" : ""}>Homepage Only</option>
                        <option value="post_type"${cond.target === "post_type" ? " selected" : ""}>Specific Post Type</option>
                        <option value="taxonomy"${cond.target === "taxonomy" ? " selected" : ""}>In Category / Term</option>
                        <option value="specific_post"${cond.target === "specific_post" ? " selected" : ""}>Specific Post / Page ID</option>
                        <option value="archives"${cond.target === "archives" ? " selected" : ""}>Archive Pages</option>
                    `;

    const valContainer = document.createElement("div");
    valContainer.className = "gmb-cond-val-wrapper";

    function updateValField() {
      const tgt = targetSelect.value;
      valContainer.innerHTML = "";
      if (tgt === "post_type") {
        const sel = document.createElement("select");
        sel.className = "gmb-cond-value gmb-select";
        if (window.gmb_schema_pts && window.gmb_schema_pts.length) {
          window.gmb_schema_pts.forEach((pt) => {
            const opt = document.createElement("option");
            opt.value = pt.slug;
            opt.textContent = pt.label + " (" + pt.slug + ")";
            if (cond.value === pt.slug) opt.selected = true;
            sel.appendChild(opt);
          });
        }
        valContainer.appendChild(sel);
      } else if (tgt === "taxonomy") {
        const sel = document.createElement("select");
        sel.className = "gmb-cond-value gmb-select";
        if (window.gmb_schema_cats && window.gmb_schema_cats.length) {
          window.gmb_schema_cats.forEach((cat) => {
            const opt = document.createElement("option");
            opt.value = cat.slug;
            opt.textContent = cat.name + " (" + cat.slug + ")";
            if (cond.value === cat.slug) opt.selected = true;
            sel.appendChild(opt);
          });
        }
        valContainer.appendChild(sel);
      } else if (tgt === "specific_post") {
        const inp = document.createElement("input");
        inp.type = "number";
        inp.className = "gmb-cond-value gmb-input";
        inp.placeholder = "Enter Post or Page ID (e.g. 42)";
        inp.value = cond.value || "";
        valContainer.appendChild(inp);
      } else {
        valContainer.innerHTML =
          '<span class="gmb-cond-no-param">No additional parameters needed</span>';
      }
    }

    targetSelect.addEventListener("change", updateValField);
    updateValField();

    const delBtn = document.createElement("button");
    delBtn.type = "button";
    delBtn.className = "gmb-cond-del-btn";
    delBtn.innerHTML =
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
    delBtn.title = "Remove rule";
    delBtn.addEventListener("click", function () {
      row.remove();
    });

    row.appendChild(typeSelect);
    row.appendChild(targetSelect);
    row.appendChild(valContainer);
    row.appendChild(delBtn);
    tplConditionsContainer.appendChild(row);
  }

  if (tplAddCondBtn) {
    tplAddCondBtn.addEventListener("click", function () {
      renderConditionRow({ type: "include", target: "entire_site", value: "" });
    });
  }

  // Save Template Handler
  if (schemaTplSaveBtn) {
    schemaTplSaveBtn.addEventListener("click", function () {
      const title = tplTitleInput ? tplTitleInput.value.trim() : "";
      if (!title) {
        alert("Please enter a Schema Template Name.");
        switchBuilderTab("edit");
        tplTitleInput.focus();
        return;
      }

      const jsonRaw = tplJsonArea ? tplJsonArea.value.trim() : "";
      if (!jsonRaw) {
        alert("Please provide structured data JSON.");
        return;
      }

      try {
        JSON.parse(jsonRaw);
      } catch (e) {
        alert("Invalid JSON structured data: " + e.message);
        switchBuilderTab("code");
        return;
      }

      // Collect condition rows
      const conditions = [];
      const rows = tplConditionsContainer.querySelectorAll(
        ".gmb-condition-rule-row",
      );
      rows.forEach((r) => {
        const cType = r.querySelector(".gmb-cond-type")
          ? r.querySelector(".gmb-cond-type").value
          : "include";
        const cTarget = r.querySelector(".gmb-cond-target")
          ? r.querySelector(".gmb-cond-target").value
          : "entire_site";
        const valEl = r.querySelector(".gmb-cond-value");
        const cVal = valEl ? valEl.value : "";
        conditions.push({ type: cType, target: cTarget, value: cVal });
      });

      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const originalText = schemaTplSaveBtn.textContent;
      schemaTplSaveBtn.disabled = true;
      schemaTplSaveBtn.textContent = "Saving...";

      const fd = new FormData();
      fd.append("action", "gmb_save_schema_template");
      fd.append("nonce", getAdminNonce());
      fd.append("id", tplIdInput.value);
      fd.append("title", title);
      fd.append("type", tplTypeSelect.value);
      fd.append("status", tplStatusSelect.value);
      fd.append("schema_json", jsonRaw);

      conditions.forEach((c, idx) => {
        fd.append(`conditions[${idx}][type]`, c.type);
        fd.append(`conditions[${idx}][target]`, c.target);
        fd.append(`conditions[${idx}][value]`, c.value);
      });

      fetch(ajaxEndpoint, {
        method: "POST",
        body: fd,
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Template saved successfully!",
            );
            window.location.href =
              window.location.pathname +
              "?page=gmb-ranker-schema&tab=templates";
          } else {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Error saving template.",
            );
            schemaTplSaveBtn.disabled = false;
            schemaTplSaveBtn.textContent = originalText;
          }
        })
        .catch(() => {
          alert("Network error saving template.");
          schemaTplSaveBtn.disabled = false;
          schemaTplSaveBtn.textContent = originalText;
        });
    });
  }

  // Table Actions: Edit
  document.querySelectorAll(".gmb-edit-template-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      if (!id) return;
      btn.disabled = true;

      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const fd = new FormData();
      fd.append("action", "gmb_get_schema_template");
      fd.append("nonce", getAdminNonce());
      fd.append("id", id);

      fetch(ajaxEndpoint, { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          btn.disabled = false;
          if (res.success && res.data && res.data.template) {
            openBuilderModal(
              res.data.template.type || "Custom",
              true,
              res.data.template,
            );
          } else {
            alert("Failed to fetch template.");
          }
        })
        .catch(() => {
          btn.disabled = false;
          alert("Network error.");
        });
    });
  });

  // Table Actions: Duplicate
  document.querySelectorAll(".gmb-duplicate-template-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      if (!id) return;
      if (!confirm("Duplicate this Schema Template?")) return;

      btn.disabled = true;
      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const fd = new FormData();
      fd.append("action", "gmb_get_schema_template");
      fd.append("nonce", getAdminNonce());
      fd.append("id", id);

      fetch(ajaxEndpoint, { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          if (res.success && res.data && res.data.template) {
            const tpl = res.data.template;
            const dupFd = new FormData();
            dupFd.append("action", "gmb_save_schema_template");
            dupFd.append("nonce", getAdminNonce());
            dupFd.append("id", "");
            dupFd.append("title", tpl.title + " (Copy)");
            dupFd.append("type", tpl.type);
            dupFd.append("status", tpl.status);
            dupFd.append("schema_json", tpl.schema_json);
            if (tpl.conditions) {
              tpl.conditions.forEach((c, idx) => {
                dupFd.append(`conditions[${idx}][type]`, c.type);
                dupFd.append(`conditions[${idx}][target]`, c.target);
                dupFd.append(`conditions[${idx}][value]`, c.value);
              });
            }
            return fetch(ajaxEndpoint, { method: "POST", body: dupFd }).then(
              (r2) => r2.json(),
            );
          }
          throw new Error("Could not retrieve template");
        })
        .then((res2) => {
          if (res2.success) {
            window.location.href =
              window.location.pathname +
              "?page=gmb-ranker-schema&tab=templates";
          } else {
            alert("Error duplicating template");
            btn.disabled = false;
          }
        })
        .catch((err) => {
          alert(err.message || "Network error");
          btn.disabled = false;
        });
    });
  });

  // Table Actions: Toggle Status
  document.querySelectorAll(".gmb-toggle-template-status").forEach((cb) => {
    cb.addEventListener("change", function () {
      const id = this.getAttribute("data-id");
      if (!id) return;
      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const fd = new FormData();
      fd.append("action", "gmb_toggle_schema_template");
      fd.append("nonce", getAdminNonce());
      fd.append("id", id);

      fetch(ajaxEndpoint, { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          if (!res.success) {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Error changing status.",
            );
            cb.checked = !cb.checked;
          }
        })
        .catch(() => {
          alert("Network error.");
          cb.checked = !cb.checked;
        });
    });
  });

  // Table Actions: Delete
  document.querySelectorAll(".gmb-delete-template-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-id");
      if (!id) return;
      if (
        !confirm(
          "Are you sure you want to permanently delete this Schema Template?",
        )
      ) {
        return;
      }

      btn.disabled = true;
      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const fd = new FormData();
      fd.append("action", "gmb_delete_schema_template");
      fd.append("nonce", getAdminNonce());
      fd.append("id", id);

      fetch(ajaxEndpoint, { method: "POST", body: fd })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            const row = document.getElementById("gmb-tpl-row-" + id);
            if (row) {
              row.style.opacity = "0";
              setTimeout(() => row.remove(), 250);
            }
          } else {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Error deleting template.",
            );
            btn.disabled = false;
          }
        })
        .catch(() => {
          alert("Network error.");
          btn.disabled = false;
        });
    });
  });

  // 1-Click Schema Presets
  const presetBtns = document.querySelectorAll(".gmb-apply-preset-btn");
  presetBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const preset = this.getAttribute("data-preset");
      if (!preset) return;

      if (
        !confirm(
          "Apply this Schema blueprint? This will update your default schema mapping and knowledge graph settings.",
        )
      ) {
        return;
      }

      const originalText = this.textContent;
      this.disabled = true;
      this.textContent = "Applying...";

      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.schema_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const fd = new FormData();
      fd.append("action", "gmb_apply_schema_preset");
      fd.append("nonce", getAdminNonce());
      fd.append("preset", preset);

      fetch(ajaxEndpoint, {
        method: "POST",
        body: fd,
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Preset applied successfully!",
            );
            window.location.reload();
          } else {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Failed to apply preset.",
            );
            btn.disabled = false;
            btn.textContent = originalText;
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.textContent = originalText;
          alert("Network error applying preset.");
        });
    });
  });

  const gmb_toggle_module_nonce =
    (window.gmb_ranker_admin && window.gmb_ranker_admin.toggle_module_nonce) ||
    (window.gmb_ranker_admin && window.gmb_ranker_admin.nonce) ||
    "";

  function saveDashboardModuleToggle(checkbox) {
    if (!checkbox) return;

    const slider = checkbox.nextElementSibling;
    if (slider) slider.style.opacity = "0.5";

    const moduleName = checkbox.name;
    const moduleValue = checkbox.checked ? "1" : "0";

    const formData = new FormData();
    formData.append("action", "gmb_toggle_dashboard_module");
    if (typeof gmb_ranker_admin !== "undefined") {
      formData.append(
        "nonce",
        gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
      );
    }
    formData.append("nonce", gmb_toggle_module_nonce);
    formData.append("module", moduleName);
    formData.append("value", moduleValue);

    fetch(ajaxurl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (slider) slider.style.opacity = "";
        if (!data.success) {
          checkbox.checked = !checkbox.checked;
        }
      })
      .catch((err) => {
        if (slider) slider.style.opacity = "";
        checkbox.checked = !checkbox.checked;
      });
  }

  function updateCardActiveState(checkbox) {
    if (!checkbox) return;
    const card = checkbox.closest(".rm-card");
    if (!card) return;
    const settingsBtn = card.querySelector(".rm-settings-btn");
    if (checkbox.checked) {
      card.classList.remove("is-inactive");
      if (settingsBtn) {
        settingsBtn.style.visibility = "visible";
        settingsBtn.style.opacity = "1";
      }
    } else {
      card.classList.add("is-inactive");
      if (settingsBtn) {
        settingsBtn.style.visibility = "hidden";
        settingsBtn.style.opacity = "0";
      }
    }
  }

  const toggles = document.querySelectorAll(
    '#gmb-modules-form input[type="checkbox"]',
  );
  toggles.forEach((t) => {
    updateCardActiveState(t);
    t.addEventListener("change", function () {
      saveDashboardModuleToggle(this);
      updateCardActiveState(this);
    });
  });

  document.querySelectorAll(".gmb-btn-enable-module").forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const mod = this.getAttribute("data-module");
      if (!mod) return;
      this.disabled = true;
      this.textContent = "Enabling...";

      const fd = new FormData();
      fd.append("action", "gmb_toggle_dashboard_module");
      fd.append("nonce", gmb_toggle_module_nonce);
      fd.append("module", mod);
      fd.append("value", "1");

      fetch(ajaxurl, {
        method: "POST",
        body: fd,
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            window.location.reload();
          } else {
            alert(
              res.data && res.data.message
                ? res.data.message
                : "Error enabling module.",
            );
            btn.disabled = false;
            btn.textContent = "Enable Module";
          }
        })
        .catch(() => {
          window.location.reload();
        });
    });
  });

  // Module Category Filter Pills Click Handler
  const filterPills = document.querySelectorAll(".gmb-mod-filter-pill");
  if (filterPills.length > 0) {
    filterPills.forEach((pill) => {
      pill.addEventListener("click", function () {
        filterPills.forEach((p) => {
          p.classList.remove("active");
          p.style.background = "#ffffff";
          p.style.color = "#475569";
          p.style.borderColor = "#cbd5e1";
        });
        this.classList.add("active");
        this.style.background = "#466afa";
        this.style.color = "#ffffff";
        this.style.borderColor = "#466afa";

        const filter = this.getAttribute("data-filter");
        const cards = document.querySelectorAll(".rm-card");
        cards.forEach((card) => {
          const category = card.getAttribute("data-category");
          if (filter === "all" || category === filter) {
            card.style.setProperty("display", "flex", "important");
          } else {
            card.style.setProperty("display", "none", "important");
          }
        });
      });
    });
  }

  const overlay = document.getElementById("api-settings-overlay");
  const openBtn = document.getElementById("open-api-settings");
  const helpOpenBtn = document.getElementById("help-open-settings");
  const closeBtn = document.getElementById("close-api-settings");

  if (openBtn) {
    openBtn.addEventListener("click", function () {
      overlay.classList.add("active");
    });
  }
  if (helpOpenBtn) {
    helpOpenBtn.addEventListener("click", function () {
      overlay.classList.add("active");
    });
  }

  if (closeBtn && overlay) {
    closeBtn.addEventListener("click", function () {
      overlay.classList.remove("active");
    });
  }

  if (overlay) {
    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) {
        overlay.classList.remove("active");
      }
    });
  }

  var llmsOpenBtn = document.getElementById("open-llmstxt-settings");
  if (llmsOpenBtn) {
    llmsOpenBtn.addEventListener("click", function () {
      window.location.href = "admin.php?page=gmb-ranker-settings&tab=llmstxt";
    });
  }

  var metaOpenBtn = document.getElementById("open-metadata-settings");
  if (metaOpenBtn) {
    metaOpenBtn.addEventListener("click", function () {
      window.location.href = "admin.php?page=gmb-ranker-metadata";
    });
  }

  var sitemapsOpenBtn = document.getElementById("open-sitemaps-settings");
  if (sitemapsOpenBtn) {
    sitemapsOpenBtn.addEventListener("click", function () {
      window.location.href = "admin.php?page=gmb-ranker-sitemaps";
    });
  }

  var selectAllTypesBtn = document.getElementById("llms-select-all-types");
  if (selectAllTypesBtn) {
    selectAllTypesBtn.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(".llms-post-type-checkbox");
      const anyUnchecked = Array.from(checkboxes).some((cb) => !cb.checked);
      checkboxes.forEach((cb) => (cb.checked = anyUnchecked));
    });
  }

  var selectAllTaxsBtn = document.getElementById("llms-select-all-taxs");
  if (selectAllTaxsBtn) {
    selectAllTaxsBtn.addEventListener("click", function () {
      const checkboxes = document.querySelectorAll(".llms-tax-checkbox");
      const anyUnchecked = Array.from(checkboxes).some((cb) => !cb.checked);
      checkboxes.forEach((cb) => (cb.checked = anyUnchecked));
    });
  }

  var resetLlmsBtn = document.getElementById("llms-reset-options-btn");
  if (resetLlmsBtn) {
    resetLlmsBtn.addEventListener("click", function () {
      if (confirm("Are you sure you want to reset all LLMs Txt settings?")) {
        document.querySelectorAll(".llms-post-type-checkbox").forEach((cb) => {
          cb.checked = ["post", "page", "product"].includes(cb.value);
        });
        document
          .querySelectorAll(".llms-tax-checkbox")
          .forEach((cb) => (cb.checked = false));
        document.querySelector('input[name="gmb_llms_limit"]').value = "100";
        document.querySelector('input[name="gmb_llms_title"]').value =
          (window.gmb_ranker_admin && window.gmb_ranker_admin.site_name) || "";
        document.querySelector('textarea[name="gmb_llms_desc"]').value =
          (window.gmb_ranker_admin && window.gmb_ranker_admin.site_desc) || "";
        document.querySelector(
          'textarea[name="gmb_llms_additional_content"]',
        ).value = "";
      }
    });
  }

  var prefOverlay = document.getElementById(
    "preferred-source-settings-overlay",
  );
  var prefOpenBtn = document.getElementById("open-preferred-source-settings");
  var prefCloseBtn = document.getElementById("close-preferred-source-settings");

  if (prefOpenBtn) {
    prefOpenBtn.addEventListener("click", function () {
      prefOverlay.classList.add("active");
    });
  }
  if (prefCloseBtn) {
    prefCloseBtn.addEventListener("click", function () {
      prefOverlay.classList.remove("active");
    });
  }
  if (prefOverlay) {
    prefOverlay.addEventListener("click", function (e) {
      if (e.target === prefOverlay) {
        prefOverlay.classList.remove("active");
      }
    });
  }

  // Sub-tab AI Settings switching logic
  const aiSelectSub = document.getElementById("gmb-ai-provider-select-sub");
  function toggleAiSectionsSub() {
    if (!aiSelectSub) return;
    const sections = ["openrouter", "groq", "ollama"];
    const currentVal = aiSelectSub.value;
    sections.forEach(function (sec) {
      const elem = document.getElementById("ai-section-" + sec + "-sub");
      if (elem) {
        if (sec === currentVal) {
          elem.classList.remove("gmb-hidden");
        } else {
          elem.classList.add("gmb-hidden");
        }
      }
    });
  }
  if (aiSelectSub) {
    aiSelectSub.addEventListener("change", toggleAiSectionsSub);
    toggleAiSectionsSub();
  }

  const resetAiBtn = document.getElementById("gmb-reset-ai-options");
  if (resetAiBtn) {
    resetAiBtn.addEventListener("click", function () {
      if (confirm("Are you sure you want to reset Content AI settings?")) {
        if (aiSelectSub) {
          aiSelectSub.value = "openrouter";
          toggleAiSectionsSub();
        }
        document.querySelector('input[name="gmb_ai_openrouter_key"]').value =
          "";
        document.querySelector('input[name="gmb_ai_openrouter_model"]').value =
          "meta-llama/llama-3-8b-instruct:free";
        document.querySelector('input[name="gmb_ai_groq_key"]').value = "";
        document.querySelector('input[name="gmb_ai_groq_model"]').value =
          "llama3-8b-8192";
        document.querySelector('input[name="gmb_ai_ollama_url"]').value =
          "http://localhost:11434";
        document.querySelector('input[name="gmb_ai_ollama_model"]').value =
          "llama3";
      }
    });
  }

  var aiOverlay = document.getElementById("ai-provider-settings-overlay");
  var aiOpenBtn = document.getElementById("open-ai-provider-settings");
  var aiCloseBtn = document.getElementById("close-ai-provider-settings");
  var aiSelect = document.getElementById("gmb-ai-provider-select");

  function toggleAiSections() {
    if (!aiSelect) return;
    const sections = ["openrouter", "groq", "ollama"];
    const currentVal = aiSelect.value;
    sections.forEach(function (sec) {
      const elem = document.getElementById("ai-section-" + sec);
      if (elem) {
        if (sec === currentVal) {
          elem.classList.remove("gmb-hidden");
        } else {
          elem.classList.add("gmb-hidden");
        }
      }
    });
  }

  if (aiSelect) {
    aiSelect.addEventListener("change", toggleAiSections);
    toggleAiSections();
  }

  if (aiOpenBtn) {
    aiOpenBtn.addEventListener("click", function () {
      aiOverlay.classList.add("active");
      toggleAiSections();
    });
  }
  if (aiCloseBtn) {
    aiCloseBtn.addEventListener("click", function () {
      aiOverlay.classList.remove("active");
    });
  }
  if (aiOverlay) {
    aiOverlay.addEventListener("click", function (e) {
      if (e.target === aiOverlay) {
        aiOverlay.classList.remove("active");
      }
    });
  }
  const tocOpenBtn = document.getElementById("open-toc-settings");
  if (tocOpenBtn) {
    tocOpenBtn.addEventListener("click", function () {
      const tabBtn = document.querySelector(
        '.rm-nav-tab[data-tab="rm-tab-toc"]',
      );
      if (tabBtn) {
        tabBtn.click();
      } else {
        window.location.href = "admin.php?page=gmb-ranker-settings&tab=toc";
      }
    });
  }

  const dbOverlay = document.getElementById("db-tools-settings-overlay");
  const dbManageBtn = document.getElementById("db-tools-trigger-btn");
  const dbCloseBtn = document.getElementById("close-db-settings");

  if (dbManageBtn) {
    dbManageBtn.addEventListener("click", function () {
      dbOverlay.classList.add("active");
    });
  }

  if (dbCloseBtn) {
    dbCloseBtn.addEventListener("click", function () {
      dbOverlay.classList.remove("active");
    });
  }

  if (dbOverlay) {
    dbOverlay.addEventListener("click", function (e) {
      if (e.target === dbOverlay) {
        dbOverlay.classList.remove("active");
      }
    });
  }

  const roleOverlay = document.getElementById("role-manager-settings-overlay");
  const roleManageBtn = document.getElementById("role-manager-trigger-btn");
  const roleCloseBtn = document.getElementById("close-role-settings");

  if (roleManageBtn) {
    roleManageBtn.addEventListener("click", function () {
      roleOverlay.classList.add("active");
    });
  }

  if (roleCloseBtn) {
    roleCloseBtn.addEventListener("click", function () {
      roleOverlay.classList.remove("active");
    });
  }

  if (roleOverlay) {
    roleOverlay.addEventListener("click", function (e) {
      if (e.target === roleOverlay) {
        roleOverlay.classList.remove("active");
      }
    });
  }

  const idxOverlay = document.getElementById(
    "instant-indexing-settings-overlay",
  );
  const idxManageBtn = document.getElementById("instant-indexing-trigger-btn");
  const idxCloseBtn = document.getElementById("close-indexing-settings");

  if (idxManageBtn) {
    idxManageBtn.addEventListener("click", function () {
      idxOverlay.classList.add("active");
    });
  }

  if (idxCloseBtn) {
    idxCloseBtn.addEventListener("click", function () {
      idxOverlay.classList.remove("active");
    });
  }

  if (idxOverlay) {
    idxOverlay.addEventListener("click", function (e) {
      if (e.target === idxOverlay) {
        idxOverlay.classList.remove("active");
      }
    });
  }

  const toggleMultiCheck = document.getElementById(
    "gmb-toggle-multi-locations",
  );
  const singleLocPanel = document.getElementById("gmb-single-location-panel");
  const multiLocPanel = document.getElementById("gmb-multiple-locations-panel");

  if (toggleMultiCheck) {
    toggleMultiCheck.addEventListener("change", function () {
      if (toggleMultiCheck.checked) {
        singleLocPanel.style.display = "none";
        multiLocPanel.style.display = "block";
      } else {
        singleLocPanel.style.display = "block";
        multiLocPanel.style.display = "none";
      }
    });
  }

  const addLocBtn = document.getElementById("gmb-add-loc-btn");
  if (addLocBtn) {
    addLocBtn.addEventListener("click", function () {
      const name = document.getElementById("gmb-new-loc-name").value.trim();
      const phone = document.getElementById("gmb-new-loc-phone").value.trim();
      const address = document
        .getElementById("gmb-new-loc-address")
        .value.trim();

      if (!name) {
        alert("Location name is required!");
        return;
      }

      addLocBtn.innerText = "Saving...";
      const formData = new FormData();
      formData.append("action", "gmb_add_local_location");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("name", name);
      formData.append("phone", phone);
      formData.append("address", address);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          addLocBtn.innerText = "Save Location";
          if (data.success) {
            alert("Location added successfully!");
            window.location.reload();
          }
        });
    });
  }

  const deleteLocBtns = document.querySelectorAll(".gmb-delete-loc-btn");
  deleteLocBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = btn.getAttribute("data-id");
      if (confirm("Are you sure you want to delete this business location?")) {
        const formData = new FormData();
        formData.append("action", "gmb_delete_local_location");
        if (typeof gmb_ranker_admin !== "undefined") {
          formData.append(
            "nonce",
            gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
          );
        }
        formData.append("id", id);

        fetch(ajaxurl, {
          method: "POST",
          body: new URLSearchParams(formData),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              alert("Location deleted!");
              window.location.reload();
            }
          });
      }
    });
  });

  const wizPanel1 = document.getElementById("wiz-panel-1");
  const wizPanel2 = document.getElementById("wiz-panel-2");
  const wizPanel3 = document.getElementById("wiz-panel-3");

  const wizNode1 = document.getElementById("wiz-node-1");
  const wizNode2 = document.getElementById("wiz-node-2");
  const wizNode3 = document.getElementById("wiz-node-3");

  const wizNext1 = document.getElementById("wiz-next-1");
  if (wizNext1) {
    wizNext1.addEventListener("click", function () {
      if (wizPanel1) wizPanel1.style.display = "none";
      if (wizPanel2) wizPanel2.style.display = "block";
      if (wizNode1) wizNode1.className = "wizard-step-node completed";
      if (wizNode2) wizNode2.className = "wizard-step-node active";
    });
  }

  const wizBack2 = document.getElementById("wiz-back-2");
  if (wizBack2) {
    wizBack2.addEventListener("click", function () {
      if (wizPanel2) wizPanel2.style.display = "none";
      if (wizPanel1) wizPanel1.style.display = "block";
      if (wizNode1) wizNode1.className = "wizard-step-node active";
      if (wizNode2) wizNode2.className = "wizard-step-node";
    });
  }

  const wizSave2 = document.getElementById("wiz-save-2");
  if (wizSave2) {
    wizSave2.addEventListener("click", function () {
      const keyInput = document.getElementById("wiz-api-key-input");
      const keyVal = keyInput ? keyInput.value : "";

      const formData = new FormData();
      formData.append("action", "gmb_save_wizard_api_key");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("api_key", keyVal);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      }).then((res) => {
        if (wizPanel2) wizPanel2.style.display = "none";
        if (wizPanel3) wizPanel3.style.display = "block";
        if (wizNode2) wizNode2.className = "wizard-step-node completed";
        if (wizNode3) wizNode3.className = "wizard-step-node completed";
      });
    });
  }

  const wizFinish = document.getElementById("wiz-finish");
  if (wizFinish) {
    wizFinish.addEventListener("click", function () {
      window.location.href = "admin.php?page=gmb-ranker-automation";
    });
  }

  const optBtn = document.getElementById("gmb-db-optimize-btn");
  if (optBtn) {
    optBtn.addEventListener("click", function () {
      optBtn.innerText = "Optimizing...";
      const formData = new FormData();
      formData.append("action", "gmb_db_optimize_tables");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          optBtn.innerText = "Run Tool";
          if (data.success) {
            alert("Database tables optimized successfully!");
          }
        });
    });
  }

  const orphanBtn = document.getElementById("gmb-db-orphan-btn");
  if (orphanBtn) {
    orphanBtn.addEventListener("click", function () {
      orphanBtn.innerText = "Cleaning...";
      const formData = new FormData();
      formData.append("action", "gmb_db_clear_orphan_meta");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          orphanBtn.innerText = "Run Tool";
          if (data.success) {
            alert(
              "Deleted " + data.data + " orphan postmeta entries successfully!",
            );
          }
        });
    });
  }

  const transBtn = document.getElementById("gmb-db-transients-btn");
  if (transBtn) {
    transBtn.addEventListener("click", function () {
      transBtn.innerText = "Purging...";
      const formData = new FormData();
      formData.append("action", "gmb_db_clear_transients");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          transBtn.innerText = "Run Tool";
          if (data.success) {
            alert("Deleted " + data.data + " expired cache transient options!");
          }
        });
    });
  }

  function triggerRankMathImport(btn) {
    if (
      !confirm(
        "Are you sure you want to import all SEO metadata (Titles, Descriptions, Keywords, Canonicals, Robots) from Rank Math? Existing GMB Ranker metadata for posts that have Rank Math settings will be overwritten.",
      )
    ) {
      return;
    }
    btn.innerText = "Importing...";
    const formData = new FormData();
    formData.append("action", "gmb_db_import_rankmath");
    if (typeof gmb_ranker_admin !== "undefined") {
      formData.append(
        "nonce",
        gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
      );
    }

    fetch(ajaxurl, {
      method: "POST",
      body: new URLSearchParams(formData),
    })
      .then((res) => res.json())
      .then((data) => {
        btn.innerText =
          btn.id === "gmb-db-import-rankmath-btn" ? "Run Tool" : "Import Now";
        if (data.success) {
          alert("Rank Math data imported successfully!");
          window.location.reload();
        } else {
          alert("Import failed or no Rank Math data found.");
        }
      });
  }

  const dbImportRmBtn = document.getElementById("gmb-db-import-rankmath-btn");
  const mainImportRmBtn = document.getElementById("importer-rm-btn");

  if (dbImportRmBtn) {
    dbImportRmBtn.addEventListener("click", function () {
      triggerRankMathImport(dbImportRmBtn);
    });
  }
  if (mainImportRmBtn) {
    mainImportRmBtn.addEventListener("click", function () {
      triggerRankMathImport(mainImportRmBtn);
    });
  }

  // Yoast Importer Click listener
  const importYoastBtn = document.getElementById("importer-yoast-btn");
  if (importYoastBtn) {
    importYoastBtn.addEventListener("click", function () {
      if (
        !confirm(
          "Are you sure you want to import all SEO metadata (Titles, Descriptions, Keywords, Canonicals, Robots) from Yoast SEO? Existing GMB Ranker metadata for posts that have Yoast settings will be overwritten.",
        )
      ) {
        return;
      }
      importYoastBtn.innerText = "Importing...";
      const formData = new FormData();
      formData.append("action", "gmb_db_import_yoast");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          importYoastBtn.innerText = "Import Now";
          if (data.success) {
            alert("Yoast SEO data imported successfully!");
            window.location.reload();
          } else {
            alert("Import failed or no Yoast SEO data found.");
          }
        });
    });
  }

  // Restore Backup click listener
  const restoreBtn = document.getElementById("gmb-restore-submit-btn");
  if (restoreBtn) {
    restoreBtn.addEventListener("click", function () {
      const fileInput = document.getElementById("gmb-restore-file-input");
      if (!fileInput || !fileInput.files.length) {
        alert("Please select a settings JSON backup file first.");
        return;
      }

      if (
        !confirm(
          "Are you sure you want to restore settings from this backup? Current configurations will be overwritten.",
        )
      ) {
        return;
      }

      restoreBtn.innerText = "Restoring...";
      const file = fileInput.files[0];
      const formData = new FormData();
      formData.append("action", "gmb_import_settings_upload");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("settings_file", file);

      fetch(ajaxurl, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          restoreBtn.innerText = "Restore Backup";
          if (data.success) {
            alert("Settings successfully restored!");
            window.location.reload();
          } else {
            alert(data.data || "Restore process failed.");
          }
        });
    });
  }

  const saveRolesBtn = document.getElementById("gmb-save-roles-btn");
  if (saveRolesBtn) {
    saveRolesBtn.addEventListener("click", function () {
      saveRolesBtn.innerText = "Saving...";

      const formData = new FormData();
      formData.append("action", "gmb_save_role_permissions");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }

      const checkboxes = document.querySelectorAll(".gmb-role-checkbox");
      checkboxes.forEach((cb) => {
        const role = cb.getAttribute("data-role");
        const cap = cb.getAttribute("data-cap");
        const val = cb.checked ? "1" : "0";
        formData.append("matrix[" + role + "][" + cap + "]", val);
      });

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          saveRolesBtn.innerText = "Save Role Permissions";
          if (data.success) {
            alert("Role permissions saved successfully!");
            roleOverlay.classList.remove("active");
          }
        });
    });
  }

  const submitIndexingBtn = document.getElementById("gmb-submit-indexing-btn");
  if (submitIndexingBtn) {
    submitIndexingBtn.addEventListener("click", function () {
      const urls = document.getElementById("gmb-indexing-urls").value.trim();
      if (!urls) {
        alert("Please enter at least one URL to submit!");
        return;
      }

      submitIndexingBtn.innerText = "Submitting...";
      const getAdminNonce = () => {
        return (
          (window.gmb_ranker_admin &&
            (window.gmb_ranker_admin.instant_index_nonce ||
              window.gmb_ranker_admin.admin_nonce ||
              window.gmb_ranker_admin.nonce)) ||
          ""
        );
      };
      const ajaxEndpoint =
        typeof ajaxurl !== "undefined"
          ? ajaxurl
          : window.gmb_ranker_admin && window.gmb_ranker_admin.ajax_url
            ? window.gmb_ranker_admin.ajax_url
            : "/wp-admin/admin-ajax.php";

      const formData = new FormData();
      formData.append("action", "gmb_instant_index_submit");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("nonce", getAdminNonce());
      formData.append("urls", urls);

      fetch(ajaxEndpoint, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          submitIndexingBtn.innerText = "Submit to IndexNow";
          if (data.success) {
            alert("URLs successfully submitted to IndexNow API!");
            idxOverlay.classList.remove("active");
            document.getElementById("gmb-indexing-urls").value = "";
          } else {
            alert("Submission failed: " + data.data);
          }
        });
    });
  }

  // Redirections Add/Edit Form & Filters logic
  const addRuleBtn = document.getElementById("gmb-add-rule-btn");
  if (addRuleBtn) {
    addRuleBtn.addEventListener("click", function () {
      const editId = document.getElementById("gmb-redirect-edit-id")
        ? document.getElementById("gmb-redirect-edit-id").value.trim()
        : "";
      const source = document
        .getElementById("gmb-redirect-source")
        .value.trim();
      const destination = document
        .getElementById("gmb-redirect-destination")
        .value.trim();
      const code = document.getElementById("gmb-redirect-code").value;
      const matchTypeEl = document.getElementById("gmb-redirect-match-type");
      const statusEl = document.getElementById("gmb-redirect-status");
      const noteEl = document.getElementById("gmb-redirect-note");
      const matchType = matchTypeEl ? matchTypeEl.value : "exact";
      const status = statusEl ? statusEl.value : "active";
      const note = noteEl ? noteEl.value.trim() : "";

      if (!source || !destination) {
        alert("Source and destination fields are required!");
        return;
      }

      addRuleBtn.disabled = true;
      const formData = new FormData();
      formData.append("action", "gmb_add_redirect_rule");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      if (editId) {
        formData.append("id", editId);
      }
      formData.append("source", source);
      formData.append("destination", destination);
      formData.append("code", code);
      formData.append("match_type", matchType);
      formData.append("status", status);
      formData.append("note", note);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          addRuleBtn.disabled = false;
          if (data.success) {
            window.location.reload();
          } else {
            alert(
              "Failed to save redirection rule: " +
                (data.data || "Unknown error"),
            );
          }
        })
        .catch((err) => {
          addRuleBtn.disabled = false;
          alert("Error: " + err.message);
        });
    });
  }

  // Edit Rule button handler
  const editRuleBtns = document.querySelectorAll(".gmb-edit-rule-btn");
  editRuleBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const formContainer = document.getElementById(
        "gmb-redirect-form-container",
      );
      if (formContainer) {
        formContainer.style.display = "block";
        document.getElementById("gmb-redirect-form-title").innerText =
          "Edit Redirection";
        const badge = document.getElementById("gmb-redirect-edit-badge");
        if (badge) badge.style.display = "inline-block";

        document.getElementById("gmb-redirect-edit-id").value =
          btn.getAttribute("data-id") || "";
        document.getElementById("gmb-redirect-source").value =
          btn.getAttribute("data-source") || "";
        document.getElementById("gmb-redirect-destination").value =
          btn.getAttribute("data-dest") || "";
        document.getElementById("gmb-redirect-code").value =
          btn.getAttribute("data-code") || "301";
        document.getElementById("gmb-redirect-match-type").value =
          btn.getAttribute("data-match") || "exact";
        document.getElementById("gmb-redirect-status").value =
          btn.getAttribute("data-status") || "active";
        if (document.getElementById("gmb-redirect-note")) {
          document.getElementById("gmb-redirect-note").value =
            btn.getAttribute("data-note") || "";
        }

        formContainer.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  const deleteBtns = document.querySelectorAll(".gmb-delete-rule-btn");
  deleteBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = btn.getAttribute("data-id");
      if (confirm("Are you sure you want to delete this redirection rule?")) {
        const formData = new FormData();
        formData.append("action", "gmb_delete_redirect_rule");
        if (typeof gmb_ranker_admin !== "undefined") {
          formData.append(
            "nonce",
            gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
          );
        }
        formData.append("id", id);

        fetch(ajaxurl, {
          method: "POST",
          body: new URLSearchParams(formData),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              window.location.reload();
            }
          });
      }
    });
  });

  // Toggle Single 404 delete button
  const delete404Btns = document.querySelectorAll(".gmb-delete-single-404-btn");
  delete404Btns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const url = btn.getAttribute("data-url");
      if (confirm("Delete this 404 log entry?")) {
        const formData = new FormData();
        formData.append("action", "gmb_delete_single_404_log");
        if (typeof gmb_ranker_admin !== "undefined") {
          formData.append(
            "nonce",
            gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
          );
        }
        formData.append("uri", url);

        fetch(ajaxurl, {
          method: "POST",
          body: new URLSearchParams(formData),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              const row = btn.closest("tr");
              if (row) row.remove();
            }
          });
      }
    });
  });

  // 404 Redirect button
  const createBtns = document.querySelectorAll(".gmb-create-redirect-btn");
  createBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const url = btn.getAttribute("data-url");
      const manageNavBtn = document.querySelector(
        '.gmb-redirect-subnav[data-sub="gmb-redirect-manage"]',
      );
      if (manageNavBtn) {
        manageNavBtn.click();
      }
      const formContainer = document.getElementById(
        "gmb-redirect-form-container",
      );
      if (formContainer) {
        formContainer.style.display = "block";
        document.getElementById("gmb-redirect-form-title").innerText =
          "Add Redirection from 404 Log";
        document.getElementById("gmb-redirect-edit-id").value = "";
        document.getElementById("gmb-redirect-source").value = url;
        document.getElementById("gmb-redirect-destination").value = "";
        document.getElementById("gmb-redirect-destination").focus();
        formContainer.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  // Toggle Add Redirection Form
  const toggleAddFormBtn = document.getElementById("gmb-toggle-add-form-btn");
  const cancelAddBtn = document.getElementById("gmb-cancel-add-btn");
  const redirectFormContainer = document.getElementById(
    "gmb-redirect-form-container",
  );

  if (toggleAddFormBtn && redirectFormContainer) {
    toggleAddFormBtn.addEventListener("click", function () {
      if (redirectFormContainer.style.display === "none") {
        redirectFormContainer.style.display = "block";
        document.getElementById("gmb-redirect-form-title").innerText =
          "Add New Redirection";
        document.getElementById("gmb-redirect-edit-id").value = "";
        document.getElementById("gmb-redirect-source").value = "";
        document.getElementById("gmb-redirect-destination").value = "";
        if (document.getElementById("gmb-redirect-note"))
          document.getElementById("gmb-redirect-note").value = "";
        const badge = document.getElementById("gmb-redirect-edit-badge");
        if (badge) badge.style.display = "none";
      } else {
        redirectFormContainer.style.display = "none";
      }
    });
  }

  if (cancelAddBtn && redirectFormContainer) {
    cancelAddBtn.addEventListener("click", function () {
      redirectFormContainer.style.display = "none";
    });
  }


  // AI Auto-Fix 404 Redirections Handler
  const aiSuggestBtn = document.getElementById("gmb-ai-suggest-404-btn");
  const aiModal = document.getElementById("gmb-ai-redirect-modal");
  const aiModalClose = document.getElementById("gmb-ai-modal-close");
  const aiModalCancel = document.getElementById("gmb-ai-modal-cancel");
  const aiApplyBtn = document.getElementById("gmb-ai-apply-btn");
  const aiLoadingBox = document.getElementById("gmb-ai-modal-loading");
  const aiModalContent = document.getElementById("gmb-ai-modal-content");
  const aiTbody = document.getElementById("gmb-ai-suggestions-tbody");
  const aiSelectAll = document.getElementById("gmb-ai-select-all");

  function openAiModal() {
    if (!aiModal) return;
    aiModal.style.display = "flex";
    aiModal.classList.add("active");
    if (aiLoadingBox) aiLoadingBox.style.display = "flex";
    if (aiModalContent) aiModalContent.classList.add("gmb-hidden");
    if (aiApplyBtn) aiApplyBtn.disabled = true;
    if (aiTbody) aiTbody.innerHTML = "";
  }

  function closeAiModal() {
    if (aiModal) {
      aiModal.style.display = "none";
      aiModal.classList.remove("active");
    }
  }

  if (aiModalClose) aiModalClose.addEventListener("click", closeAiModal);
  if (aiModalCancel) aiModalCancel.addEventListener("click", closeAiModal);

  function fetchAiSuggestions(singleUri) {
    openAiModal();
    const formData = new FormData();
    formData.append("action", "gmb_ai_suggest_404_redirects");
    if (typeof gmb_ranker_admin !== "undefined") {
      formData.append("nonce", gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce);
    }
    if (singleUri) {
      formData.append("uri", singleUri);
    }

    fetch(ajaxurl, {
      method: "POST",
      body: new URLSearchParams(formData)
    })
    .then(res => res.json())
    .then(data => {
      if (aiLoadingBox) aiLoadingBox.style.display = "none";
      if (!data.success || !data.data || !data.data.suggestions || data.data.suggestions.length === 0) {
        alert("AI suggestions failed: " + (data.data || "No 404 entries to process"));
        closeAiModal();
        return;
      }

      if (aiModalContent) aiModalContent.classList.remove("gmb-hidden");
      if (aiApplyBtn) aiApplyBtn.disabled = false;

      let html = "";
      data.data.suggestions.forEach((item, idx) => {
        const confClass = item.confidence === 'high' ? 'gmb-pill-badge--green' : (item.confidence === 'medium' ? 'gmb-pill-badge--blue' : 'gmb-pill-badge--red');
        html += `
          <tr class="gmb-ai-suggestion-row">
            <td class="gmb-text-center">
              <input type="checkbox" class="gmb-ai-rule-check" data-idx="${idx}" checked />
            </td>
            <td>
              <code class="gmb-code-path">${item.source}</code>
            </td>
            <td>
              <input type="text" class="gmb-input gmb-input-sm gmb-ai-dest-input" value="${item.destination || ''}" placeholder="/destination-path or empty for 410" />
            </td>
            <td>
              <select class="gmb-select gmb-select-sm gmb-ai-code-select">
                <option value="301" ${item.code == 301 ? 'selected' : ''}>301 Move</option>
                <option value="302" ${item.code == 302 ? 'selected' : ''}>302 Temp</option>
                <option value="410" ${item.code == 410 ? 'selected' : ''}>410 Gone</option>
              </select>
            </td>
            <td>
              <span class="gmb-pill-badge ${confClass}">${item.confidence || 'medium'}</span>
              <div class="gmb-text-xs gmb-text-muted">${item.reason || ''}</div>
            </td>
          </tr>
        `;
      });

      if (aiTbody) aiTbody.innerHTML = html;
    })
    .catch(err => {
      if (aiLoadingBox) aiLoadingBox.style.display = "none";
      alert("Error generating AI suggestions: " + err.message);
      closeAiModal();
    });
  }

  if (aiSuggestBtn) {
    aiSuggestBtn.addEventListener("click", function() {
      fetchAiSuggestions("");
    });
  }

  // Single row AI fix button
  const singleAiBtns = document.querySelectorAll(".gmb-ai-single-suggest-btn");
  singleAiBtns.forEach(btn => {
    btn.addEventListener("click", function() {
      const url = btn.getAttribute("data-url");
      fetchAiSuggestions(url);
    });
  });

  // Select all AI checkboxes
  if (aiSelectAll) {
    aiSelectAll.addEventListener("change", function() {
      const checks = document.querySelectorAll(".gmb-ai-rule-check");
      checks.forEach(c => c.checked = aiSelectAll.checked);
    });
  }

  // Batch Apply AI Rules
  if (aiApplyBtn) {
    aiApplyBtn.addEventListener("click", function() {
      const rows = document.querySelectorAll(".gmb-ai-suggestion-row");
      const rulesToApply = [];

      rows.forEach(row => {
        const check = row.querySelector(".gmb-ai-rule-check");
        if (check && check.checked) {
          const source = row.querySelector(".gmb-code-path").innerText.trim();
          const dest = row.querySelector(".gmb-ai-dest-input").value.trim();
          const code = row.querySelector(".gmb-ai-code-select").value;

          rulesToApply.push({
            source: source,
            destination: dest,
            code: parseInt(code) || 301
          });
        }
      });

      if (rulesToApply.length === 0) {
        alert("Please select at least one AI recommendation to apply.");
        return;
      }

      aiApplyBtn.disabled = true;
      aiApplyBtn.innerText = "Applying AI Rules...";

      const formData = new FormData();
      formData.append("action", "gmb_apply_ai_redirects");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append("nonce", gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce);
      }
      formData.append("rules", JSON.stringify(rulesToApply));

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData)
      })
      .then(res => res.json())
      .then(data => {
        aiApplyBtn.disabled = false;
        aiApplyBtn.innerText = "Batch Apply AI Rules";
        if (data.success) {
          alert(data.data.message || "AI Redirections successfully applied!");
          window.location.reload();
        } else {
          alert("Failed to apply AI rules: " + (data.data || "Unknown error"));
        }
      })
      .catch(err => {
        aiApplyBtn.disabled = false;
        aiApplyBtn.innerText = "Batch Apply AI Rules";
        alert("Error applying AI rules: " + err.message);
      });
    });
  }

  // Redirections subnavigation tab switching logic
  const redirectSubnavs = document.querySelectorAll(".gmb-redirect-subnav");
  const redirectPanels = document.querySelectorAll(".gmb-redirect-view-panel");
  const manageTopActions = document.getElementById(
    "gmb-redirect-manage-top-actions",
  );

  redirectSubnavs.forEach((btn) => {
    btn.addEventListener("click", function () {
      redirectSubnavs.forEach((b) => {
        b.classList.remove("active");
        b.style.background = "";
        b.style.borderColor = "";
        b.style.color = "";
      });

      btn.classList.add("active");
      btn.style.background = "";
      btn.style.borderColor = "";
      btn.style.color = "";

      const targetSub = btn.getAttribute("data-sub");
      redirectPanels.forEach((panel) => {
        panel.style.display = "none";
      });

      const targetPanel = document.getElementById(targetSub + "-view");
      if (targetPanel) {
        targetPanel.style.display = "block";
      }

      if (manageTopActions) {
        manageTopActions.style.display =
          targetSub === "gmb-redirect-manage" ? "block" : "none";
      }
    });
  });

  // Live search & filters for Redirection Rules table
  const redirectSearchInput = document.getElementById("gmb-redirect-search");
  const redirectFilterCode = document.getElementById(
    "gmb-filter-redirect-code",
  );
  const redirectFilterStatus = document.getElementById(
    "gmb-filter-redirect-status",
  );

  function filterRedirectRules() {
    const query = redirectSearchInput
      ? redirectSearchInput.value.toLowerCase().trim()
      : "";
    const codeFilter = redirectFilterCode ? redirectFilterCode.value : "all";
    const statusFilter = redirectFilterStatus
      ? redirectFilterStatus.value
      : "all";

    const rows = document.querySelectorAll(".gmb-rule-row");
    rows.forEach((row) => {
      const src = (
        row.querySelector(".gmb-rule-source-text")?.innerText || ""
      ).toLowerCase();
      const dest = (
        row.querySelector(".gmb-rule-dest-text")?.innerText || ""
      ).toLowerCase();
      const note = (
        row.querySelector(".gmb-rule-note-text")?.innerText || ""
      ).toLowerCase();
      const code = row.getAttribute("data-code");
      const status = row.getAttribute("data-status");

      const matchesQuery =
        !query ||
        src.includes(query) ||
        dest.includes(query) ||
        note.includes(query);
      const matchesCode = codeFilter === "all" || code === codeFilter;
      const matchesStatus = statusFilter === "all" || status === statusFilter;

      row.style.display =
        matchesQuery && matchesCode && matchesStatus ? "" : "none";
    });
  }

  if (redirectSearchInput)
    redirectSearchInput.addEventListener("input", filterRedirectRules);
  if (redirectFilterCode)
    redirectFilterCode.addEventListener("change", filterRedirectRules);
  if (redirectFilterStatus)
    redirectFilterStatus.addEventListener("change", filterRedirectRules);

  // Live search for 404 logs table
  const search404Input = document.getElementById("gmb-404-search");
  if (search404Input) {
    search404Input.addEventListener("input", function () {
      const q = this.value.toLowerCase().trim();
      const rows = document.querySelectorAll(".gmb-404-log-row");
      rows.forEach((row) => {
        const uri = (
          row.querySelector(".gmb-404-uri-text")?.innerText || ""
        ).toLowerCase();
        row.style.display = !q || uri.includes(q) ? "" : "none";
      });
    });
  }

  // Bulk Selection & Actions
  const selectAllRules = document.getElementById("gmb-select-all-rules");
  if (selectAllRules) {
    selectAllRules.addEventListener("change", function () {
      const checkboxes = document.querySelectorAll(".gmb-rule-checkbox");
      checkboxes.forEach((cb) => {
        if (cb.closest("tr").style.display !== "none") {
          cb.checked = selectAllRules.checked;
        }
      });
    });
  }

  const bulkApplyBtn = document.getElementById("gmb-bulk-apply-btn");
  if (bulkApplyBtn) {
    bulkApplyBtn.addEventListener("click", function () {
      const bulkAction = document.getElementById(
        "gmb-bulk-redirect-action",
      ).value;
      if (!bulkAction) {
        alert("Please select a bulk action.");
        return;
      }

      const selectedIds = Array.from(
        document.querySelectorAll(".gmb-rule-checkbox:checked"),
      ).map((cb) => cb.value);
      if (selectedIds.length === 0) {
        alert("Please select at least one redirection rule.");
        return;
      }

      if (
        bulkAction === "delete" &&
        !confirm(
          "Are you sure you want to delete " +
            selectedIds.length +
            " redirection rule(s)?",
        )
      ) {
        return;
      }

      bulkApplyBtn.disabled = true;
      const formData = new FormData();
      formData.append("action", "gmb_bulk_redirect_actions");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("bulk_action", bulkAction);
      selectedIds.forEach((id) => formData.append("ids[]", id));

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          bulkApplyBtn.disabled = false;
          if (data.success) {
            window.location.reload();
          } else {
            alert("Bulk action failed.");
          }
        })
        .catch((err) => {
          bulkApplyBtn.disabled = false;
          console.error(err);
        });
    });
  }

  // Bulk Import Pasted text
  const bulkImportSubmitBtn = document.getElementById(
    "gmb-bulk-import-submit-btn",
  );
  if (bulkImportSubmitBtn) {
    bulkImportSubmitBtn.addEventListener("click", function () {
      const textarea = document.getElementById("gmb-bulk-import-textarea");
      const matchType = document.getElementById("gmb-bulk-import-match").value;
      const text = textarea ? textarea.value.trim() : "";

      if (!text) {
        alert("Please enter at least one redirection rule to import.");
        return;
      }

      bulkImportSubmitBtn.disabled = true;
      const formData = new FormData();
      formData.append("action", "gmb_bulk_import_redirects_text");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("text", text);
      formData.append("match_type", matchType);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          bulkImportSubmitBtn.disabled = false;
          if (data.success) {
            alert(
              "Successfully imported " + data.data + " redirection rule(s)!",
            );
            window.location.reload();
          } else {
            alert("Import failed: " + (data.data || "Unknown error"));
          }
        })
        .catch((err) => {
          bulkImportSubmitBtn.disabled = false;
          alert("Error: " + err.message);
        });
    });
  }

  // Fallback behavior dropdown toggle custom URL wrap
  const fallbackSelect = document.getElementById(
    "gmb_ranker_fallback_behavior",
  );
  if (fallbackSelect) {
    fallbackSelect.addEventListener("change", function () {
      const customWrap = document.getElementById("gmb-fallback-url-wrap");
      if (customWrap) {
        customWrap.style.display = this.value === "custom" ? "block" : "none";
      }
    });
  }

  // Toggle redirection rule active status via AJAX
  const toggleRuleStatusBtns = document.querySelectorAll(
    ".gmb-toggle-rule-status-btn",
  );
  toggleRuleStatusBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = btn.getAttribute("data-id");
      btn.disabled = true;

      const formData = new FormData();
      formData.append("action", "gmb_toggle_redirect_rule");
      if (typeof gmb_ranker_admin !== "undefined") {
        formData.append(
          "nonce",
          gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
        );
      }
      formData.append("id", id);

      fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(formData),
      })
        .then((res) => res.json())
        .then((data) => {
          btn.disabled = false;
          if (data.success) {
            window.location.reload();
          } else {
            alert("Failed to toggle redirection rule status!");
          }
        })
        .catch((err) => {
          btn.disabled = false;
          console.error(err);
        });
    });
  });

  const clearLogsBtn = document.getElementById("gmb-clear-404-btn");
  if (clearLogsBtn) {
    clearLogsBtn.addEventListener("click", function () {
      if (confirm("Are you sure you want to delete all 404 detection logs?")) {
        const formData = new FormData();
        formData.append("action", "gmb_clear_404_logs");
        if (typeof gmb_ranker_admin !== "undefined") {
          formData.append(
            "nonce",
            gmb_ranker_admin.admin_nonce || gmb_ranker_admin.nonce,
          );
        }
        fetch(ajaxurl, {
          method: "POST",
          body: new URLSearchParams(formData),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              alert("Logs purged successfully!");
              window.location.reload();
            }
          });
      }
    });
  }
  const resetLinksBtn = document.getElementById("gmb-reset-links-options");
  if (resetLinksBtn) {
    resetLinksBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (
        confirm("Are you sure you want to reset all links options to default?")
      ) {
        const stripCheck = document.getElementById("gmb_strip_category_base");
        if (stripCheck) stripCheck.checked = false;

        const redirectCheck = document.getElementById(
          "gmb_redirect_attachments",
        );
        if (redirectCheck) redirectCheck.checked = true;

        const orphanInput = document.getElementById(
          "gmb_redirect_orphan_attachments",
        );
        if (orphanInput) orphanInput.value = "";

        const nofollowCheck = document.getElementById(
          "gmb_nofollow_external_links",
        );
        if (nofollowCheck) nofollowCheck.checked = true;

        const nofollowImgCheck = document.getElementById(
          "gmb_nofollow_image_links",
        );
        if (nofollowImgCheck) nofollowImgCheck.checked = false;

        const targetCheck = document.getElementById(
          "gmb_new_window_external_links",
        );
        if (targetCheck) targetCheck.checked = true;

        const affiliateInput = document.getElementById(
          "gmb_affiliate_link_prefixes",
        );
        if (affiliateInput) affiliateInput.value = "";

        const form = resetLinksBtn.closest("form");
        if (form) {
          form.submit();
        }
      }
    });
  }

  // Integrations: Toggle API Key Visibility
  const toggleKeyBtn = document.getElementById("gmb-toggle-key-visibility");
  const apiKeyInput = document.getElementById("gmb_ranker_api_key_input");
  if (toggleKeyBtn && apiKeyInput) {
    toggleKeyBtn.addEventListener("click", function () {
      if (apiKeyInput.type === "password") {
        apiKeyInput.type = "text";
        toggleKeyBtn.textContent = "Hide";
      } else {
        apiKeyInput.type = "password";
        toggleKeyBtn.textContent = "Show";
      }
    });
  }

  // Integrations: Switch Active AI Provider
  const aiProviderSelect = document.getElementById("gmb_ai_provider_select");
  if (aiProviderSelect) {
    aiProviderSelect.addEventListener("change", function () {
      const val = this.value;
      const sections = document.querySelectorAll(".gmb-ai-section");
      sections.forEach((s) => (s.style.display = "none"));
      const activeSec = document.getElementById("ai-section-" + val);
      if (activeSec) activeSec.style.display = "block";
    });
  }

  // Integrations: Generate IndexNow API Key
  const genIndexNowBtn = document.getElementById("gmb-generate-indexnow-key");
  const indexNowInput = document.getElementById("gmb_indexnow_key_input");
  if (genIndexNowBtn && indexNowInput) {
    genIndexNowBtn.addEventListener("click", function () {
      let hex = "";
      const chars = "0123456789abcdef";
      for (let i = 0; i < 32; i++) {
        hex += chars.charAt(Math.floor(Math.random() * chars.length));
      }
      indexNowInput.value = hex;
    });
  }

  // Integrations: Copy Webhook Endpoint URL
  const copyWebhookBtn = document.getElementById("gmb-copy-webhook-btn");
  const webhookInput = document.getElementById("gmb_webhook_endpoint");
  if (copyWebhookBtn && webhookInput) {
    copyWebhookBtn.addEventListener("click", function () {
      navigator.clipboard
        .writeText(webhookInput.value)
        .then(() => {
          const origText = copyWebhookBtn.textContent;
          copyWebhookBtn.textContent = "Copied!";
          setTimeout(() => {
            copyWebhookBtn.textContent = origText;
          }, 2000);
        })
        .catch(() => {
          webhookInput.select();
          document.execCommand("copy");
          alert("Webhook URL copied to clipboard!");
        });
    });
  }

  // Reset Local SEO options
  const resetLocalBtn = document.getElementById("gmb-reset-local-options");
  if (resetLocalBtn) {
    resetLocalBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to reset all Local SEO options to default?",
        )
      ) {
        const useMulti = document.getElementById("gmb-toggle-multi-locations");
        if (useMulti) useMulti.checked = false;

        const name = document.getElementById("gmb_local_business_name");
        if (name) name.value = "";

        const phone = document.getElementById("gmb_local_business_phone");
        if (phone) phone.value = "";

        const address = document.getElementById("gmb_local_business_address");
        if (address) address.value = "";

        const form = resetLocalBtn.closest("form");
        if (form) form.submit();
      }
    });
  }

  // Reset Metadata Templates
  const resetMetadataBtn = document.getElementById(
    "gmb-reset-metadata-options",
  );
  if (resetMetadataBtn) {
    resetMetadataBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to reset all Metadata options to default?",
        )
      ) {
        const postTitle = document.getElementById(
          "gmb_metadata_post_title_template",
        );
        if (postTitle) postTitle.value = "%title% %sep% %sitename%";

        const postDesc = document.getElementById(
          "gmb_metadata_post_desc_template",
        );
        if (postDesc) postDesc.value = "%excerpt%";

        const pageTitle = document.getElementById(
          "gmb_metadata_page_title_template",
        );
        if (pageTitle) pageTitle.value = "%title% %sep% %sitename%";

        const pageDesc = document.getElementById(
          "gmb_metadata_page_desc_template",
        );
        if (pageDesc) pageDesc.value = "%excerpt%";

        const form = resetMetadataBtn.closest("form");
        if (form) form.submit();
      }
    });
  }

  // Interactive Tag Insert Pills Click Handler
  document.querySelectorAll(".gmb-tag-insert-pill").forEach((pill) => {
    pill.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("data-target");
      const tag = this.getAttribute("data-tag");
      const input =
        document.getElementById(targetId) ||
        document.querySelector('[name="' + targetId + '"]');
      if (input) {
        const start =
          typeof input.selectionStart === "number"
            ? input.selectionStart
            : input.value.length;
        const end =
          typeof input.selectionEnd === "number"
            ? input.selectionEnd
            : input.value.length;
        const val = input.value;
        const prefix = start > 0 && val.charAt(start - 1) !== " " ? " " : "";
        const suffix = end < val.length && val.charAt(end) !== " " ? " " : "";
        const insertText = prefix + tag + suffix;
        input.value = val.substring(0, start) + insertText + val.substring(end);
        input.focus();
        const newPos = start + insertText.length;
        if (typeof input.setSelectionRange === "function") {
          input.setSelectionRange(newPos, newPos);
        }
      }
    });
  });

  // Universal Smooth Media Uploader (Single Instance Frame)
  (function () {
    var gmbMediaFrames = {};

    document.addEventListener(
      "click",
      function (e) {
        var btn = e.target.closest(".gmb-media-upload-btn");
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var targetId = btn.getAttribute("data-target");
        var inputField = document.getElementById(targetId);
        var previewContainer = document.getElementById(targetId + "_preview");

        if (typeof wp === "undefined" || !wp.media) {
          alert(
            "WordPress Media Library is unavailable. Please refresh the page.",
          );
          return;
        }

        if (gmbMediaFrames[targetId]) {
          gmbMediaFrames[targetId].open();
          return;
        }

        gmbMediaFrames[targetId] = wp.media({
          title: "Select or Upload Image",
          button: {
            text: "Use this Image",
          },
          multiple: false,
        });

        gmbMediaFrames[targetId].on("select", function () {
          var attachment = gmbMediaFrames[targetId]
            .state()
            .get("selection")
            .first()
            .toJSON();
          if (inputField) {
            inputField.value = attachment.url;
            inputField.dispatchEvent(new Event("input", { bubbles: true }));
            inputField.dispatchEvent(new Event("change", { bubbles: true }));
          }
          if (previewContainer) {
            previewContainer.style.display = "block";
            var img = previewContainer.querySelector("img");
            if (!img) {
              img = document.createElement("img");
              img.style.maxWidth = "150px";
              img.style.maxHeight = "100px";
              img.style.borderRadius = "4px";
              img.style.border = "1px solid #e2e8f0";
              img.style.objectFit = "contain";
              img.style.marginTop = "8px";
              previewContainer.appendChild(img);
            }
            img.src = attachment.url;
          }
        });

        gmbMediaFrames[targetId].open();
      },
      true,
    );
  })();

  // Reset Image SEO templates
  const resetImageBtn = document.getElementById("gmb-reset-image-options");
  if (resetImageBtn) {
    resetImageBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to reset all Image SEO options to default?",
        )
      ) {
        const alt = document.getElementById("gmb_image_seo_alt_template");
        if (alt) alt.value = "%filename%";

        const title = document.getElementById("gmb_image_seo_title_template");
        if (title) title.value = "%filename%";

        const form = resetImageBtn.closest("form");
        if (form) form.submit();
      }
    });
  }

  // Reset Security options
  const resetSecurityBtn = document.getElementById(
    "gmb-reset-security-options",
  );
  if (resetSecurityBtn) {
    resetSecurityBtn.addEventListener("click", function (e) {
      e.preventDefault();
      if (
        confirm(
          "Are you sure you want to reset all Security options to default?",
        )
      ) {
        const xmlrpc = document.querySelector(
          'input[name="gmb_seo_disable_xmlrpc"]',
        );
        if (xmlrpc) xmlrpc.checked = false;

        const wpVer = document.querySelector(
          'input[name="gmb_seo_hide_wp_version"]',
        );
        if (wpVer) wpVer.checked = false;

        const rest = document.querySelector(
          'input[name="gmb_seo_restrict_rest_api"]',
        );
        if (rest) rest.checked = false;

        const headers = document.querySelector(
          'input[name="gmb_seo_enable_security_headers"]',
        );
        if (headers) headers.checked = false;

        const form = resetSecurityBtn.closest("form");
        if (form) form.submit();
      }
    });
  }

  // Pagination for 404 Monitor logs table
  function init404Pagination() {
    const table = document.getElementById("gmb-404-logs-table");
    if (!table) return;

    const rows = Array.from(table.querySelectorAll(".gmb-404-log-row"));
    const itemsPerPage = 10;
    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / itemsPerPage);

    const paginationContainer = document.getElementById("gmb-404-pagination");
    const prevBtn = document.getElementById("gmb-404-prev-btn");
    const nextBtn = document.getElementById("gmb-404-next-btn");
    const infoText = document.getElementById("gmb-404-pagination-info");

    if (rows.length <= itemsPerPage) {
      if (paginationContainer) paginationContainer.style.display = "none";
      return;
    }

    if (paginationContainer) paginationContainer.style.display = "flex";

    function showPage(page) {
      currentPage = page;
      const start = (page - 1) * itemsPerPage;
      const end = page * itemsPerPage;

      rows.forEach((row, index) => {
        row.style.display = index >= start && index < end ? "" : "none";
      });

      // Update buttons status
      if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
        prevBtn.style.opacity = currentPage === 1 ? "0.5" : "1";
        prevBtn.style.cursor = currentPage === 1 ? "not-allowed" : "pointer";
      }
      if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.style.opacity = currentPage === totalPages ? "0.5" : "1";
        nextBtn.style.cursor =
          currentPage === totalPages ? "not-allowed" : "pointer";
      }

      // Update info text
      if (infoText) {
        const showingStart = start + 1;
        const showingEnd = Math.min(end, rows.length);
        infoText.innerText = `Showing ${showingStart} to ${showingEnd} of ${rows.length} entries`;
      }
    }

    if (prevBtn) {
      prevBtn.addEventListener("click", function () {
        if (currentPage > 1) {
          showPage(currentPage - 1);
        }
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener("click", function () {
        if (currentPage < totalPages) {
          showPage(currentPage + 1);
        }
      });
    }

    showPage(1);
  }

  // Initialize Security Hardening 1-Click Action
  function initSecurityHandlers() {
    const secBtn = document.getElementById("gmb-apply-recommended-sec-btn");
    if (secBtn) {
      secBtn.addEventListener("click", function (e) {
        e.preventDefault();
        if (
          !confirm(
            "Apply all recommended enterprise security hardening options now?",
          )
        )
          return;
        secBtn.disabled = true;
        secBtn.textContent = "Hardening Website...";

        fetch(ajaxurl, {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({
            action: "gmb_apply_recommended_security",
            nonce: window.gmb_ranker_admin.nonce,
          }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.success) {
              alert(
                data.data.message || "Security hardening applied successfully!",
              );
              window.location.reload();
            } else {
              alert(data.data.message || "Error applying security hardening.");
              secBtn.disabled = false;
              secBtn.textContent = "⚡ Apply Recommended Hardening";
            }
          })
          .catch((err) => {
            alert("Network error occurred.");
            secBtn.disabled = false;
            secBtn.textContent = "⚡ Apply Recommended Hardening";
          });
      });
    }

    // Filter tabs for security audit checks (All, Issues Only, Protected)
    const filterBtns = document.querySelectorAll(".gmb-sec-filter-btn");
    if (filterBtns.length > 0) {
      filterBtns.forEach((btn) => {
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          filterBtns.forEach((b) => {
            b.style.boxShadow = "none";
            b.style.fontWeight = "600";
            b.classList.remove("active");
          });
          this.style.boxShadow = "0 0 0 2px #3b82f6";
          this.style.fontWeight = "700";
          this.classList.add("active");

          const filter = this.getAttribute("data-filter");
          const rows = document.querySelectorAll(".gmb-sec-check-row");
          rows.forEach((row) => {
            if (filter === "all") {
              row.style.display = "flex";
            } else if (filter === "issues") {
              row.style.display =
                row.getAttribute("data-status") === "issue" ? "flex" : "none";
            } else if (filter === "passed") {
              row.style.display =
                row.getAttribute("data-status") === "passed" ? "flex" : "none";
            }
          });
        });
      });
    }
  }

  initSecurityHandlers();
  init404Pagination();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initGmbAdminApp);
} else {
  initGmbAdminApp();
}
