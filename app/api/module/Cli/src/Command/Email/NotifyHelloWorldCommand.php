<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Command\Email;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Command\AbstractOlcsCommand;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends a single hello-world email through the GOV.UK Notify transport using one of the
 * seed test templates. Doubles as a smoke test, operator tool, and PoC for the transport PR.
 *
 * Usage:
 *   php bin/cli.php notify:hello-world you@example.com
 *   php bin/cli.php notify:hello-world you@example.com --template=notify-smoke-cy --locale=cy_GB
 */
class NotifyHelloWorldCommand extends AbstractOlcsCommand
{
    protected static $defaultName = 'notify:hello-world';

    public function __construct(
        CommandHandlerManager $commandHandlerManager,
        private readonly TemplateRenderer $templateRenderer,
    ) {
        parent::__construct($commandHandlerManager);
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Send a hello-world email via GOV.UK Notify using a seed template.')
            ->addArgument('recipient', InputArgument::REQUIRED, 'Recipient email address')
            ->addOption('template', 't', InputOption::VALUE_REQUIRED, 'Template key', 'notify-smoke-plain')
            ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'Locale (en_GB or cy_GB)', 'en_GB')
            ->addOption('subject', 's', InputOption::VALUE_REQUIRED, 'Subject line', 'Notify smoke test');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeOutputInterface($output);

        /** @var string $recipient */
        $recipient = $input->getArgument('recipient');
        /** @var string $template */
        $template = $input->getOption('template');
        /** @var string $locale */
        $locale = $input->getOption('locale');
        /** @var string $subject */
        $subject = $input->getOption('subject');

        $message = (new Message($recipient, $subject))
            ->setLocale($locale)
            ->setTemplateKey($template)
            ->setPersonalisation(['name' => 'world']);

        $this->templateRenderer->renderMarkdownBody($message, $template, ['name' => 'world']);

        $output->writeln(sprintf('<info>Dispatching Notify email to %s via template "%s" (%s)</info>', $recipient, $template, $locale));

        $status = $this->handleCommand([$message->buildCommand()]);

        return $status === Command::SUCCESS
            ? $this->outputResult($status, 'Hello-world email dispatched.', 'Hello-world email dispatch failed.')
            : Command::FAILURE;
    }
}
