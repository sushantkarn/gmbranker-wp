# Enterprise WordPress Plugin Engineering & Architecture Rules

This document defines the mandatory engineering standards, security controls, architectural guidelines, preservation policies, and prompt methodology for all AI agents working on this codebase.

---

## 1. PRESERVE EXISTING FUNCTIONALITY

Before changing any file, inspect what the file currently provides and what other files depend on it.

Never remove, rename, replace, or simplify existing:
- Metaboxes
- Fields & Form Controls
- Tabs & Sub-navigation
- Sections & Layout Panels
- Buttons & Action Controls
- Admin Settings & Configuration Options
- AJAX Actions & Handlers
- Hooks (`add_action`, `add_filter`)
- Custom Post Types & Taxonomies
- Third-Party Integrations (Rank Math, Yoast, AIOSEO, SEOPress, Google APIs)
- Services, Repositories, & Registries
- API Contracts & Data Response Schemas
- JavaScript Behavior & DOM Contracts
- CSS Classes, IDs, & Data Attributes
- Saved Metadata & Transient Caches
- Database Options & Schema Migrations
- Backward Compatibility Layer

> [!CAUTION]
> NEVER optimize for "less code" or "cleaner UI" at the expense of existing features. If something appears duplicated or obsolete, trace ALL callers and consumers before making any change.

---

## 2. NEVER MODIFY A FILE IN ISOLATION

Before editing a file, inspect:
* Its parent controller, interfaces, services, and repositories.
* Admin views, metaboxes, and rendering logic.
* JS consumers, localized data variables, and DOM event listeners.
* AJAX handlers, REST endpoints, and action nonces.
* Option/meta keys, database schema, and migration scripts.
* Other classes calling it and classes it calls.

Understand the complete data flow before changing any implementation.

---

## 3. GLOBAL ARCHITECTURE FIRST

The agent must reason about the entire system architecture:
```
Plugin Bootstrap
→ Module Manager / Module Registry
→ Admin / API / Automation Registries
→ Capability & Authorization Registry
→ Settings Registry
→ Integration & Provider Registries
→ Domain Services
→ Repositories & Data Access
→ AJAX / REST Endpoints
→ View Models & Admin Views
→ JavaScript & Event Listeners
→ Persistence Layer
```
Do NOT introduce a secondary architecture inside an existing module. Always reuse existing canonical services, registries, repositories, validators, and providers.

---

## 4. SINGLE SOURCE OF TRUTH (SSOT)

Before adding new logic, search the repository for existing implementations. Never duplicate:
* SEO Scoring & Analysis Engines
* Metadata Models & Storage Keys
* Schema Registries & JSON-LD Builders
* Post Type & Taxonomy Registries
* AI Provider & Model Registries
* Integration & Credential Registries
* Condition & Action Registries
* Settings, Validation, & URL Helpers

If a canonical implementation already exists, consume it.

---

## 5. NO HARDCODED SITE OR BUSINESS ASSUMPTIONS

All code must remain 100% generic for arbitrary WordPress websites, industries, languages, and countries.

Never hardcode assumptions about:
* Business names, domains, or phone numbers.
* Specific geographic locations, regions, or countries.
* Specific languages, locales, or search engine TLDs.
* Industry-specific post types, taxonomies, or content templates.

Everything that varies must be derived from canonical settings, registries, runtime context, or user input.

---

## 6. NO FABRICATED DATA & TRUTHFUL UI

Never add fallback or demo values merely to make the UI appear complete:
* Do NOT fabricate SEO scores, optimization potentials, ranking positions, SERP analytics, or traffic data.
* Do NOT fabricate recommendations, schema properties, reviews, locations, progress percentages, or connection statuses.
* If real backend data is unavailable, accurately display the actual state (`unavailable`, `loading`, `error`, `not_configured`).

---

## 7. NEVER HIDE BACKEND ERRORS AS EMPTY STATES

Do not convert API failures, missing data, or exceptions into silent empty arrays (`[]`) or default success responses. Errors must be captured, logged safely, and reported truthfully at the correct architectural layer.

---

## 8. AI IS NEVER A SOURCE OF TRUTH

AI output is untrusted external input. Never allow AI output to directly:
* Mutate WordPress posts or metadata without domain validation.
* Generate unvalidated redirects, canonical URLs, or JSON-LD schema.
* Execute PHP code, SQL queries, or arbitrary callbacks.
* Modify capabilities, user permissions, or system settings.

All AI recommendations must flow through:
`AI Output → Parser → Schema Validation → Domain Validation → Authorization → User Approval (where required) → Application Service → Persistence`

---

## 9. CLIENT IS NEVER A TRUST BOUNDARY

Never trust hidden form inputs, JS variables, checkbox states, client-side scores, or client-provided IDs. The server must authorize every read/write operation and resolve authoritative state independently.

---

## 10. PRESERVE METABOXES & CONDITIONAL FUNCTIONALITY

* **Metabox Inventory**: The agent must never remove or hide existing post metaboxes, metabox tabs, or sidebar panels.
* **Post-Type Genericity**: Preserve custom post type and WooCommerce support across all metaboxes and services. Respect WordPress registered post types dynamically.
* **Conditional Logic Awareness**: A feature hidden under certain post types or capabilities is conditional—NOT obsolete. Understand visibility rules before refactoring.

---

## 11. DATABASE, SETTINGS, & META KEY BACKWARD COMPATIBILITY

Before changing any database option (`get_option`), post meta key (`get_post_meta`), term meta, or user meta:
* Search every reader, writer, fallback, migration, and AJAX handler.
* Never rename or orphan stored metadata without a migration and backward-compatibility adapter strategy.

---

## 12. CONTRACT COMPATIBILITY & SECURITY CONTROL

Every AJAX handler, REST endpoint, action, and filter hook MUST maintain backwards compatibility:
* Preserve capability checks (`current_user_can`).
* Preserve CSRF nonces (`check_ajax_referer`).
* Preserve object-level authorization (prevent IDOR).
* Escape output according to context (`esc_html`, `esc_attr`, `esc_url`, `wp_json_encode`).

---

## 13. MANDATORY WORKFLOW FOR EVERY CODE TASK

1. **PHASE 1 — INVENTORY & TRACE**: Understand the requested change, locate target files, trace callers, dependencies, JS contracts, metaboxes, and database schemas.
2. **PHASE 2 — ARCHITECTURAL ALIGNMENT**: Identify canonical registries, services, and sources of truth.
3. **PHASE 3 — ROOT-CAUSE REFACTOR**: Fix the owning domain service/repository rather than patching symptoms or deleting code.
4. **PHASE 4 — FUNCTIONALITY PRESERVATION CHECK**: Verify every metabox, tab, field, setting, AJAX action, and hook remains intact and functional.
5. **PHASE 5 — SYNTAX & STATIC ANALYSIS**: Validate PHP syntax (`php -l`) and git status before declaring completion.
