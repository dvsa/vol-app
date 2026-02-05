<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Dvsa\Olcs\Api\Domain\Command\Email\SendErruErrors as SendErruErrorsCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository\ErruRequestFailure as ErruRequestFailureRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure as ErruRequestFailureEntity;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Send Erru Email
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SendErruErrors extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    public const EMAIL_ADDRESS = 'ERRU-UK@vosa.gsi.gov.uk';
    public const EMAIL_TEMPLATE = 'erru-failure';
    public const EMAIL_SUBJECT = 'email.erru-errors.subject';
    public const SUBJECT_DATE_FORMAT = 'd/m/Y';
    public const BODY_DATE_FORMAT = 'd/m/Y H:i:s';
    public const MISSING_INPUT = 'Unknown';
    public const UNKNOWN_DATE = 'unknown date';
    public const UNKNOWN_BUSINESS_CASE = 'Unknown business case ID';

    /**
     * @var string
     */
    protected $repoServiceName = 'ErruRequestFailure';

    /**
     * @var Message
     */
    protected $message;

    /**
     * Handle command to send error email for an erru request failure
     *
     * @param CommandInterface|SendErruErrorsCmd $command the command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var ErruRequestFailureRepo $repo
         * @var ErruRequestFailureEntity $erruFailure
         */
        $repo = $this->getRepo();
        $erruFailure = $repo->fetchUsingId($command);

        //we will always have errors saved
        $errors = json_decode((string) $erruFailure->getErrors(), true);

        //we won't always have input data saved e.g. when the XML file couldn't be parsed
        $jsonInputData = $erruFailure->getInput();
        $input = [];

        if (!empty($jsonInputData)) {
            $input = json_decode((string) $jsonInputData, true);
        }

        //get the email message, and assign addresses
        $this->message = new Message(self::EMAIL_ADDRESS, self::EMAIL_SUBJECT);

        //email subject line
        $this->message->setSubjectVariables($this->getSubjectVars($input));

        //document
        $document = $erruFailure->getDocument();
        $this->message->setDocs([$document->getId()]);

        $templateVars = $this->getTemplateVars($document, $input, $errors);
        $this->sendEmailTemplate($this->message, self::EMAIL_TEMPLATE, $templateVars);

        $result = new Result();
        $result->addId('erruRequestFailure', $erruFailure->getId());
        $result->addMessage('Email sent');

        return $result;
    }

    /**
     * get template variables for the email
     *
     * @param DocumentEntity $document erru request document
     * @param array          $input    array of erru input data
     * @param array          $errors   array of erru error data
     *
     * @return array
     */
    private function getTemplateVars(DocumentEntity $document, array $input, array $errors)
    {
        $filename = basename($document->getFilename());
        $notificationNumber =
            ($input['notificationNumber'] ?? self::MISSING_INPUT);
        $memberStateCode =
            ($input['memberStateCode'] ?? self::MISSING_INPUT);
        $originatingAuthority =
            ($input['originatingAuthority'] ?? self::MISSING_INPUT);
        $sentAt = self::MISSING_INPUT;

        if (isset($input['sentAt'])) {
            //providing we have a date, we know the format is OK as it has been enforced by the XML schema
            $sentDateTime = new \DateTime($input['sentAt']);
            $sentAt = $sentDateTime->format(self::BODY_DATE_FORMAT);
        }

        return [
            'sentAt' => $sentAt,
            'notificationNumber' => $notificationNumber,
            'memberState' => $memberStateCode,
            'originatingAuthority' => $originatingAuthority,
            'errorMessages' => $errors,
            'filename' => $filename
        ];
    }

    /**
     * Gets subject line variables
     *
     * @param array $input erru input data
     *
     * @return array
     */
    private function getSubjectVars($input)
    {
        $date = self::UNKNOWN_DATE;
        $businessCase =
            $input['notificationNumber'] ?? self::UNKNOWN_BUSINESS_CASE;

        if (isset($input['sentAt'])) {
            //providing we have a date, we know the format is OK as it has been enforced by the XML schema
            $dateTime = new \DateTime($input['sentAt']);
            $date = $dateTime->format(self::SUBJECT_DATE_FORMAT);
        }

        return [$date, $businessCase];
    }

    /**
     * Returns the generated message
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}
