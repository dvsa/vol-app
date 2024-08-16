<?php

/**
 * Send Ebsr Email Abstract
 *
 * @author Craig R <uk@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Email;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as Repository;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrCancelled as CancelCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRegistered as RegCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrWithdrawn as WithdrawnCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefused as RefusedCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrReceived as ReceivedCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefreshed as RefreshedCmd;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRequestMap as RequestMapCmd;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Email\Data\Message;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Doctrine\Common\Collections\Collection as CollectionInterface;

/**
 * Send Ebsr Email Abstract
 *
 * @author Craig R <uk@valtech.co.uk>
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class SendEbsrAbstract extends AbstractCommandHandler implements EmailAwareInterface
{
    use EmailAwareTrait;

    public const DATE_FORMAT = 'l F jS Y';

    public const UNKNOWN_REG_NO = 'unknown reg no';

    protected $repoServiceName = 'EbsrSubmission';

    protected $extraRepos = ['BusRegSearchView'];

    protected $template = null;

    /**
     * @var EbsrSubmissionEntity
     */
    protected $ebsr;

    /**
     * @var BusRegEntity
     */
    protected $busReg;

    /**
     * @var array
     */
    protected $submissionResult;

    /**
     * @var array
     */
    protected $emailData;

    /**
     * @var string
     */
    protected $regNo;

    /**
     * @var string
     */
    protected $pdfType;

    /**
     * @var Message
     */
    protected $message;

    /**
     * Handles the command
     *
     * @param CommandInterface|CancelCmd|RegCmd|WithdrawnCmd|RefusedCmd|ReceivedCmd|RefreshedCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var $repo Repository $repo */
        $repo = $this->getRepo();
        $this->ebsr = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT, null);
        $this->busReg = $this->ebsr->getBusReg();
        $this->submissionResult = $this->ebsr->getDecodedSubmissionResult();

        //request map only
        $this->pdfType = ($command instanceof RequestMapCmd ? $command->getPdfType() : null);

        //get template variables
        $this->emailData = $this->getTemplateVariables($command);

        //get the bus regNo (which could come from a number of sources)
        $this->regNo = $this->getBusRegNo();

        $this->message = $this->buildMessage();

        $this->sendEmailTemplate($this->message, $this->template, $this->emailData);

        $result = new Result();
        $result->addId('ebsrSubmission', $this->ebsr->getId());
        $result->addMessage('Email sent');

        return $result;
    }

    /**
     * Returns the message object, used to assist with UT
     *
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Gets the bus registration number, works differently depending on where we get the data from
     *
     * @return null|string
     */
    private function getBusRegNo()
    {
        //if the submission failed, we won't always have a bus reg
        if ($this->busReg instanceof BusRegEntity) {
            return $this->busReg->getRegNo();
        } elseif ($this->emailData['registrationNumber'] !== self::UNKNOWN_REG_NO) {
            return $this->emailData['registrationNumber'];
        }

        return null;
    }

    /**
     * Gets the email message
     *
     * @return Message
     */
    private function buildMessage()
    {
        $localAuthoritiesCc = [];

        //if the submission failed, we won't always have a bus reg
        if ($this->busReg instanceof BusRegEntity) {
            //get local auth data (list of local auths copied, and their email addresses)
            $localAuthoritiesCc = $this->getLocalAuthEmails($this->busReg->getLocalAuthoritys());
        }

        //organisation address
        $orgAddress = $this->ebsr->getOrganisationEmailAddress();

        //administrator emails
        $administratorEmails = $this->ebsr->getOrganisation()->getAdminEmailAddresses();

        //org address will be blank or else validated on ebsr submission
        if (!$orgAddress) {
            $orgAddress = $administratorEmails[0];
            unset($administratorEmails[0]);
        }

        //get the email message, and assign addresses
        $message = new Message($orgAddress, $this->getSubject());
        $message->setCc(array_merge($localAuthoritiesCc, $administratorEmails));

        //email subject line
        $message->setSubjectVariables($this->getSubjectVars());

        return $message;
    }

    /**
     * Gets subject line variables, depends on whether the bus reg and pdf type is included or not
     *
     * @return array
     */
    private function getSubjectVars()
    {
        //map requests only
        if ($this->pdfType !== null) {
            return [$this->pdfType, $this->regNo, $this->ebsr->getId()];
        }

        if ($this->regNo) {
            return [$this->regNo, $this->ebsr->getId()];
        }

        return [$this->ebsr->getId()];
    }

    /**
     * Decides on the subject line. If template is a string then it is easy to match it. Failure emails use an array
     * of templates, so we do it based on whether we have a bus reg entity
     *
     * @return string
     */
    private function getSubject()
    {
        $subject = 'email.ebsr-failed-no-bus-reg.subject';

        if (!is_array($this->template)) {
            $subject = 'email.' . $this->template . '.subject';
        } elseif ($this->regNo) {
            $subject = 'email.ebsr-failed.subject';
        }

        return $subject;
    }

    /**
     * Gets template variables. If we have no busReg, then we use the extra_bus_data field instead
     *
     * @param CommandInterface $command the command
     *
     * @return array
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function getTemplateVariables($command)
    {
        $submissionErrors = $this->submissionResult['errors'] ?? [];

        //if the submission failed, we won't have a bus reg, so use any data the pack processor was able to extract
        if (!$this->busReg instanceof BusRegEntity) {
            $emailData = $this->submissionResult['extra_bus_data'];
            $emailData['submissionErrors'] = $submissionErrors;

            return $emailData;
        }

        $emailData = [
            'submissionDate' => $this->formatDate($this->ebsr->getSubmittedDate()),
            'registrationNumber' => $this->busReg->getRegNo(),
            'origin' => $this->busReg->getStartPoint(),
            'destination' => $this->busReg->getFinishPoint(),
            'lineName' => $this->busReg->getFormattedServiceNumbers(),
            'startDate' => $this->formatDate($this->busReg->getEffectiveDate()),
            'localAuthoritys' => $this->getLocalAuthString($this->busReg->getLocalAuthoritys()),
            'submissionErrors' => $submissionErrors,
            'hasBusData' => true,
            'publicationId' => null,
            'pdfType' => null
        ];

        //when ebsr bus reg is registered or cancelled, we send additional publication data
        if (($command instanceof RegCmd || $command instanceof CancelCmd)) {
            $pubSection = $this->busReg->getPublicationSectionForGrantEmail();

            if ($pubSection) {
                $pubSectionEntity = $this->getRepo()->getReference(PublicationSectionEntity::class, $pubSection);
                $emailData['publicationId'] = $this->busReg->getPublicationLinksForGrantEmail($pubSectionEntity);
            }
        }

        //if command is a request map command, we need to know which one we are generating.
        if ($command instanceof RequestMapCmd) {
            $emailData['pdfType'] = $command->getPdfType();
        }

        return $emailData;
    }

    /**
     * Returns array of local authority emails to cc
     *
     * @param CollectionInterface $localAuths local authorities
     *
     * @return array
     */
    private function getLocalAuthEmails(CollectionInterface $localAuths)
    {
        $localAuthoritiesCc = [];

        /** @var LocalAuthority $localAuth */
        foreach ($localAuths as $localAuth) {
            //main local authority email address
            $localAuthoritiesCc[] = $localAuth->getEmailAddress();

            //retrieve other local authority users and add their email addresses
            $localAuthUsers = $localAuth->getUsers();

            /** @var UserEntity $user */
            foreach ($localAuthUsers as $user) {
                $contactDetails = $user->getContactDetails();

                if ($contactDetails instanceof ContactDetailsEntity) {
                    $localAuthoritiesCc[] = $contactDetails->getEmailAddress();
                }
            }
        }

        return $localAuthoritiesCc;
    }

    /**
     * Returns a comma separated list of local authorities
     *
     * @param CollectionInterface $localAuths local authorities
     *
     * @return string
     */
    private function getLocalAuthString(CollectionInterface $localAuths)
    {
        $localAuthoritiesList = [];

        /** @var LocalAuthority $localAuth */
        foreach ($localAuths as $localAuth) {
            $localAuthoritiesList[] = $localAuth->getDescription();
        }

        return implode(', ', $localAuthoritiesList);
    }

    /**
     * Formats the date according to the specified format
     *
     * @param string|\DateTime $date the date
     *
     * @return string
     */
    private function formatDate($date)
    {
        if (!$date instanceof \DateTime) {
            return date(self::DATE_FORMAT, strtotime($date));
        }

        return $date->format(self::DATE_FORMAT);
    }
}
