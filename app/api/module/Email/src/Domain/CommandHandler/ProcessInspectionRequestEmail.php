<?php

/**
 * Process Inspection Request Email
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Email\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Email\Domain\Command\UpdateInspectionRequest as UpdateInspectionRequestCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Olcs\Logging\Log\Logger;
use Dvsa\Olcs\Email\Service\Imap as Mailbox;

/**
 * Process Inspection Request Email
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ProcessInspectionRequestEmail extends AbstractCommandHandler
{
    public const string SUBJECT_REGEX = '/\[ Maintenance Inspection \] REQUEST=([\d]+),STATUS=([SU]?)$/';
    public const string MAILBOX_ID = 'inspection_request';

    /**
     * @var Mailbox
     */
    private $mailbox;

    /**
     * @return Mailbox
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    public function setMailbox(Mailbox $mailbox)
    {
        $this->mailbox = $mailbox;
    }

    public function handleCommand(CommandInterface $command)
    {
        $this->getMailbox()->connect(self::MAILBOX_ID);

        // get list of pending emails
        $emails = $this->getEmailList();

        if (empty($emails)) {
            $this->outputLine('No emails found - nothing to do!');
            return $this->result;
        }

        $this->outputLine(sprintf('Found %d email(s) to process', count($emails)));

        // loop through emails and process
        foreach ($emails as $uniqueId) {
            $this->outputLine('=Processing email id ' . $uniqueId);

            $email = $this->getEmail($uniqueId);

            if (!isset($email['subject'])) {
                Logger::warn('==Could not retrieve email ' . $uniqueId);
                continue;
            }

            $this->outputLine('==Email subject: ' . $email['subject']);

            // parse subject line
            [$requestId, $status] = $this->parseSubject($email['subject']);

            if (!$requestId || !$status) {
                // log warn and continue if invalid subject
                Logger::warn('==Unable to parse email subject line: ' . $email['subject']);
                continue;
            }

            // process valid status update
            $result = $this->processStatusUpdate($requestId, $status, $email['subject']);

            // delete email
            if ($result === true) {
                $this->deleteEmail($uniqueId);
            } else {
                $this->outputLine('==Failed to process email id ' . $uniqueId);
            }
        }

        $this->outputLine('Done');

        return $this->result;
    }

    /**
     * Get list of emails
     *
     * @return array
     */
    protected function getEmailList()
    {
        $this->outputLine('Checking mailbox...');
        return $this->getMailbox()->getMessages();
    }

    /**
     * Make REST call to retrieve individual email
     *
     * @param string $id
     * @return array
     */
    protected function getEmail($id)
    {
        $this->outputLine('==Retrieving email ' . $id);
        return $this->getMailbox()->getMessage($id);
    }

    /**
     * Make REST call to delete an individual email
     *
     * @param string $id
     */
    protected function deleteEmail($id)
    {
        $this->outputLine('==Deleting email ' . $id);
        return $this->getMailbox()->removeMessage($id);
    }

    /**
     * Parse request id and status from a subject line
     *
     * @param string $subject
     * @return array|null
     */
    protected function parseSubject($subject)
    {
        $matches = null;

        preg_match(self::SUBJECT_REGEX, $subject, $matches);

        if (!$matches) {
            return null;
        }
        return [$matches[1], $matches[2]];
    }

    /**
     * Process an inspection request update
     *
     * @param int $requestId
     * @param string $status 'S'|'U'
     * @param string $emailSubject
     * @return boolean success
     */
    protected function processStatusUpdate($requestId, $status, $emailSubject)
    {
        $this->outputLine(
            sprintf('==Handling status of \'%s\' for inspection request %s', $status, $requestId)
        );

        $data = [
            'id' => $requestId,
            'status' => $status,
        ];

        try {
            $this->handleSideEffect(UpdateInspectionRequestCmd::create($data));
            return true;
        } catch (NotFoundException) {
            Logger::warn('==Unable to find the inspection request from the email subject line: ' . $emailSubject);
            return false;
        } catch (\Exception) {
            return false;
        }
    }

    protected function outputLine($message)
    {
        $this->result->addMessage($message);
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->setMailbox($container->get('ImapService'));
        return parent::__invoke($container, $requestedName, $options);
    }
}
