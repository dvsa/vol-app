<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Alphagov\Notifications\Client as NotifyClient;
use League\CommonMark\ConverterInterface;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Symfony Mailer transport factory that registers the `govuknotify://` and `govuknotify+mailpit://`
 * DSN schemes. The plain scheme dispatches via GOV.UK Notify; the `+mailpit` variant wraps the
 * rendered Markdown in a local GOV.UK-chromed preview and delivers to the dev SMTP sink.
 */
final class GovUkNotifyTransportFactory extends AbstractTransportFactory
{
    public const SCHEME_PRODUCTION = 'govuknotify';
    public const SCHEME_DEV = 'govuknotify+mailpit';

    /**
     * @param array<string, string> $passthroughTemplateIds Map of locale => Notify template UUID
     * @param callable(string): NotifyClient $clientFactory Callable that returns a Notify client for a given API key
     * @param ?string $apiKey The key (`notify.api_key`) for a `govuknotify://` DSN that doesn't carry its own
     */
    public function __construct(
        private readonly array $passthroughTemplateIds,
        private readonly \Closure $clientFactory,
        private readonly ConverterInterface $markdownConverter,
        private readonly string $chromeTemplate,
        private readonly ?NotifyMessageRecorderInterface $recorder = null,
        private readonly ?string $apiKey = null,
    ) {
        parent::__construct();
    }

    /**
     * The same factory for admin test sends: they record nothing in notify_message, and their DSN
     * must carry its own key, so a test send never falls back to the live key.
     */
    public function forTestSends(): self
    {
        return new self(
            $this->passthroughTemplateIds,
            $this->clientFactory,
            $this->markdownConverter,
            $this->chromeTemplate,
        );
    }

    #[\Override]
    public function supports(Dsn $dsn): bool
    {
        return in_array($dsn->getScheme(), $this->getSupportedSchemes(), true);
    }

    #[\Override]
    protected function getSupportedSchemes(): array
    {
        return [self::SCHEME_PRODUCTION, self::SCHEME_DEV];
    }

    #[\Override]
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ($scheme === self::SCHEME_DEV) {
            $host = $dsn->getHost();
            $port = $dsn->getPort(1025);
            $inner = new EsmtpTransport($host, $port, false, $this->dispatcher, $this->logger);
            return new DevNotifyTransport(
                $inner,
                $this->markdownConverter,
                $this->chromeTemplate,
                $this->dispatcher,
                $this->logger,
                $this->recorder,
            );
        }

        $apiKey = self::keyFromDsn($dsn) ?? $this->apiKey ?? '';
        if ($apiKey === '') {
            throw new \InvalidArgumentException('govuknotify needs an API key: set notify.api_key, or put one in the DSN');
        }

        $client = ($this->clientFactory)($apiKey);

        return new GovUkNotifyTransport(
            $client,
            $this->passthroughTemplateIds,
            $this->dispatcher,
            $this->logger,
            $this->recorder,
        );
    }

    /** The key in a DSN's password or user part (`govuknotify://KEY@default`), if it has one. */
    public static function keyFromDsn(Dsn $dsn): ?string
    {
        foreach ([$dsn->getPassword(), $dsn->getUser()] as $part) {
            if ($part !== null && trim($part) !== '') {
                return $part;
            }
        }

        return null;
    }
}
