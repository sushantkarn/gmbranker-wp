# Enterprise WordPress Plugin Engineering & Audit Rules

This document defines the mandatory engineering standards, security controls, architectural guidelines, and prompt methodology for all AI agents working on this codebase.

---

## 1. Core Engineering Principle: Full Architecture Context

Never treat a requested file as an isolated script. Before modifying any file:
* Inspect the **complete file** first.
* Identify its class, interface, and function responsibilities.
* Trace all callers, consumers, and upstream/downstream dependencies.
* Trace related services, repositories, registries, REST/AJAX endpoints, UI views, cron jobs, actions, and condition evaluators.
* Trace persistence, option, and meta key ownership.
* Trace JavaScript contracts, localized data arrays, and AJAX action names.
* Trace third-party integrations (Rank Math, Yoast, AIOSEO, SEOPress, Google APIs).
* Trace migration, backward-compatibility, and multisite requirements.
* Determine the canonical architecture already present in the repository.

> [!IMPORTANT]
> Do NOT guess architecture. Do NOT invent new services, registries, repositories, interfaces, or APIs when canonical implementations already exist. Reuse existing architecture.

---

## 2. Root-Cause Engineering

Never perform superficial cleanup or symptom patching when the underlying architecture is flawed.
* Do **NOT** limit fixes to syntax cleanup, formatting, replacing functions, adding isolated `try/catch` or `null` checks, or suppressing warnings.
* **Identify Root Causes**:
  * If a View directly performs database persistence, move persistence to the canonical Repository/Service layer.
  * If a Controller contains domain logic, move the logic to the canonical Domain layer.
  * If multiple files duplicate the same registry, unify them behind a Single Source of Truth.
  * If an API client manages credentials, move credential ownership to the canonical Credential Provider.
  * If a Condition Evaluator trusts arbitrary inputs, enforce a strict Field/Operator Registry with fail-closed evaluation.
  * If a UI claims capabilities the backend cannot guarantee, fix the state model.

---

## 3. Single Source of Truth (SSOT)

Always search for existing sources of truth before adding new constants, defaults, arrays, registries, options, schemas, or configuration.

Avoid duplicate definitions for:
* Modules & Capabilities
* Post Types & Taxonomies
* Schema Types & JSON-LD Properties
* Metadata Keys (`_gmb_ranker_seo_title`, `_gmb_ranker_seo_description`, `_gmb_ranker_focus_keyword`)
* Robots Directives & Template Variables
* AI Providers & AI Models
* API Endpoints & Versions
* Condition Fields & Operators
* Automation Actions & Workflow States
* Admin Tabs, Settings, & Credentials

---

## 4. Generic & Business-Agnostic Site Architecture

All code must operate generically on **ANY** compatible WordPress website across any industry.

Never hardcode site-specific assumptions or business data:
* Do **NOT** hardcode business names, domains, countries, languages, currencies, phone numbers, addresses, or specific service locations.
* Do **NOT** hardcode specific custom post types, taxonomies, or industry-specific content structures in core services.
* Keep test and example data strictly isolated in unit tests or mock fixtures.

---

## 5. No Global Content Templates or Fabricated Data

* **Dynamic Content Strategy**: Do not force rigid H2 structures, fixed introduction patterns, fixed FAQ placement, or artificial word-count/keyword-density formulas. Content structure must be driven by search intent, topic/entity relationships, and user configuration.
* **Truthful UI & Data**: Never fabricate analytics, SEO scores, keyword rankings, SERP positions, traffic data, health scores, or API connection statuses. If data is unavailable, represent the actual state (`unavailable`, `disconnected`, `pending`, `error`).

---

## 6. Security-First Architecture

Treat every external input as untrusted (POST, GET, REST, AJAX, Cron, CLI, imported JSON/CSV, AI output, API responses, webhook payloads, database inputs).

### 6.1 Defense-in-Depth Separation
* **Sanitization** is NOT Validation.
* **Escaping** is NOT Authorization.
* A **Nonce** is NOT an Authorization Check.
* A **Capability Check** is NOT Object-Level Authorization.

### 6.2 Fail-Closed Execution
Security-sensitive components (authorization, condition evaluators, API boundaries, automation actions) MUST fail closed:
* Missing field $\rightarrow$ `false`
* Unknown operator $\rightarrow$ `false`
* Unauthorized resource $\rightarrow$ Reject / Abort
* Invalid payload $\rightarrow$ Reject / Return Error

---

## 7. Authorization & Object-Level Access Control

Every mutation and read operation must enforce proper authorization:
* Check capability (`current_user_can()`).
* Check object-level ownership and edit permissions (prevent IDOR).
* Validate Nonce / CSRF tokens.
* Verify site and multisite tenant context.
* Never rely on frontend JS visibility for security.

---

## 8. Thin UI, Thin Controllers, & Thin Actions

* **Views**: Render ViewModels, escape output, delegate interactions to dedicated JS files. Views must NOT perform DB queries or business logic.
* **Controllers & REST Handlers**: Coordinate requests, validate inputs against schemas, authorize, and delegate to Domain Services.
* **Automation Actions**: Validate parameters, resolve targets, authorize, delegate to Domain Services, and return normalized result arrays.
* **Condition Evaluators**: Implement deterministic, strongly-typed comparisons against registered field/operator schemas. Fail closed on unknown inputs.

---

## 9. AI Output Trust Boundary

AI output is inherently untrusted. Never allow raw AI output to:
* Execute PHP code, SQL queries, or arbitrary callbacks.
* Modify security settings, capabilities, or user roles.
* Create unvalidated redirects, canonical URLs, or schema markup.
* Mutate arbitrary posts or options without schema validation and authorization.

---

## 10. Required Workflow for Every Task

1. **PHASE 1 — DISCOVER**: Inspect complete files, callers, consumers, registries, persistence, JS contracts, and tests.
2. **PHASE 2 — MODEL**: Identify ownership, data flows, trust boundaries, and canonical sources of truth.
3. **PHASE 3 — AUDIT**: Check security, correctness, validation, authorization, multisite isolation, performance, and backward compatibility.
4. **PHASE 4 — ROOT-CAUSE FIX**: Refactor the owning domain service/repository rather than patching symptoms.
5. **PHASE 5 — CONTRACT CHECK**: Update all callers, consumers, hooks, and JS contracts to ensure zero breaking changes.
6. **PHASE 6 — TEST & VERIFY**: Run static analysis (`php -l`) and git status checks before completing.
