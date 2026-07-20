---
sidebar_position: 30
title: Retrieve via Link
---

# Retrieve via Link

Secure document delivery by **link instead of email attachment**, for documents that are too
large for GOV.UK Notify's 2&nbsp;MB attachment cap or too sensitive to sit in an inbox. The
recipient clicks an unguessable link and — depending on the flow — either downloads immediately or
enters a one-time code first. **Publications** (public Applications &amp; Decisions) is the first
consumer.

## Overview

- An email flow calls `RetrievalLinkCreator` instead of attaching the file. This mints a bundle of
  one or more documents behind a single opaque token, and the flow puts
  `http://selfserve/retrieve/{token}` in the email body (rewritten to the real selfserve URL by the
  existing `SendEmail::replaceUris`).
- The recipient lands on an **anonymous selfserve page** (`RetrieveController`, no login) that
  resolves the token to a redacted summary and offers downloads.
- Each download is **streamed through the API** from the document store — no presigned URLs,
  consistent with the bucket-browser posture (`Document\BucketBrowserDownload`).
- **Real document ids are never exposed.** Only opaque values (the link token, per-document
  `memberRef`) ever appear in a URL, token or markup; they resolve to real ids server-side.

## Per-flow policy

Delivery is governed by a per-flow policy (`config['retrieval']['policies']`, in the api module
config), resolved by `RetrievalPolicyResolver`:

```php
'publication'        => ['gate' => 'none', 'expiry' => 'P42D'],  // public record, 6 weeks
'financial-evidence' => ['gate' => 'otp',  'expiry' => 'P3D'],   // DoB/bank data, OTP + 72h
```

- `gate`: `none` (unguessable link only) or `otp` (one-time code emailed to the original
  recipient before download).
- `expiry`: ISO-8601 duration or integer seconds.
- **Fails secure:** an unconfigured flow resolves to `otp` + a short window, so a missing policy can
  never accidentally publish without a gate.

## Security model

| Concern          | Mechanism                                                                                                                                                                         |
| ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Link secrecy     | 256-bit `random_bytes` token, base64url (`TokenGenerator`); the only id in the URL                                                                                                |
| OTP              | 6-digit code, hashed at rest (`password_hash`), constant-time verify, 10-min TTL, 5-attempt cap, send rate-limited (`OtpService`)                                                 |
| Post-OTP session | Short-lived (15-min) HMAC grant bound to the link token (`SessionGrantService`); held in an httponly/secure/SameSite grant cookie by selfserve, re-checked by the API on download |
| Forwarding       | OTP goes to the _original_ recipient's mailbox, so forwarding the email doesn't grant access                                                                                      |
| No oracle        | Unknown / expired / revoked links and incorrect / expired codes all return the same neutral response                                                                              |
| Audit            | Every view / OTP request / OTP result / download / denial is recorded in `retrieval_link_event`                                                                                   |

## Data model (olcs-etl, `8.3.0-schema`)

- `retrieval_link` — one row per issued link (token, `gate_mode`, `flow_key`, `source_context`,
  `recipient_email` for OTP flows, `expires_at`, `revoked_at`).
- `retrieval_link_document` — bundle members: maps an opaque `member_ref` to a real `document_id`.
- `retrieval_otp` — one-time codes (hashed), attempt counter, expiry.
- `retrieval_link_event` — append-only audit trail.

No `_hist` tables (operational/ephemeral data). The document / OTP FKs are `ON DELETE CASCADE`, but
the **audit FK is `ON DELETE SET NULL`**: purging an expired link nulls the event's link reference
but keeps the event, with `source_context` denormalised onto it so the trail stays meaningful after
the link is gone.

## Operations

- **Feature toggle** `retrieve_via_link` — seeded **inactive**. Flows fall back to legacy
  attachments until it is enabled per environment.
- **Purge** — the scheduled batch job `batch:retrieval-link-purge`
  (`RetrievalLinkPurgeCommand` → `RetrievalLink\PurgeExpired`) bulk-deletes expired links nightly;
  the cascade FKs remove member/OTP rows, and audit events are retained. Scheduled from each
  environment's `infra/terraform/environments/<env>/main.tf` jobs list (daily 03:30) — the
  `service/batch.tf` module generates the Batch job definition and EventBridge rule from that entry.
- **Session secret** — `config['retrieval']['session_secret']` (≥32 chars) must be set in
  OTP-enabled environments (via secrets / local config), or the OTP path errors. Non-OTP
  environments boot fine with it empty.

## Publications integration

`SendPublication` gates on the toggle:

- **Non-police** (public A&D) — one shared `gate=none` link, BCC'd to all recipients.
- **Police** — a `gate=otp` link **per recipient** (`publication-police` flow), each bound to that
  recipient's address so the one-time code is emailed to their own mailbox (a shared link would have
  nowhere to send it); one email per recipient.

The `publication-published` email template is a Twig both-modes conditional
(`{% if retrievalLink %}` download link `{% else %}` attached file), so the same template serves the
link and legacy-attachment paths.

## Known follow-ups

- **Translations** — the selfserve `retrieve-document.*` keys and the Welsh OTP-email / template
  copy are placeholders needing professional Welsh review + seeding into the translation store.
- **Client IP** — behind the load balancer, selfserve needs `RemoteAddress::setUseProxy(true)` for
  accurate rate-limiting.
- **`session_secret` provisioning** — adding the SSM parameter follows the vol-terraform (rfc-008)
  process before the OTP path is enabled in a deployed environment.
- **Audit retention** — events are currently kept indefinitely; a longer-horizon sweep can be added
  to the purge command if a retention period is set.
