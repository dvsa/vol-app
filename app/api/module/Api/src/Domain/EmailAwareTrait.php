<?php

namespace Dvsa\Olcs\Api\Domain;

use Doctrine\ORM\EntityNotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\MissingEmailException;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Email\Data\Message;

/**
 * Email Aware
 */
trait EmailAwareTrait
{
    /**
     * @var TemplateRenderer
     */
    protected $templateRendererService;

    /**
     * @return void
     */
    public function setTemplateRendererService(TemplateRenderer $service)
    {
        $this->templateRendererService = $service;
    }

    /**
     * @return TemplateRenderer
     */
    public function getTemplateRendererService()
    {
        return $this->templateRendererService;
    }

    /**
     * Send an email
     *
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Email\Exception\EmailNotSentException
     */
    public function sendEmail(Message $message)
    {
        return $this->handleSideEffect($message->buildCommand());
    }

    /**
     * Send an email in a HTML template
     *
     * @param string|array $template
     * @param string       $layout
     * @return true
     * @throws \Dvsa\Olcs\Email\Exception\EmailNotSentException
     */
    public function sendEmailTemplate(Message $message, $template, array $variables = [], $layout = 'default')
    {
        $this->getTemplateRendererService()->renderBody($message, $template, $variables, $layout);
        return $this->sendEmail($message);
    }

    /**
     * In theory (although probably not in practice) both the user and the organisation emails could be empty
     * Need to decide what to do in those situations (throw an exception here?)
     *
     *
     * @return array
     * @throws MissingEmailException
     */
    public function organisationRecipients(Organisation $organisation, ?User $user = null): array
    {
        $toEmail = '';
        $orgEmailAddresses = $organisation->getAdminEmailAddresses();

        //on rare occasions a user may have been soft deleted, or may not have contact details
        try {
            if ($user instanceof User && !$user->isInternal()) {
                $contactDetails = $user->getContactDetails();

                if ($contactDetails instanceof ContactDetails) {
                    $toEmail = $contactDetails->getEmailAddress();
                }
            }
        } catch (EntityNotFoundException) {
            // user or contact details entry may have been soft deleted and lazy loading can throw
        }

        if (empty($toEmail) && !empty($orgEmailAddresses)) {
            $toEmail = $orgEmailAddresses[0];
            unset($orgEmailAddresses[0]);
        }

        if (empty($toEmail)) {
            throw new MissingEmailException(MissingEmailException::MSG_NO_ORG_EMAIL);
        }

        return [
            'to' => $toEmail,
            'cc' => $orgEmailAddresses,
            'bcc' => []
        ];
    }
}
