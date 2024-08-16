<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Domain\Repository\ReadonlyRepositoryInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;

/**
 * Generic email sending handler
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractEmailHandler extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    /**
     * Repository the record will come from
     *
     * @var string
     */
    protected $repoServiceName = 'change-me';

    /**
     * The email template to be used
     *
     * @var string
     */
    protected $template = 'change-me';

    /**
     * The email subject to be used
     *
     * @var string
     */
    protected $subject = 'change-me';

    /**
     * Email message class
     *
     * @var Message
     */
    private $message;

    /**
     * Sends email confirming ecmt application has been submitted
     *
     * @param CommandInterface $command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @throws \Dvsa\Olcs\Email\Exception\EmailNotSentException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var ReadonlyRepositoryInterface $repo
         */
        $repo = $this->getRepo();
        $recordObject = $repo->fetchUsingId($command);

        $result = new Result();
        $result->addId($this->repoServiceName, $recordObject->getId());

        try {
            $recipients = $this->getRecipients($recordObject);
        } catch (MissingEmailException $e) {
            return $this->createMissingEmailTask($recordObject, $result, $e);
        }

        $templateVariables = $this->getTemplateVariables($recordObject);
        $subjectVariables = $this->getSubjectVariables($recordObject);

        $this->message = new Message($recipients['to'], $this->subject);
        $this->message->setSubjectVariables($subjectVariables);
        $this->message->setCc($recipients['cc']);
        $this->message->setBcc($recipients['bcc']);
        $this->message->setTranslateToWelsh($this->getTranslateToWelsh($recordObject));
        if (method_exists($command, 'getDocs') && $command->getDocs() !== null) {
            $this->message->setDocs($command->getDocs());
        }

        $this->sendEmailTemplate($this->message, $this->template, $templateVariables);

        $result->addMessage('Email sent');

        return $result;
    }

    /**
     * Override this method to get the template variables required
     *
     * @param object $recordObject record will be a doctrine entity
     *
     * @return array
     */
    protected function getTemplateVariables($recordObject)
    {
        return [];
    }

    /**
     * Override this method to get the subject line variables required
     *
     * @param object $recordObject record will be a doctrine entity
     *
     * @return array
     */
    protected function getSubjectVariables($recordObject)
    {
        return [];
    }

    /**
     * Override this method to determine whether the message should be sent in welsh
     *
     * @param object $recordObject record will be a doctrine entity
     *
     * @return string
     */
    protected function getTranslateToWelsh($recordObject)
    {
        return 'N';
    }

    /**
     * Fetch a list of recipient addresses based on the record being accessed
     * Should return an array indexed by "to", "cc" and "bcc" - can be empty arrays if there are no addresses
     *
     * @param object $recordObject the record the email is based off
     *
     * @return array
     * @throws MissingEmailException
     */
    abstract protected function getRecipients($recordObject): array;

    /**
     * Allows creation of task with suitable attributes/context.
     *
     * @return Result
     */
    abstract protected function createMissingEmailTask(mixed $recordObject, Result $result, MissingEmailException $exception): Result;

    /**
     * Returns the message object, used to assist with UT
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
