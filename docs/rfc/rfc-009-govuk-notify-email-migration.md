# RFC-009: GOV.UK Notify Email Migration

## Summary

vol-app historically sent all outbound email via SMTP to a pair of Amazon Linux 2 Postfix relay EC2 instances that smart-host to AWS SES. AL2 reaches end of life in mid 2026 and those hosts must be gone before then.

This RFC documents the decision to migrate vol-app's outbound email from SMTP to **GOV.UK Notify**, via a new Symfony Mailer transport that wraps the official `alphagov/notifications-php-client`. The transport reuses the existing `Queue::TYPE_EMAIL` queue and `queue:process` daemon for retry/async — we already have a queue processor and don't need another one. A **"passthrough template" pattern** keeps template content in vol-app (version-controlled and admin-editable) instead of clicking ~80 templates into the Notify web UI by hand. Local development continues to use Mailpit via a dev-only transport variant, so day-to-day developer experience is unchanged.

The DVSA Notify Service Lambda starter was considered and rejected: vol-app already has its own queue, the starter is a template not a deployed shared service, and the Lambda detour adds infrastructure, debugging hops, and a LocalStack dependency for local testing — all for zero functional gain over a direct PHP client transport.

> This RFC is published retrospectively: the transport and template work is implemented and environments are cut over by configuration. Where the design evolved during implementation, this document describes what was actually built (see "Design evolution during implementation").

## Problem

### Why we have to do something

VOL-5578 (Amazon Linux 2 EOL) is forcing the decommissioning of all remaining AL2 EC2 instances, including the SMTP relay servers vol-app talks to — Postfix smart-hosting to SES, fronted by an NLB on TCP/25. When those instances go away, vol-app has nothing to send mail through. The parent spike VOL-6877 framed the choice as: migrate to GOV.UK Notify (the stated default) or containerise the existing Postfix arrangement.

### What vol-app does today

Every email flows through a single `SendEmail` command, normally queued via `QueueAwareTrait::emailQueue()` (a `Queue::TYPE_EMAIL` row). The `queue:process` CLI daemon drains the queue, invoking the `SendEmail` command handler, which renders the message body via `TemplateRenderer`, pulls any document attachments from the Document Store, and calls `Dvsa\Olcs\Email\Service\Email::send()`. That service hands the resulting `Symfony\Component\Mime\Email` to a Symfony Mailer transport configured in `config.global.php`.

### Magnitude

| Metric                          | Value                                                                           |
| ------------------------------- | ------------------------------------------------------------------------------- |
| Email-sending command handlers  | ~47 (`app/api/module/Api/src/Domain/CommandHandler/Email/`)                     |
| Unique template names           | 41 (en_GB)                                                                      |
| Languages supported             | 2 (en_GB, cy_GB — Welsh parity required day one)                                |
| Queue types for email           | 4 (TYPE_EMAIL, TYPE_EMAIL_BULK_UPLOAD, TYPE_CNS_EMAIL, TYPE_POST_SCORING_EMAIL) |
| Bulk-send mechanism             | CSV upload → per-row queued ProcessEmail command                                |
| Attachments                     | Supported (document IDs resolved from Document Store)                           |
| Prior GOV.UK Notify integration | None — greenfield                                                               |

### What GOV.UK Notify constrains

Two facts from the Notify API docs shape the whole design:

1. **Templates can only be created/edited in the Notify web UI.** The REST API is read/preview-only for templates. Template content is not API-manageable.
2. **Personalisation values are rendered as Markdown.** The docs state that placeholders can carry Markdown formatting and links — which is what makes the passthrough pattern below possible.

Other practical constraints: the from-address is account-wide (reply-to is per-message); attachments are supported up to 2 MB against a fixed file-type allow-list via `prepareUpload()`; the rate limit is 3,000 messages/minute per API key; email is free for central government.

## Proposal

### High-level shape

A new Symfony Mailer transport, `Dvsa\Olcs\Email\Transport\GovUkNotifyTransport`, wraps `alphagov/notifications-php-client` and is selected by a `govuknotify://` DSN (`config['mail']['dsn']`, populated per environment from Parameter Store / Secrets Manager; the DSN takes precedence over the legacy SMTP host/port configuration, which remains available until the last environment cuts over). Everything above the transport — the `SendEmail` handler's role, the `Queue::TYPE_EMAIL` flow, the queue daemon, attachment fetching, the `send_all_mail_to` redirect for non-production — keeps working. Only the wire-level dispatch mechanism changes.

### Reuse the existing queue, don't add another one

vol-app already has a database-backed email queue with retry semantics. The transport surfaces Notify's transient errors (HTTP 429, 5xx) as retryable exceptions the existing queue machinery requeues, and maps permanent errors (4xx validation failures) to non-retryable failures (wrapped so the queue consumer marks the job permanently failed). Any architecture that puts SQS or another buffer between vol-app and Notify duplicates work the queue table is already doing.

### The "passthrough template" pattern

A naive reading of "templates only via the web UI" implies clicking ~82 templates (41 × 2 languages) into the Notify admin UI — no version control, no PR review, no rollback.

Because personalisation values are rendered as Markdown, this collapses to **two passthrough templates per Notify service** (one per locale), each consisting only of a `((subject))` subject and a `((body))` body. vol-app renders the entire Markdown body locally and passes it as the `body` personalisation value; Notify renders the Markdown into its standard GOV.UK email chrome at delivery time. The per-locale template UUIDs are configuration (`email.notify.passthrough_templates`), resolved per environment.

Why this is the right call:

- Template source-of-truth stays inside vol-app — diffable, reviewable, rollbackable, testable
- Welsh parity is nearly free: same passthrough templates, content driven from the cy_GB variants through the same locale-resolution logic
- The handlers' branching logic (PSV vs Goods, ECMT scoring tables, etc.) stays in PHP where it already works — Notify's lack of conditionals/loops in templates is irrelevant
- Two manually-created Notify templates per service instead of 82

**Why Notify's Markdown-injection warning doesn't apply:** that warning targets services piping user-submitted input straight into personalisation variables. vol-app is the sole author of the body Markdown — where user-supplied values appear (operator names, references) they are escaped at the point of insertion, as the renderer already did for HTML.

### Templates are Markdown-Twig rows in the database

The body content itself lives in the existing user-editable template system: rows in the `template` table with **`format='md'`**, rendered by the existing Twig pipeline (`StrategySelectingViewRenderer` → `DatabaseTwigLoader` → `TwigRenderer`). This reuses an existing column and requires zero schema change, keeps templates admin-editable in the internal UI (with a CommonMark-based approximate preview), and gives content authors Twig — vastly more expressive than Notify's dumb substitution. There is no layout wrap for `md` templates: Notify supplies the email chrome server-side.

The legacy on-disk Phtml (`html` + `plain`) templates remain as the SMTP-mode rendering source until final cleanup, which is also the rollback story: an environment switched back to an SMTP DSN renders the legacy formats again.

### Cutover is atomic per environment, driven by the DSN

`TemplateRenderer` operates in "Notify mode" when the environment's mailer DSN scheme is `govuknotify`. In Notify mode it renders the `format='md'` template into the message's `markdownBody` and stamps the passthrough template UUID; in SMTP mode it renders the legacy `html`/`plain` bodies. The ~47 sending handlers did not need individual migration — they all route through `EmailAwareTrait::sendEmailTemplate()` and the renderer, so the switch is central.

A missing `md` template row in Notify mode fails loudly (it does **not** fall back to SMTP rendering): this deliberately enforces that an environment only flips once **all** templates have `md` rows, making the cutover atomic per environment (VOL-7238). Template conversion (Phtml → Markdown-Twig, en_GB + cy_GB in lock-step) was carried out in content batches ahead of any DSN flip.

To de-risk the flip, a dedicated `NotifyTestMailer` (separate DSN, `email.notify_test.dsn`) backs an admin **"Send test via Notify"** action, letting an environment exercise the real Notify path with a test-mode key _before_ its `mail.dsn` changes. A `notify:hello-world <recipient>` CLI command provides the same end-to-end check from a shell.

### Local development testing

The existing Mailpit workflow is preserved. A `DevNotifyTransport`, selected by `govuknotify+mailpit://`, renders the Markdown body to HTML via `league/commonmark`, wraps it in a static GOV.UK-alike chrome (`NotifyChrome`), and hands the message to Mailpit over SMTP. Developers still trigger an email and inspect it in the Mailpit UI.

- ✅ Zero change to developer experience; zero new infrastructure; works offline, no API key
- ✅ Unit tests mock `\Alphagov\Notifications\Client` directly
- ⚠️ The local renderer is a CommonMark approximation of Notify's rendering, not the real thing — bounded by using a well-maintained library, and mitigated by the admin test-send and CLI checks against a real test-mode key

### Design evolution during implementation

Decisions taken while building that refine the original proposal:

- **Central switch, not per-handler migration.** The original plan migrated handlers in groups, each populating Notify fields with SMTP fallback per handler. As built, the renderer switches centrally on the DSN and the cutover unit is the environment (see above) — no handler changes, and no mixed state where some emails in one environment go via Notify and others via SMTP.
- **Notify payload travels in a MIME header.** The Notify-specific payload (markdown body, personalisation, attachments, reference, reply-to) is attached to the `Symfony\Component\Mime\Email` as a JSON custom header (`X-Olcs-Notify-Payload`) by the `SendEmail` handler and extracted (and removed) by the transport. This keeps the standard `TransportInterface` contract — the same message object still works on the SMTP transport.
- **Attachment validation is explicit.** `NotifyAttachmentValidator` enforces Notify's file-type allow-list and 2 MB limit before `prepareUpload()`, answering the draft's open question about attachment types.
- **Link rewriting.** The `SendEmail` handler rewrites internal placeholder URIs (`http://selfserve/`, `http://internal/`) in the Markdown body to the environment's public URLs — Markdown bodies are composed environment-agnostically.
- **`send_all_mail_to` retained** and driven by configuration value, so non-production redirect behaviour is unchanged under Notify.

## Alternatives Considered

### Containerise the existing Postfix relay

Build a vanilla Postfix-over-SES container on ECS and point vol-app at it unchanged. Rejected as the primary plan: it "solves" AL2 decommissioning but leaves vol-app on SMTP indefinitely, creates a new piece of infrastructure to own, and the parent spike names Notify as the default absent a justification — which the passthrough pattern removes. Retained as a time-boxed contingency (~half a week of effort) had the migration missed the AL2 deadline.

### Use the DVSA Notify Service Lambda starter

`dvsa/dvsa-notify-service` is a TypeScript Lambda starter that consumes SQS and forwards to Notify. Rejected because vol-app already has a queue processor (SQS + Lambda would be a parallel queueing system doing the same job); local testing would require LocalStack in every developer's stack; the repo is a starter template, not a deployed shared service — adopting it means owning a Node.js Lambda, its Terraform, CI, secrets and monitoring, from a PHP team; and debugging gains an extra two hops. It may suit a service with no queue of its own; that is not vol-app.

### Port all ~82 templates into the Notify UI

Faithfully re-create each email type per language in the Notify admin UI. Rejected: a lot of unautomatable clicking, content outside version control, Welsh parity doubles the work, and Notify's no-loops-no-conditionals model can't express the conditional content — which would end up pre-computed in PHP anyway, at which point the passthrough pattern is strictly better.

## Rollout and decommissioning

1. **Platform setup** — Notify services per environment tier with the passthrough templates; API keys, template UUIDs and the DSN in Parameter Store / Secrets Manager; branding and sender-address continuity for `notifications@vehicle-operator-licensing.service.gov.uk`; egress allow-listing to the Notify API.
2. **Template conversion** — Phtml → Markdown-Twig `format='md'` rows, in content batches, en_GB and cy_GB in lock-step (never English-on-Notify / Welsh-on-SMTP).
3. **Per-environment cutover** — verify via the admin test-send, then flip `%olcs_mail_dsn%` to `govuknotify://…`; soak; non-production environments first, production last. Rollback is flipping the DSN back to SMTP while the relay still exists.
4. **Cleanup** — once production has soaked: remove the SMTP code path, legacy `html`/`plain` rendering, dead Phtml files and `%olcs_email_host%`/`%olcs_email_port%` configuration; infra decommissions the Postfix ASG/NLB/DNS under VOL-5578. vol-app's sign-off is "we no longer talk to this hostname".

## Key files

- `app/api/module/Email/src/Transport/GovUkNotifyTransport.php` — the production transport (passthrough dispatch, attachment upload, error mapping)
- `app/api/module/Email/src/Transport/GovUkNotifyTransportFactory.php` + `Factory/GovUkNotifyTransportFactoryFactory.php` — DSN registration (`govuknotify://`, `govuknotify+mailpit://`)
- `app/api/module/Email/src/Transport/DevNotifyTransport.php` + `src/View/NotifyChrome.php` — local Mailpit rendering
- `app/api/module/Email/src/Transport/NotifyAttachmentValidator.php` — allow-list + size validation
- `app/api/module/Email/src/Service/TemplateRenderer.php` — Notify-mode switch, `md` rendering, passthrough UUID stamping
- `app/api/module/Email/src/Service/NotifyTestMailer.php` — admin "Send test via Notify" (VOL-7238)
- `app/api/module/Email/src/Domain/CommandHandler/SendEmail.php` — payload header attachment, URI rewriting, `send_all_mail_to`
- `app/api/module/Email/src/Data/Message.php` — `templateKey` / `personalisation` / `markdownBody` fields
- `app/api/module/Cli/src/Command/Email/NotifyHelloWorldCommand.php` — end-to-end smoke CLI
- `app/api/config/autoload/config.global.php` — `mail.dsn` + `email.notify.*` configuration

## References

- Parent spike: VOL-6877; parent epic: VOL-5578 (Amazon Linux 2 EOL); cutover enforcement/test-send: VOL-7238
- GOV.UK Notify REST API docs: https://docs.notifications.service.gov.uk/rest-api.html
- GOV.UK Notify PHP client docs: https://docs.notifications.service.gov.uk/php.html
- alphagov/notifications-php-client: https://packagist.org/packages/alphagov/notifications-php-client
- dvsa/dvsa-notify-service (the rejected Lambda starter): https://github.com/dvsa/dvsa-notify-service
- league/commonmark (dev-mode renderer): https://commonmark.thephpleague.com/
