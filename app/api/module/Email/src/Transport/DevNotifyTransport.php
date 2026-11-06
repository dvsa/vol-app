<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Dvsa\Olcs\Email\View\NotifyChrome;
use League\CommonMark\ConverterInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\RawMessage;

/**
 * Development-only transport. Renders the Notify markdown body to HTML via CommonMark,
 * wraps it in a static GOV.UK email chrome template, and hands the message to an inner
 * SMTP transport pointed at Mailpit so the existing developer workflow is preserved.
 *
 * When no Notify payload header is present (legacy handlers not yet migrated), the
 * message is passed through unchanged to the inner transport.
 */
final class DevNotifyTransport extends AbstractTransport
{
    use RecordsNotifyEmails;

    public function __construct(
        private readonly TransportInterface $inner,
        private readonly ConverterInterface $markdownConverter,
        private readonly string $chromeTemplate,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null,
        private readonly ?NotifyMessageRecorderInterface $recorder = null,
    ) {
        parent::__construct($dispatcher, $logger);
    }

    #[\Override]
    public function __toString(): string
    {
        return 'govuknotify+mailpit://';
    }

    #[\Override]
    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        if (!$original instanceof SymfonyEmail) {
            $this->inner->send($original ?? new RawMessage(''), $message->getEnvelope());
            return;
        }

        $headers = $original->getHeaders();
        $header = $headers->get(GovUkNotifyTransport::PAYLOAD_HEADER);

        $payload = null;
        if ($header instanceof UnstructuredHeader) {
            $payload = $this->renderMarkdownIntoHtml($original, $header->getValue());
            $headers->remove(GovUkNotifyTransport::PAYLOAD_HEADER);
        }

        $this->inner->send($original, $message->getEnvelope());

        if ($payload !== null) {
            $context = $this->recordingContext($payload, null);
            foreach ($message->getEnvelope()->getRecipients() as $recipient) {
                $this->recordEmail($this->recorder, $recipient->getAddress(), $context, null, null, null);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function renderMarkdownIntoHtml(SymfonyEmail $email, string $rawPayload): array
    {
        try {
            $payload = json_decode($rawPayload, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $payload = [];
        }

        $markdown = is_array($payload) && isset($payload['markdownBody']) && is_string($payload['markdownBody'])
            ? $payload['markdownBody']
            : ($email->getTextBody() ?? '');

        $html = NotifyChrome::renderMarkdownBody($markdown, $this->markdownConverter);
        $chromed = strtr($this->chromeTemplate, [
            '{{subject}}' => htmlspecialchars((string) $email->getSubject(), ENT_QUOTES, 'UTF-8'),
            '{{body}}' => $html,
        ]);

        $email->html($chromed);
        if ($email->getTextBody() === null || $email->getTextBody() === '') {
            $email->text($markdown);
        }

        return is_array($payload) ? $payload : [];
    }
}
