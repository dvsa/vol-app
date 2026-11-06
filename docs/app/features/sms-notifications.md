---
sidebar_position: 60
title: SMS Notifications
---

# SMS Notifications

VOL texts operators through GOV.UK Notify. Today there is one kind of text: a nudge when we send
the operator a letter, asking them to check their account or post. Every SMS, and every email
sent through Notify apart from admin test sends, is recorded in the `notify_message` table.

## When SMS is switched on

All of these must be true, checked by `SmsGate` when a text is queued and again when it is sent:

| Setting                                              | Needs to be                                                  |
| ---------------------------------------------------- | ------------------------------------------------------------ |
| `mail.dsn` (`olcs_mail_dsn`)                         | a `govuknotify://` DSN (or `govuknotify+mailpit://` locally) |
| `email.sms.template_id` (`olcs_notify_sms_template`) | the id of the Notify SMS template (see below)                |
| `sms_notifications` toggle                           | on                                                           |
| `email.sms.send_all_sms_to` (`olcs_send_all_sms_to`) | blank or a valid UK number (see below)                       |

The Notify API key comes from the mail DSN, so SMS can't go live before the Notify email cutover.
SSM can't store an empty string, so `" "` or `"null"` mean unset.

The Notify template is a passthrough: its whole content is `((body))`. VOL renders the text itself
and sends it as the `body` personalisation, so wording changes happen in VOL, not in Notify.

### Who gets the text

| Environment | `send_all_sms_to` | Text goes to                                            |
| ----------- | ----------------- | ------------------------------------------------------- |
| Production  | ignored           | the operator's number                                   |
| Non-prod    | a valid number    | that number (the row records it in `redirected_to`)     |
| Non-prod    | unset             | the operator's number\*                                 |
| Non-prod    | set, not a number | nobody, SMS stays off (a typo never texts real numbers) |

\* With it unset, the environment's Notify key is what keeps texts away from real people. A
**test** key never delivers anything. A **team and guest list** key only delivers to the
service's team members and guest list, and Notify refuses anyone else (the row is marked
rejected). Don't put a live key outside production. To test, add yourself to the team or guest
list and set an operator's SMS number to your own.

Environments built from production data hold real operators' numbers, so give those a redirect
number.

`olcs_send_all_sms_to` is a key in each non-production environment's API secret
(`*-BASE-SM-APPLICATION-API` in Secrets Manager), not a Terraform parameter, so phone numbers stay
out of this public repo. Every non-production secret needs the key, even if it's just `" "`, or the
API won't start.

## The letter text

`Document\PrintLetter` and `Document\DispatchDocument` queue a text after a letter goes to an
operator (`Sms\QueueLetterSms`). It is skipped when:

- the letter's template is marked suppress from operator
- the licence has no SMS number
- that number has already had a letter text in the current SMS day (20:00 to 20:00 London, across
  all licences)

Texts are only sent between 08:00 and 20:00 London time. Outside those hours the queue item
waits until 08:00. A text that has to wait for the window more than 12 hours after it was
requested is marked failed rather than sent, though a retry or a queue backlog late in the
evening can still push one into the next morning. Sending (`Sms\SendSms`, queue type
`que_typ_sms`) makes up to 4 attempts when Notify is unavailable. Before a retry it asks Notify
whether the text already went, using the reference `<env>-sms-<rowId>`, so a text is never sent
twice.

Variables available to the template: `name` (the organisation name) and `dayOfWeek`. Welsh
licences get the `cy_GB` version when there is one, otherwise English.

### Adding another kind of text

Give it its own template name and its own queueing command. The one-a-day limit and the sending
hours belong to the letter text, so a new kind sets its own (a one-time code, for example, needs
neither). The `NotifyMessage` repository's `hasSmsForRecipientBetween()` checks one template name
at a time.

## Collecting the number

Operators give an SMS number (mobile or UK landline, validated the way Notify does it) on the
Addresses page and the continuation checklist. It is stored in E.164 as a `phone_t_SMS` phone
contact and shown back in UK national format.

| `sms_number_capture` toggle | Selfserve Addresses (applications, variations, licences) | Continuation checklist | Internal Addresses |
| --------------------------- | -------------------------------------------------------- | ---------------------- | ------------------ |
| off                         | not shown                                                | not shown              | not shown          |
| on                          | required\*\*                                             | required               | optional           |

\*\* A new application without one also can't be paid for or submitted. Variations aren't
checked again.

With capture off, saving the page leaves any number already held as it is. Review pages show the
SMS number only when there is one.

Turn capture on only once sending is live (the hint tells operators we'll text them), and once VFT,
the Playwright suite and the api-calls tests fill in the field.

## Admin screen

**Admin > Content management > SMS Management** lists the SMS templates (`template` rows with
format `sms`). It works like Email Templates: edit the text and preview it with the template's
test data. The preview shows the character count and how many texts it will
take, after Notify's own character swaps.

## `notify_message`

One row per message sent through Notify, with the channel (`nmc_sms`, `nmc_email`; letters later),
status (`nms_*`), recipient, Notify id, template, timings and errors, plus the licence,
organisation, application, case, bus reg or document it relates to. That context is there so
internal users can be shown delivery status later (VOL-7721). Email rows are written by the Notify
email transport and don't affect sending if the write fails. Admin "send test" emails aren't
recorded. Rows aren't removed by data retention yet.

Locally, `govuknotify+mailpit://` sends texts to Mailpit as emails to `sms@localhost` with the
subject `SMS to <number>`.
