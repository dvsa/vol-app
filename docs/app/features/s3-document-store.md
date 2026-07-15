---
sidebar_position: 20
title: S3 Document Store & Bucket Browser
---

# S3 Document Store & Bucket Browser

Two related capabilities for the document store:

1. A native **S3 backend** for the document store — a per-environment, toggleable alternative to the WebDAV/SabreDAV path.
2. A super-admin in-app **S3 bucket browser** that replaces direct SMB/NFS mount access to the store.

## Overview

- **S3 document store** (`app/api`) — `S3DocumentStore` implements the existing `DocumentStoreInterface` (read/write/remove). The `ContentStore` service is built by `DocumentStoreFactory`, which picks WebDAV or S3 from `document_share.backend`. Switching backend is a one-line config change; any value other than `s3` (including an unresolved placeholder) falls back to WebDAV, so it is instantly reversible.
- **Bucket browser** (`app/internal` admin UI + `app/api` handlers) — system-admins browse the bucket as folders, download any object, and (when separately enabled) overwrite objects. It lists **straight from S3 as the source of truth**, decoupled from the `document` table, and proxies bytes through the API (audited; no presigned URLs).

Both reuse the existing `Aws\S3\S3Client` and share a small `StreamsS3Objects` trait for the GetObject/PutObject streaming.

## Configuration

Under `document_share` in `app/api/config/autoload/config.global.php`, resolved per environment from SSM placeholders. The parameters are set in `vol-terraform` (`etc/env_eu-west-1_<env>.tfvars`, under `environment_application_parameters`) and published to `/applicationparams/<env>/<key>` — see [RFC-008](../../rfc/rfc-008-parameter-and-secrets-config-change-process.md) for the parameter-change process.

| Config key                     | SSM parameter                       | Value                                                                                                                       |
| ------------------------------ | ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------- |
| `document_share.backend`       | `olcs_document_store_backend`       | `webdav` (default) or `s3`. Any non-`s3` value → WebDAV. Used only by the document-store migration; the browser ignores it. |
| `document_share.s3.bucket`     | `olcs_document_store_s3_bucket`     | Per-env bucket `<project>-<env>-<component>-sabredav` (e.g. `olcs-devappdev-base-sabredav`).                                |
| `document_share.s3.key_prefix` | `olcs_document_store_s3_key_prefix` | Root prefix the objects live under (e.g. `migration/olcs`); may be empty (= whole bucket).                                  |

## Feature toggles

DB-backed toggles, seeded **inactive** by `olcs-etl` (patch `7.6.0`), gating both the API handlers and the admin UI:

| Toggle                        | Gates                                                 |
| ----------------------------- | ----------------------------------------------------- |
| `s3_bucket_browser`           | The browser as a whole — browse + download.           |
| `s3_bucket_browser_overwrite` | Object overwrite/upload (requires the above as well). |

:::warning Overwrite prerequisites
Leave `s3_bucket_browser_overwrite` **inactive** until both are true: (1) the WebDAV→S3 cutover is complete (so S3-native writes are not clobbered by the EBS→S3 sync), and (2) bucket versioning is enabled — an overwrite is otherwise irreversible.
:::

## Bucket browser (admin UI)

- **Where** — Admin dashboard → _Document store browser_ (`/admin/s3-browser`), system-admins only (`PERMISSION_SYSTEM_ADMIN`).
- **What** — delimiter-based folder navigation with a breadcrumb, download any object, and overwrite an object at a raw key (when the overwrite toggle is on).
- **Controls** — centralised, authenticated, system-admin-gated, audit-logged, and behind a runtime kill-switch (the master toggle). Downloads are always served as `attachment` with `X-Content-Type-Options: nosniff`; uploads are AV-scanned.

## Operational notes

- **Cutover / rollback** — flip `olcs_document_store_backend` to `s3` for an environment in `vol-terraform`; set it back to `webdav` to revert.
- **Decoupling** — the browser works on raw S3 keys and does **not** touch the `document` table; an overwrite deliberately leaves document metadata unchanged.

## Technical reference

**API — `app/api`**

- `module/DocumentShare/src/Service/S3DocumentStore.php` (+ `S3DocumentStoreFactory.php`)
- `module/DocumentShare/src/Service/DocumentStoreFactory.php` — backend selector, wired as `ContentStore` in `module/Api/config/module.config.php`
- `module/DocumentShare/src/Service/S3BucketBrowser.php` (+ `S3BucketBrowserFactory.php`)
- `module/DocumentShare/src/Service/StreamsS3Objects.php` — shared streaming trait
- `module/Api/src/Domain/QueryHandler/Document/{BucketBrowserList,BucketBrowserDownload}.php`, `module/Api/src/Domain/CommandHandler/Document/BucketBrowserOverwrite.php`
- `module/Api/src/Entity/System/FeatureToggle.php` — toggle constants

**Internal — `app/internal`**

- `module/Admin/src/Controller/S3/BrowserController.php`; route `admin-dashboard/admin-s3-browser` in `module/Admin/config/module.config.php`

**Cross-repo**

- `olcs-transfer` — `Query/Document/{BucketBrowserList,BucketBrowserDownload}`, `Command/Document/BucketBrowserOverwrite` (+ routes)
- `olcs-common` — `Common\FeatureToggle` constants (frontend)
- `olcs-etl` — `patches/7.6.0/VOL-feature-toggle-s3-bucket-browser.sql` (toggle seed)
- `vol-terraform` — `etc/env_eu-west-1_<env>.tfvars` (SSM parameters)
