# RFC-010: Laminas MVC to Mezzio Migration

## Summary

Replace the discontinued Laminas MVC framework (and its satellite packages) across the three VOL applications with [Mezzio](https://docs.mezzio.dev/) + PSR-15 middleware, delivered incrementally as a strangler-fig migration: each app runs a dual-stack bootstrap with per-route feature toggles, so routes move one batch at a time and rollback is a toggle flip. The actively maintained standalone Laminas components (servicemanager, form, view, validator, filter, router) are retained — only the MVC glue is replaced.

## Problem

The Laminas Technical Steering Committee has discontinued the Laminas MVC framework and its satellites. Five packages in the VOL estate are directly affected:

-   `laminas/laminas-mvc`
-   `laminas/laminas-mvc-i18n` (already removed)
-   `laminas/laminas-mvc-plugin-prg` (already removed)
-   `laminas/laminas-mvc-plugin-flashmessenger`
-   `lm-commons/lmc-rbac-mvc`

Security patches continue until **2028-12-31** (aligned with PHP 8.4 EOL) and there will be **no PHP 8.5 support**. Doing nothing means VOL is pinned below PHP 8.5 and, from 2029, runs on an unpatched framework.

The coupling is broad:

-   All three apps boot through `Laminas\Mvc\Application`; cross-cutting concerns (logging, auth, CSRF, cookies, route-param hydration) hang off MVC events.
-   456 frontend controllers (153 selfserve, 300 internal), of which 453 inherit `olcs-common`'s `AbstractOlcsController` / `Lva\AbstractController` — both of which override `onDispatch(MvcEvent)`. The shared base is the gate: nothing frontend can move until it is framework-agnostic.
-   The in-tree libraries `lib/olcs-common`, `lib/olcs-auth` and `lib/olcs-logging` require `laminas-mvc` directly; `lib/olcs-common` and `lib/olcs-auth` also pull the flash-messenger plugin and `lmc-rbac-mvc`.
-   The API's REST surface is small (~4 controllers) and already delegates to the framework-agnostic CQRS domain layer.

## Proposal

Adopt **Mezzio + PSR-15** as the replacement HTTP runtime, migrated route-by-route behind feature toggles.

### Why Mezzio

-   **Actively maintained, same ecosystem.** Mezzio is the Laminas project's own middleware runtime (3.28.1 released June 2026, supporting PHP 8.2–8.5, with a 4.0 branch in development). It already clears the PHP 8.5 wall that laminas-mvc never will.
-   **Smallest rewrite surface.** Mezzio reuses everything we keep: the ServiceManager v3 container, laminas-view, laminas-form, laminas-validator/filter, and — via `mezzio-laminasrouter` — the existing route definitions verbatim. Only the dispatch glue (controllers, MVC event listeners, controller plugins) changes.
-   **The RBAC gap has closed.** `lmc-rbac-mezzio` reached a stable 1.0.1 in May 2026 (PHP 8.3–8.5), giving a like-for-like replacement for `lmc-rbac-mvc`'s route guards. Both wrap the same `lm-commons/rbac` core, so roles and permissions are unchanged.
-   **The monorepo makes it cheaper.** A strangler-fig migration requires repeated lock-step changes to the shared libraries and the apps. Since the library absorption (RFC-011), `lib/olcs-common`, `lib/olcs-auth` and `lib/olcs-logging` are in-tree, so every such change is a single atomic PR with library and app CI in one pipeline run — no coordinated releases, and rollback is a plain revert.

### Alternatives considered

-   **Do nothing / fork laminas-mvc.** A dead end: the 2028-12-31 patch horizon and the PHP 8.5 block both stand, and a community fork of an abandoned framework transfers the maintenance burden to us at its worst point.
-   **Symfony (full framework or HttpKernel).** Would discard the ServiceManager container, laminas-form, laminas-view and every route definition — turning a glue swap into a multi-year platform rewrite of 456 controllers plus their forms and templates.
-   **Slim 4 (or another PSR-15 micro-framework).** PSR-15 like Mezzio, but with no laminas-router bridge, no view/form integration and no RBAC package — the same interop work as Mezzio with fewer reused parts.
-   **Big-bang rewrite to Mezzio.** Rejected on risk: a single cutover of three apps and 456 controllers cannot be meaningfully staged, tested in production, or rolled back.

### Migration mechanism (strangler fig)

-   Each app's `public/index.php` dispatches a request to either the MVC stack or the Mezzio pipeline based on a **per-route feature toggle** (MVC default). Both stacks share the same ServiceManager instance, so services, repositories and configuration resolve identically from either side. Rollback at any point, per route, is a toggle flip.
-   Both stacks share one **session store** (the existing `Laminas\Session\Container` namespaces), so a user's session survives switching stacks mid-journey during the interop window.
-   **API first.** The API is the pilot: its controllers are thin delegations to the CQRS command/query handler managers, so there is almost no logic to rewrite, and its identity is resolved per-request from the JWT `Authorization` header — token-based, not session-based — so the API's authorization is independent of the frontends' and the three apps do not need to migrate together. With only ~4 controllers the API can optionally cut over big-bang behind a single app-level toggle.
-   **Decouple the shared controller base, then batch the frontends.** The shared logic in `AbstractOlcsController` / `Lva\AbstractController` is extracted into framework-agnostic services (feature-toggle checks, action resolution, resource-conflict handling, section translation, flash, CSRF). Two thin delivery adapters share those services — the existing MVC base and a new PSR-15 `AbstractOlcsHandler` — with a common interface keeping type safety while both exist. Frontend controllers then migrate in per-route batches (route prefix / feature area / complexity tier), each batch soaking in production before the next.

### Key component decisions

-   **FlashMessenger: in-house session-backed adapter, not `mezzio-flash`.** The discontinued plugin's storage is just `Laminas\Session\Container` (maintained). An in-house adapter in `lib/olcs-common` keeps the `FlashMessengerHelperService` API unchanged (~1900 call sites untouched) and is readable from both stacks during interop. `mezzio-flash` was rejected because it only functions inside a Mezzio pipeline and its store cannot be read by the MVC stack mid-migration; VOL's same-request "current messages" and `prominent-error` semantics aren't flash-hop semantics anyway. This lands early, on the current MVC stack, and removes a discontinued package outright.
-   **RBAC: a stack-neutral interface, `lmc-rbac-mezzio` for the frontends only.** An `AuthorizationServiceInterface` decouples the 49 FormService classes and ~106 `isGranted()` call sites from `LmcRbacMvc`; the internal app's route guards port to `lmc-rbac-mezzio` middleware. The API keeps using the framework-agnostic `AuthorizationService` with JWT identity fed from the PSR-7 request — it needs no guard middleware at all.
-   **Logging: `LogRequest`/`LogError` become PSR-15 middleware** in `lib/olcs-logging`, shipped alongside the MVC listeners so each stack has its own path during interop.
-   **Auth: `lib/olcs-auth`'s MVC dispatch and `RedirectStrategy` become authentication + redirect middleware**, with the legacy MVC entry points retained (and feature-toggled) through an extended soak, since login/logout has the highest blast radius.

### Sequencing

Work already landed that this plan builds on:

-   `laminas-mvc-plugin-prg` and `laminas-mvc-i18n` removed; `dvsa/laminas-config-cloud-parameters` no longer dev-requires `laminas-mvc` (v2.1.0).
-   PHP 8.4 + PHPUnit 13 upgrade (VOL-6520) — the platform prerequisite for the migration window.
-   Library absorption into the monorepo — enables atomic library+app changes.
-   `doctrine-orm-module`/`doctrine-module` removal (in flight, separate workstream) — removes Doctrine's own `laminas-mvc` requirement without any work in this plan.

Remaining work is ticketed in Jira (18 dependency-linked tickets), ordered **isolation-first**: the pieces that are pure refactors on the current MVC stack — the flash adapter swap, the shared-base decoupling, the RBAC interface, and the view-helper cleanups — run first and in parallel, needing no Mezzio at all. The API bootstrap + pilot follows as the first vertical slice of the dual-stack mechanism, then the frontend bootstraps, the remaining untangling (route-param listeners, controller plugins, CSRF/error middleware, sessions), the batched frontend migration, and finally teardown. The final cleanup — deleting the MVC bootstrap paths and removing `laminas-mvc`, `lmc-rbac-mvc` and the MVC-only dev tools from every composer.json — is gated on all routes running Mezzio in production for at least two weeks with zero rollbacks.

### Out of scope

-   `laminas-servicemanager` v3 → v4 (blocked by `mezzio-laminasviewrenderer` 2.x and `lmc-rbac-mezzio` 1.x; a later epic).
-   Long-term replacement of `laminas-form` / `laminas-view` (both actively maintained standalone).
-   The Doctrine module replacement (its own workstream, already in flight).

### Risks

-   **Interop parity** — session and identity must behave identically across stacks; mitigated by the shared session store, per-route toggles limiting blast radius, and parity tests per migrated route.
-   **Auth changes** (`lib/olcs-auth`) touch login/logout; mitigated by feature-toggling the auth pipeline and an extended production soak.
-   **Internal's dynamic `route.param.*` listener system** has no direct Mezzio analogue; it is re-modelled as router-adjacent middleware composing per-handler pipelines, with the MVC listeners untouched until cutover.
-   **Deadline** — security patches end 2028-12-31; the batched frontend migration is the long tail and its burn-down should be tracked, with batch sizes increased if it falls behind.
