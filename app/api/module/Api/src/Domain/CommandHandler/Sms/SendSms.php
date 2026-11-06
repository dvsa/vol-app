<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Sms;

use Alphagov\Notifications\Exception\NotifyException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotReadyException;
use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage;
use Dvsa\Olcs\Api\Service\Sms\SendingWindow;
use Dvsa\Olcs\Api\Service\Sms\SmsConfig;
use Dvsa\Olcs\Api\Service\Sms\SmsGate;
use Dvsa\Olcs\Api\Service\Sms\SmsTransportInterface;
use Dvsa\Olcs\Api\Service\Template\TwigRenderer;
use Dvsa\Olcs\Email\Transport\NotifyErrors;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Utils\Helper\ValueHelper;
use Psr\Container\ContainerInterface;
use Twig\Error\Error as TwigError;
use Twig\Error\LoaderError;

/**
 * Send the SMS held in a notify_message row. Runs from the queue as the system user.
 */
class SendSms extends AbstractCommandHandler
{
    private const string LOCALE_EN = 'en_GB';
    private const string LOCALE_CY = 'cy_GB';
    private const int MAX_ATTEMPTS = 4;
    private const int RETRY_AFTER = 900;
    private const int MAX_WAIT_AFTER_REQUEST = 12 * 3600;

    /** Kinds of text a number gets at most once per SMS day, checked again here as the queue-time check can miss one */
    private const array ONE_PER_SMS_DAY = [QueueLetterSms::TEMPLATE_NAME];

    protected $repoServiceName = 'NotifyMessage';

    private SmsGate $smsGate;
    private SmsConfig $config;
    private SmsTransportInterface $transport;
    private TwigRenderer $twig;

    #[\Override]
    public function handleCommand(CommandInterface $command)
    {
        $message = $this->getRepo()->fetchById($command->getId());

        // Already handled (a retry that raced a success, or a replayed queue item).
        if ($message->getStatus()->getId() !== NotifyMessage::STATUS_QUEUED) {
            return $this->result;
        }

        $now = $this->now();

        if (!$this->smsGate->isEnabled()) {
            $this->finish($message, NotifyMessage::STATUS_CANCELLED, $now);
            $this->result->addMessage('SMS cancelled, sending is switched off');

            return $this->result;
        }

        $nextAllowed = SendingWindow::nextAllowed($now);
        if ($nextAllowed !== null) {
            // A wait for the window is not a send attempt. Each wait retries at the next 08:00, so
            // within 12 hours of the request there can be only one; a later one ends the row. With
            // at most 4 attempts that is 6 fetches at most, inside the consumer's limit.
            $requestedOn = $message->getRequestedOn(true);
            if ($requestedOn !== null && $now->getTimestamp() - $requestedOn->getTimestamp() > self::MAX_WAIT_AFTER_REQUEST) {
                $message->setErrorMessage('Still outside the SMS sending window 12 hours after it was requested');
                $this->finish($message, NotifyMessage::STATUS_FAILED, $now);
                $this->result->addMessage('SMS failed, not sent within 12 hours');

                return $this->result;
            }

            $e = new NotReadyException('Outside the SMS sending window');
            $e->setRetryAfter(max(1, $nextAllowed->getTimestamp() - $now->getTimestamp()));
            throw $e;
        }

        // Outside production, texts go to the redirect number when one is set. Without one they
        // go to the real number, and the env's team or test Notify key keeps them from real people.
        $to = $message->getRecipient();
        if (!$this->config->isProduction && $this->config->sendAllSmsTo !== null) {
            $to = $this->config->sendAllSmsTo;
            $message->setRedirectedTo($to);
        }

        try {
            $body = $this->render($message, $now);
        } catch (TwigError $e) {
            $message->setErrorMessage(mb_substr($e->getMessage(), 0, 1024));
            $this->finish($message, NotifyMessage::STATUS_FAILED, $now);
            $this->result->addMessage('SMS failed, template could not be rendered');

            return $this->result;
        }
        $templateId = (string) $this->config->templateId;

        // The template id is saved just before every send, so it is only set once a send may have happened.
        $maySendAlready = $message->getNotifyTemplateId() !== null;

        // a first attempt is checked before it counts as one, a retry only once Notify says it didn't go
        if (!$maySendAlready && $this->cancelIfAlreadyTextedToday($message, $now)) {
            return $this->result;
        }

        $message->setAttempts(($message->getAttempts() ?? 0) + 1);
        $message->setNotifyTemplateId($templateId);

        // An earlier attempt may have reached Notify without us hearing back, so ask before sending again.
        if ($maySendAlready) {
            try {
                $existingId = $this->transport->findByReference($this->config->smsReference($message->getId()));
            } catch (\Throwable $e) {
                // never send blind on a retry
                $error = 'Notify lookup failed: '
                    . ($e instanceof NotifyException ? NotifyErrors::message($e) : $e->getMessage());
                $message->setErrorMessage(mb_substr($error, 0, 1024));

                return $this->retryOrFail(
                    $message,
                    $now,
                    $this->retryDelay($now),
                    $error,
                    $e
                );
            }

            if ($existingId !== null) {
                $this->markSent($message, $existingId, $now);
                $this->result->addMessage('SMS already sent on an earlier attempt');

                return $this->result;
            }

            if ($this->cancelIfAlreadyTextedToday($message, $now)) {
                return $this->result;
            }
        }

        // saved before the send, so a crash after it leaves attempts > 0 for the next run to see
        $this->getRepo()->save($message);

        try {
            $notifyId = $this->transport->send($to, $templateId, $body, $this->config->smsReference($message->getId()));
        } catch (NotifyException $e) {
            return $this->handleFailure($message, $e, $now);
        } catch (\Throwable $e) {
            // not a Notify error, so retrying would not help and could double send
            $message->setErrorMessage(mb_substr($e::class . ': ' . $e->getMessage(), 0, 1024));
            $this->finish($message, NotifyMessage::STATUS_FAILED, $now);
            $this->result->addMessage('SMS failed, transport error');

            return $this->result;
        }

        $this->markSent($message, $notifyId, $now);
        $this->result->addMessage('SMS sent');

        return $this->result;
    }

    private function cancelIfAlreadyTextedToday(NotifyMessage $message, \DateTimeImmutable $now): bool
    {
        $templateName = (string) $message->getTemplateName();
        if (!in_array($templateName, self::ONE_PER_SMS_DAY, true)) {
            return false;
        }

        [$dayStart, $dayEnd] = SendingWindow::smsDay($now);
        $alreadyTexted = $this->getRepo()->hasOtherSmsSentToRecipientBetween(
            (int) $message->getId(),
            $message->getRecipient(),
            $templateName,
            $dayStart,
            $dayEnd
        );

        if ($alreadyTexted) {
            $this->finish($message, NotifyMessage::STATUS_CANCELLED, $now);
            $this->result->addMessage('SMS cancelled, this number already had one today');
        }

        return $alreadyTexted;
    }

    private function markSent(NotifyMessage $message, ?string $notifyId, \DateTimeImmutable $now): void
    {
        $message->setNotifyId($notifyId);
        // an error from an earlier attempt no longer applies
        $message->setErrorCode(null);
        $message->setErrorMessage(null);
        $message->setStatus($this->refData(NotifyMessage::STATUS_CREATED));
        $message->setSentOn(\DateTime::createFromInterface($now));
        $message->setStatusUpdatedOn(\DateTime::createFromInterface($now));
        $this->getRepo()->save($message);
    }

    /** Seconds to the next retry, never landing outside the window so every fetch is a real send. */
    private function retryDelay(\DateTimeImmutable $now): int
    {
        $retryAt = $now->modify('+' . self::RETRY_AFTER . ' seconds');
        $next = SendingWindow::nextAllowed($retryAt);

        return ($next ?? $retryAt)->getTimestamp() - $now->getTimestamp();
    }

    private function handleFailure(NotifyMessage $message, NotifyException $e, \DateTimeImmutable $now): Result
    {
        $message->setErrorCode((string) (int) $e->getCode());
        $message->setErrorMessage(mb_substr(NotifyErrors::message($e), 0, 1024));

        if (!NotifyErrors::isTransient($e)) {
            $this->finish($message, NotifyMessage::STATUS_REJECTED, $now);
            $this->result->addMessage('SMS rejected by Notify');

            return $this->result;
        }

        return $this->retryOrFail(
            $message,
            $now,
            $this->retryDelay($now),
            'Notify unavailable: ' . NotifyErrors::message($e),
            $e
        );
    }

    /**
     * Every retry after an attempt goes through here, so the row is marked failed on its last
     * attempt instead of being left queued when the queue item gives up.
     */
    private function retryOrFail(
        NotifyMessage $message,
        \DateTimeImmutable $now,
        int $retryAfter,
        string $reason,
        ?\Throwable $previous = null
    ): Result {
        if ($message->getAttempts() >= self::MAX_ATTEMPTS) {
            $this->finish($message, NotifyMessage::STATUS_FAILED, $now);
            $this->result->addMessage('SMS failed after the last attempt');

            return $this->result;
        }

        $this->getRepo()->save($message);
        $retry = new NotReadyException($reason, 0, $previous);
        $retry->setRetryAfter($retryAfter);
        throw $retry;
    }

    private function finish(NotifyMessage $message, string $status, \DateTimeImmutable $now): void
    {
        $message->setStatus($this->refData($status));
        $message->setStatusUpdatedOn(\DateTime::createFromInterface($now));
        $message->setCompletedOn(\DateTime::createFromInterface($now));
        $this->getRepo()->save($message);
    }

    private function render(NotifyMessage $message, \DateTimeImmutable $now): string
    {
        $variables = [
            'name' => $message->getOrganisation()?->getName(),
            'dayOfWeek' => $now->setTimezone(new \DateTimeZone('Europe/London'))->format('l'),
        ];
        $name = $message->getTemplateName();

        if (ValueHelper::isOn($message->getLicence()?->getTranslateToWelsh())) {
            try {
                return $this->twig->render(self::LOCALE_CY . '/sms/' . $name, $variables);
            } catch (LoaderError) {
                // no Welsh text yet, fall through to English
            }
        }

        return $this->twig->render(self::LOCALE_EN . '/sms/' . $name, $variables);
    }

    protected function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $this->smsGate = $container->get(SmsGate::class);
        $this->config = $container->get(SmsConfig::class);
        $this->transport = $container->get(SmsTransportInterface::class);
        $this->twig = $container->get('TemplatePlainTextTwigRenderer');

        return parent::__invoke($container, $requestedName, $options);
    }
}
