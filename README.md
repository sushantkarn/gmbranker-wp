# GMB Ranker SEO Automation WordPress Plugin

[![WordPress Version](https://img.shields.io/badge/WordPress-%3E%3D%205.8-blue.svg)](https://wordpress.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/Version-1.0.0-blue.svg)](https://github.com/sushantkarn/gmbranker-wp)

A lightweight, standalone WordPress connector plugin that bridges your website's database and pages with the **GMB Ranker Engine**. It allows you to run zero-config SEO experiments, inject internal links, publish sitemaps, manage redirects, and automate search indexing.

---

## 📂 Project Structure

```bash
gmbranker-wp/
├── assets/                  # CSS, JS, and Image resources
│   ├── css/                 # Admin panel and custom layout styles
│   ├── js/                  # Interactive widgets and controls
│   └── images/              # Logos and media resources
├── includes/                # Main class definitions & logic
│   ├── class-gmb-ranker-seo-core.php             # Core bootstrap entry
│   ├── class-gmb-ranker-seo-rest-api.php         # REST endpoints routing
│   ├── class-gmb-ranker-seo-metadata.php         # Metadata Yoast/RankMath integration
│   ├── class-gmb-ranker-seo-links.php            # Internal link injection
│   ├── class-gmb-ranker-seo-sitemaps.php         # XML Sitemap generation
│   ├── class-gmb-ranker-seo-redirects.php        # 404 logs & 301 rules
│   ├── class-gmb-ranker-seo-schema.php           # Local Schema markup
│   ├── class-gmb-ranker-seo-image.php            # Alt Text optimization
│   ├── class-gmb-ranker-seo-security.php         # Request validation & guards
│   ├── class-gmb-ranker-seo-admin.php            # Admin Settings UI
│   ├── class-google-preferred-source.php         # Content citation checks
│   ├── class-gmb-ranker-seo-db-tools.php         # Database health tools
│   ├── class-gmb-ranker-seo-role-manager.php     # RBAC capabilities
│   ├── class-gmb-ranker-seo-instant-indexing.php # IndexNow & API submission
│   ├── class-gmb-ranker-seo-local.php            # Local SEO features
│   └── class-gmb-ranker-seo-analysis.php         # SEO Auditing helpers
├── languages/               # Translation and localization files (.po, .mo, .pot)
├── gmb-ranker-seo-automation.php                 # Main entrypoint
├── readme.txt               # WordPress.org Readme
└── README.md                # GitHub Readme
```

---

## ⚡ Features

- **Contextual Internal Linking**: Autopilot injection of natural, anchor-based links based on GMB Ranker's semantic page auditing.
- **Title & Description Optimization**: Auto-updates Rank Math (`rank_math_title`) or Yoast SEO (`_yoast_wpseo_title`) meta tags.
- **Local Business Schema**: Dynamically builds and injects valid JSON-LD `LocalBusiness` and `FAQPage` schema markups into page headers.
- **Redirection Manager**: Captures 404 errors in real-time and registers permanent 301/302 redirects automatically.
- **XML Sitemaps**: Dynamic XML write engine to publish dynamic search indexes.
- **Single-Click Auto Login (`easy_wp_connect`)**: Safely logs in authorized GMB Ranker administrators directly from the dashboard.

---

## 🔑 Security & Authentication

All API operations utilize a secure REST namespace to prevent global database access.

### 1. API Token Authentication
All GMB Ranker HTTP requests must pass a valid secret token in the request headers:
```http
X-GMB-Ranker-Key: [your_gmb_ranker_api_key]
Content-Type: application/json
```

### 2. File & Directory Safeguards
To prevent arbitrary file executes, every PHP source file begins with an execution guard check:
```php
if (!defined('ABSPATH')) {
    exit;
}
```

---

## 📡 REST API Namespace: `/wp-json/gmb-ranker/v1`

### Endpoints Reference

| Endpoint | Method | Params | Description |
| :--- | :--- | :--- | :--- |
| `/handshake` | `GET` | None | Verify credentials and handshake. |
| `/seo-data` | `GET` | `page`, `per_page` | Fetch page titles, meta descriptions, and indexing statuses. |
| `/update-seo` | `POST` | Body payload | Updates meta keys or page content HTML (compatible with Elementor layouts). |
| `/page-content` | `GET` | `id` | Fetches raw content HTML for a singular post or page. |
| `/redirects` | `GET` / `POST` | Body payload | Fetches 404 monitors log entries or registers 301/302 redirect rules. |
| `/sitemap` | `POST` | None | Triggers writing dynamically generated XML sitemaps to disk. |
| `/content-ai` | `POST` | Body payload | Analyzes and optimizes content suggestions using vision/AI. |

---

## 🛠️ Installation & Setup

1. **Download ZIP**: Download the latest release ZIP from the repository.
2. **Upload**: Upload it to your WordPress admin panel via **Plugins -> Add New -> Upload Plugin**.
3. **Activate**: Click **Activate**.
4. **Link**: Go to **Settings -> GMB Ranker** in your WordPress dashboard, copy your API key, and paste it into the GMB Ranker dashboard.

---

## 📄 License
This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
