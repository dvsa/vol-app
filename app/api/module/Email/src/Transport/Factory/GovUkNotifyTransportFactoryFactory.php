<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport\Factory;

use Alphagov\Notifications\Client as NotifyClient;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransportFactory;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Laminas\ServiceManager\Factory\FactoryInterface;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Psr\Container\ContainerInterface;

/**
 * Laminas ServiceManager factory for {@see GovUkNotifyTransportFactory}.
 *
 * Reads Notify passthrough template UUIDs from application config
 * (`config['email']['notify']['passthrough_templates']`). The Notify client is constructed lazily
 * per DSN so the API key (which comes from the DSN) is not required at container-build time.
 */
final class GovUkNotifyTransportFactoryFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GovUkNotifyTransportFactory
    {
        $config = $container->get('config');
        $notifyConfig = $config['email']['notify'] ?? [];

        /** @var array<string, string> $templates */
        $templates = $notifyConfig['passthrough_templates'] ?? [];
        // Strip unresolved `%placeholder%` values so the transport throws a meaningful error
        // rather than calling Notify with a literal placeholder string.
        $templates = array_filter($templates, static fn (string $v): bool => $v !== '' && !preg_match('/^%[^%]+%$/', $v));

        $chromeTemplate = is_string($notifyConfig['dev_chrome_template'] ?? null)
            ? $notifyConfig['dev_chrome_template']
            : self::defaultChromeTemplate();

        $markdownConverter = new GithubFlavoredMarkdownConverter();

        $clientFactory = static function (string $apiKey): NotifyClient {
            return new NotifyClient([
                'apiKey' => $apiKey,
                'httpClient' => new GuzzleAdapter(new GuzzleClient()),
            ]);
        };

        return new GovUkNotifyTransportFactory(
            $templates,
            $clientFactory,
            $markdownConverter,
            $chromeTemplate,
        );
    }

    private static function defaultChromeTemplate(): string
    {
        // Minimal GOV.UK-alike chrome for DevNotifyTransport; production rendering is Notify-side.
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>{{subject}}</title>
</head>
<body style="margin:0; font-family:Arial, Helvetica, sans-serif; background:#f3f2f1; padding:20px;">
  <div style="max-width:620px; margin:0 auto; background:#ffffff; padding:30px; border-top:10px solid #1d70b8;">
    <h1 style="font-size:24px; margin:0 0 20px; color:#0b0c0c;">{{subject}}</h1>
    <div style="font-size:16px; line-height:1.5; color:#0b0c0c;">{{body}}</div>
    <hr style="border:0; border-top:1px solid #b1b4b6; margin:30px 0 15px;">
    <p style="font-size:12px; color:#505a5f;">Dev preview — rendered locally via DevNotifyTransport.</p>
  </div>
</body>
</html>
HTML;
    }
}
