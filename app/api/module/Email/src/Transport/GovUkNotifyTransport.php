<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Olcs\Logging\Log\Logger;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Header\UnstructuredHeader;

/**
 * Symfony Mailer transport that dispatches via GOV.UK Notify.
 *
 * Uses the "passthrough template" pattern: two Notify templates per service (one per locale) with
 * `((subject))` and `((body))` placeholders. The Markdown body is rendered locally by vol-app and
 * passed as the `body` personalisation value; Notify renders the Markdown into its standard email
 * chrome at delivery time.
 *
 * Notify payload (templateKey/locale/personalisation/attachments) is smuggled via a custom Mime
 * header {@see GovUkNotifyTransport::PAYLOAD_HEADER} attached by {@see \Dvsa\Olcs\Email\Domain\CommandHandler\SendEmail}.
 */
final class GovUkNotifyTransport extends AbstractTransport
{
    public const PAYLOAD_HEADER = 'X-Olcs-Notify-Payload';

    public const LOCALE_EN_GB = 'en_GB';

    /**
     * @param array<string, string> $passthroughTemplateIds Map of locale => Notify template UUID
     */
    public function __construct(
        private readonly NotifyClient $notifyClient,
        private readonly array $passthroughTemplateIds,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($dispatcher, $logger);
    }

    #[\Override]
    public function __toString(): string
    {
        return 'govuknotify://';
    }

    #[\Override]
    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        if (!$original instanceof SymfonyEmail) {
            throw new EmailNotSentException('GovUkNotifyTransport requires a Symfony Mime Email');
        }

        $payload = $this->extractPayload($original);
        $recipients = $this->resolveRecipients($message->getEnvelope(), $original);
        $templateId = $this->resolveTemplateId($payload['locale'] ?? self::LOCALE_EN_GB);
        $personalisation = $this->buildPersonalisation($original, $payload);

        // Notify has no To/Cc/Bcc and takes one address per call, so we deliver an individual copy
        // to every recipient (SMTP delivered to all of them). Once Notify accepts a send (201) it
        // owns delivery and its own delivery-retries; here we only handle the acceptance call.
        $sent = 0;
        $permanentlyFailed = 0;
        $transient = null;

        foreach ($recipients as $recipient) {
            try {
                $response = $this->notifyClient->sendEmail(
                    $recipient,
                    $templateId,
                    $personalisation,
                    $payload['reference'] ?? '',
                    $payload['emailReplyToId'] ?? null,
                );

                $sent++;
                $notifyId = is_array($response) ? ($response['id'] ?? null) : null;
                Logger::info('notify email sent', ['notify_id' => $notifyId, 'locale' => $payload['locale'] ?? null]);
            } catch (NotifyException $e) {
                if ($this->isTransient($e)) {
                    // Transient (429/5xx): the queue will retry the whole message.
                    $transient = $e;
                    Logger::info('notify send transient failure, message will be retried', ['code' => (int) $e->getCode()]);
                } else {
                    // Permanent (e.g. a malformed address): log and skip this recipient, so one bad
                    // address can neither fail the batch nor be retried forever (re-duplicating the
                    // recipients who already succeeded).
                    $permanentlyFailed++;
                    Logger::err('notify send permanently failed, recipient skipped', [
                        'code' => (int) $e->getCode(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Any transient failure ⇒ retry the whole message (may re-send to already-accepted
        // recipients, but a duplicate beats a drop, and transient Notify errors are rare).
        if ($transient !== null) {
            throw new EmailNotSentException(
                sprintf('Notify send failed (HTTP %d): %s', (int) $transient->getCode(), $transient->getMessage()),
                0,
                $transient,
            );
        }

        // Nothing got through and every recipient failed permanently ⇒ fail hard (DLQ) for ops.
        if ($sent === 0 && $permanentlyFailed > 0) {
            $message = sprintf('Notify send permanently failed for all %d recipient(s)', $permanentlyFailed);
            throw new EmailNotSentException($message, 0, new \DomainException($message));
        }
    }

    /**
     * @return array{
     *     templateKey?: string,
     *     locale?: string,
     *     personalisation?: array<string, mixed>,
     *     markdownBody?: string,
     *     attachments?: array<int, array{fileName: string, content: string}>,
     *     reference?: string,
     *     emailReplyToId?: string
     * }
     */
    private function extractPayload(SymfonyEmail $email): array
    {
        $headers = $email->getHeaders();
        $header = $headers->get(self::PAYLOAD_HEADER);
        if (!$header instanceof UnstructuredHeader) {
            throw new EmailNotSentException(sprintf(
                'GovUkNotifyTransport expected header "%s" but none was attached to the message.',
                self::PAYLOAD_HEADER,
            ));
        }

        $raw = $header->getValue();
        $headers->remove(self::PAYLOAD_HEADER);

        try {
            $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new EmailNotSentException('Invalid Notify payload header JSON: ' . $e->getMessage(), 0, $e);
        }

        if (!is_array($decoded)) {
            throw new EmailNotSentException('Notify payload header must decode to an object');
        }

        /** @var array $decoded */
        return $decoded;
    }

    /**
     * All distinct delivery addresses (To + Cc + Bcc), deduped case-insensitively with first-seen
     * order preserved. SMTP delivered to exactly these; Notify must too — one send each.
     *
     * @return list<string>
     */
    private function resolveRecipients(Envelope $envelope, SymfonyEmail $email): array
    {
        $addresses = array_map(static fn ($a): string => $a->getAddress(), $envelope->getRecipients());
        if ($addresses === []) {
            $addresses = array_map(static fn ($a): string => $a->getAddress(), $email->getTo());
        }

        $seen = [];
        $unique = [];
        foreach ($addresses as $address) {
            $key = strtolower($address);
            if ($address !== '' && !isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $address;
            }
        }

        if ($unique === []) {
            throw new EmailNotSentException('No recipient address present on the Notify message');
        }

        return $unique;
    }

    private function isTransient(NotifyException $e): bool
    {
        $code = (int) $e->getCode();

        return $code === 429 || $code >= 500;
    }

    private function resolveTemplateId(string $locale): string
    {
        $id = $this->passthroughTemplateIds[$locale] ?? null;
        if ($id === null || $id === '') {
            throw new EmailNotSentException(sprintf('No Notify passthrough template configured for locale "%s"', $locale));
        }
        return $id;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function buildPersonalisation(SymfonyEmail $email, array $payload): array
    {
        $body = $payload['markdownBody'] ?? null;
        if (!is_string($body) || $body === '') {
            $body = $email->getTextBody();
        }
        if (!is_string($body) || $body === '') {
            throw new EmailNotSentException('Notify message is missing a body (neither markdownBody nor text body set)');
        }

        $personalisation = $payload['personalisation'] ?? [];
        if (!is_array($personalisation)) {
            throw new EmailNotSentException('Notify personalisation must be an array');
        }

        $personalisation['subject'] = $email->getSubject() ?? '';
        $personalisation['body'] = $body;

        foreach ($this->prepareAttachments($payload['attachments'] ?? []) as $key => $upload) {
            $personalisation[$key] = $upload;
        }

        return $personalisation;
    }

    /**
     * @param mixed $attachments
     * @return array<string, array>
     */
    private function prepareAttachments(mixed $attachments): array
    {
        if ($attachments === []) {
            return [];
        }

        if (!is_array($attachments)) {
            throw new EmailNotSentException('Notify attachments must be an array');
        }

        $prepared = [];
        foreach ($attachments as $attachment) {
            if (!is_array($attachment) || !isset($attachment['fileName'], $attachment['content'])) {
                throw new EmailNotSentException('Attachment requires fileName and content keys');
            }

            NotifyAttachmentValidator::assertAllowed($attachment['fileName'], $attachment['content']);

            $key = $attachment['personalisationKey'] ?? ('attachment_' . count($prepared));
            $prepared[$key] = $this->notifyClient->prepareUpload($attachment['content'], $attachment['fileName']);
        }

        return $prepared;
    }
}
