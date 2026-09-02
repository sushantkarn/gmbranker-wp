=== GMB Ranker SEO Automation ===
Contributors: gmbranker
Tags: seo, link injector, schema, sitemaps, indexing
Requires at least: 5.8
Tested up to: 7.1
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Connects WordPress content and SEO metadata to GMB Ranker for automated optimization, indexing, redirects, schema, and internal linking.

== Description ==

GMB Ranker SEO Automation is a lightweight, high-performance standalone connector plugin. It exposes secure endpoints to allow the GMB Ranker engine to orchestrate and execute SEO experiments directly on your WordPress website.

= Key Features =
* **Contextual Internal Linking**: Autopilot injection of internal links based on semantic analysis.
* **Metadata Sync**: Auto-update Yoast / Rank Math Meta Titles and Descriptions.
* **Instant Indexing**: Notify Search Engines of new and updated content (IndexNow support).
* **Business Schema Integration**: Custom FAQ and LocalBusiness Schema injections.
* **301 Redirection Manager**: Track 404 monitors and commit instant redirect rules.
* **Dynamic Sitemaps**: Dynamic XML sitemap write engine.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gmb-ranker-seo-automation` directory, or install the plugin through the WordPress plugins screen directly by uploading the ZIP file.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to **Settings -> GMB Ranker** in your WordPress admin dashboard to copy your API key.
4. Paste the API key into GMB Ranker to authorize the connection.

== Frequently Asked Questions ==

= Is this plugin secure? =
Yes. All REST API requests require the `X-GMB-Ranker-Key` authentication header matching your site's unique secret key.

= Does it affect site performance? =
No. The plugin is extremely lightweight and executes only when called via targeted REST requests, utilizing native WordPress hooks.

== Changelog ==

= 2.1.0 =
* Standalone repository migration.
* Added `/page-content` REST endpoint.
* Added single-click Auto Login (`easy_wp_connect`) helper authentication.
