=== GMB Ranker SEO Automation ===
Contributors: sushantkarn
Donate link: https://gmbranker.org
Tags: seo, local seo, google my business, automation, schema
Requires at least: 5.8
Tested up to: 6.7
Stable tag: 2.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automate local SEO, schema markup, titles & meta descriptions, instant indexing, and internal linking for WordPress.

== Description ==

GMB Ranker SEO Automation is a comprehensive, lightweight WordPress plugin designed to elevate your site's search visibility and local rankings. By bridging your WordPress database directly with advanced SEO workflows, GMB Ranker automates tedious technical tasks so you can focus on growing your traffic.

Key Features:
* **NeuronWriter-Style AI Analysis**: Real-time 3-step intent & entity analyzer that optimizes your Focus Keyword, SEO Title (CTR hooks), Meta Description (PAS framework), and Schema.
* **Instant Indexing**: Automatically notify Google Indexing API and IndexNow (Bing/Yandex) whenever new content is published or updated.
* **Local Business & FAQ Schema**: Dynamically generate valid JSON-LD entity structures to earn Google Rich Snippets.
* **Smart Redirection Manager**: Capture 404 broken links in real time and automatically map 301 permanent redirects.
* **XML Sitemaps**: Dynamic rewrite-driven XML sitemap engine with search engine ping integration.
* **SILO & Contextual Internal Links**: Automatically map and insert relevant anchor links between published pages.

== External services ==

This plugin integrates with the following 3rd party external services to provide AI analysis, search indexing, local sync, and performance reporting. No user data is transmitted without explicit action or setting configuration.

* **OpenRouter AI API**
  * **Service Usage**: Generates AI SEO content suggestions, title tags, and meta descriptions.
  * **Data Transmitted**: Post title, page content snippet, target keyword, site URL.
  * **Terms of Service**: https://openrouter.ai/terms
  * **Privacy Policy**: https://openrouter.ai/privacy

* **Groq AI API**
  * **Service Usage**: Provides ultra-fast AI text completions and SERP entity analysis.
  * **Data Transmitted**: Post title, snippet, target keyword.
  * **Terms of Service**: https://groq.com/terms-of-service/
  * **Privacy Policy**: https://groq.com/privacy-policy/

* **Google Search Console & OAuth API**
  * **Service Usage**: Connects your site to retrieve Search Console performance analytics and site indexing status.
  * **Data Transmitted**: OAuth authentication tokens, site URL.
  * **Terms of Service**: https://policies.google.com/terms
  * **Privacy Policy**: https://policies.google.com/privacy

* **Google Indexing API**
  * **Service Usage**: Notifies Google immediately when posts or pages are published, updated, or removed.
  * **Data Transmitted**: Published post URL, post update timestamp.
  * **Terms of Service**: https://policies.google.com/terms
  * **Privacy Policy**: https://policies.google.com/privacy

* **Google Business Profile API**
  * **Service Usage**: Syncs local business updates and GMB local post content.
  * **Data Transmitted**: Business updates, location parameters, media URLs.
  * **Terms of Service**: https://policies.google.com/terms
  * **Privacy Policy**: https://policies.google.com/privacy

* **IndexNow API**
  * **Service Usage**: Submits updated URLs to the IndexNow protocol (Bing, Yandex, Seznam).
  * **Data Transmitted**: Site URL, IndexNow API key, updated post URL.
  * **Terms of Service**: https://www.indexnow.org/terms
  * **Privacy Policy**: https://www.indexnow.org/privacy

* **GMB Ranker Cloud API**
  * **Service Usage**: Provides license validation, rank tracking sync, and sitemap ping services.
  * **Data Transmitted**: Site domain URL, plugin version.
  * **Terms of Service**: https://gmbranker.org/terms
  * **Privacy Policy**: https://gmbranker.org/privacy

== Installation ==

1. Upload the `gmb-ranker-seo-automation` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **GMB Ranker SEO** in the admin sidebar to configure your settings, AI provider keys, and search indexing options.

== Frequently Asked Questions ==

= Does this plugin require an external API key? =
Core features like local schema generation, XML sitemaps, 404 redirects, and focus keyword auditing work out of the box without any external API keys. AI optimization features require an API key from OpenRouter or Groq.

= Is it compatible with Yoast SEO or Rank Math? =
Yes! GMB Ranker seamlessly reads and updates Yoast SEO (`_yoast_wpseo_title`) and Rank Math (`rank_math_title`) meta fields when present.

== Changelog ==

= 1.0.0 =
* Initial public release on WordPress.org.
