<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Template;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Email\Service\NotifyTestMailer;
use Dvsa\Olcs\Email\Transport\GovUkNotifyTransport;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Template\SendTestEmail as SendTestEmailCmd;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Throwable;

/**
 * Sends a single test email for a `format='md'` template through the dedicated
 * {@see NotifyTestMailer} (independent of the env-level `mail.dsn`). Used by the admin
 * "Send test via Notify" button on `/admin/email-templates` so admins can verify a
 * converted markdown template before the env-level Notify cutover.
 *
 * Validates that the row is markdown; renders against the first `template_test_data`
 * dataset so Twig placeholders are filled with realistic values; builds a Mime\Email
 * with the matching Notify passthrough template UUID; dispatches via NotifyTestMailer.
 */
final class SendTestEmail extends AbstractCommandHandler
{
    public const FORMAT_MARKDOWN = 'md';

    protected $repoServiceName = 'Template';

    private TwigRenderer $twigRenderer;
    private NotifyTestMailer $testMailer;

    /** @var array<string, string> locale → passthrough template UUID */
    private array $passthroughUuids = [];

    private string $fromEmail = '';
    private string $fromName = '';

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        /** @var SendTestEmailCmd $command */
        if (!$this->testMailer->isEnabled()) {
            throw new ValidationException(['NotifyTestMailer is not configured for this environment.']);
        }

        $template = $this->getRepo()->fetchUsingId($command);

        if ($template->getFormat() !== self::FORMAT_MARKDOWN) {
            throw new ValidationException(['Only `format=md` templates can be sent via Notify test.']);
        }

        $locale = (string) $template->getLocale();
        $templateKey = $this->passthroughUuids[$locale] ?? '';
        if ($templateKey === '') {
            throw new ValidationException([
                sprintf('No passthrough Notify template UUID configured for locale "%s".', $locale),
            ]);
        }

        $datasets = $template->getDecodedTestData();
        $firstDataset = is_array($datasets) && $datasets !== [] ? reset($datasets) : [];
        $firstDataset = is_array($firstDataset) ? $firstDataset : [];

        try {
            $renderedMarkdown = $this->twigRenderer->renderString(
                (string) $template->getSource(),
                $firstDataset,
            );
        } catch (Throwable $e) {
            throw new ValidationException(['Twig render failed: ' . $e->getMessage()]);
        }

        $subject = sprintf('[TEST] %s', (string) $template->getDescription());

        $email = (new SymfonyEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to(new Address($command->getRecipient()))
            ->subject($subject)
            ->text($renderedMarkdown);

        // The GovUkNotifyTransport reads this header to extract templateKey + personalisation
        // + markdownBody. Mirroring what the production SendEmail handler does.
        $payload = [
            'templateKey' => $templateKey,
            'locale' => $locale,
            'personalisation' => [],
            'markdownBody' => $renderedMarkdown,
            'attachments' => [],
        ];

        $email->getHeaders()->addTextHeader(
            GovUkNotifyTransport::PAYLOAD_HEADER,
            json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
        );

        try {
            $this->testMailer->send($email);
        } catch (TransportExceptionInterface | Throwable $e) {
            Logger::err('notify test send failed', [
                'templateId' => $template->getId(),
                'recipient' => $command->getRecipient(),
                'error' => $e->getMessage(),
            ]);
            throw new ValidationException(['Notify test send failed: ' . $e->getMessage()]);
        }

        Logger::info('notify test send', [
            'templateId' => $template->getId(),
            'templateName' => $template->getName(),
            'locale' => $locale,
            'recipient' => $command->getRecipient(),
        ]);

        $this->result->addMessage(sprintf('Test email sent to %s', $command->getRecipient()));
        return $this->result;
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->twigRenderer = $container->get('TemplateTwigRenderer');
        $this->testMailer = $container->get(NotifyTestMailer::class);

        $config = $container->get('config');
        $this->passthroughUuids = $config['email']['notify']['passthrough_templates'] ?? [];
        if (!is_array($this->passthroughUuids)) {
            $this->passthroughUuids = [];
        }
        $this->fromEmail = (string) ($config['email']['from_email'] ?? '');
        $this->fromName = (string) ($config['email']['from_name'] ?? '');

        return parent::__invoke($container, $requestedName, $options);
    }
}
