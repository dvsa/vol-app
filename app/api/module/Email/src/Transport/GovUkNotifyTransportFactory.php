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
     */
    public function __construct(
        private readonly array $passthroughTemplateIds,
        private readonly \Closure $clientFactory,
        private readonly ConverterInterface $markdownConverter,
        private readonly string $chromeTemplate,
    ) {
        parent::__construct();
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
            );
        }

        $apiKey = $dsn->getPassword() ?? $dsn->getUser() ?? '';
        if ($apiKey === '') {
            throw new \InvalidArgumentException('govuknotify DSN requires an API key in the password or user field');
        }

        $client = ($this->clientFactory)($apiKey);

        return new GovUkNotifyTransport(
            $client,
            $this->passthroughTemplateIds,
            $this->dispatcher,
            $this->logger,
        );
    }
}
