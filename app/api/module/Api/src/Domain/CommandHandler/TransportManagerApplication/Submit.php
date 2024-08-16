<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\TransportManagerSnapshot;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;

/**
 * Submit
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Submit extends AbstractCommandHandler implements TransactionedInterface, EmailAwareInterface
{
    use EmailAwareTrait;
    use TransportManagerSnapshot;

    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = ['TransportManager'];

    /**
     * Handle command
     *
     * @param \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\Submit $command command
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $tma TransportManagerApplication */
        if ($command->getVersion()) {
            $tma = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        } else {
            $tma = $this->getRepo()->fetchUsingId($command);
        }
        $nextStatus = $command->getNextStatus();
        if (is_null($nextStatus)) {
            $nextStatus = ($tma->getIsOwner() === 'Y') ? TransportManagerApplication::STATUS_OPERATOR_SIGNED :
                TransportManagerApplication::STATUS_TM_SIGNED;
        }

        $tma->setTmApplicationStatus($this->getRepo()->getRefdataReference($nextStatus));
        $this->getRepo()->save($tma);

        if ($nextStatus === TransportManagerApplication::STATUS_TM_SIGNED) {
            $this->sendSubmittedEmail($tma);
        } elseif ($tma->getTransportManager()->getTmType() === null) {
            $this->updateTmType($tma->getTransportManager(), $tma->getTmType());
        }

        if ($this->shouldCreateTask($tma->getApplication()->getStatus())) {
            $taskResult = $this->createTask($tma->getApplication());
            $this->result->addMessage($taskResult->getMessages()[0]);
        }

        $this->result->addMessage("Transport Manager Application ID {$tma->getId()} submitted");

        if ($this->shouldCreateSnapshot($nextStatus)) {
            $this->result->merge($this->createSnapshot($tma->getId(), $tma->getTransportManager()->getId()));
        }

        return $this->result;
    }

    /**
     * Send an email to the Organisations admins and TMA creator
     *
     * @param TransportManagerApplication $tma tma
     *
     * @return void
     */
    private function sendSubmittedEmail(TransportManagerApplication $tma)
    {
        $recipients = $this->getRecipients($tma);

        if (!empty($recipients)) {
            $emailData = [
                'tmFullName' => $tma->getTransportManager()->getHomeCd()->getPerson()->getFullName(),
                'licNo' => $tma->getApplication()->getLicence()->getLicNo(),
                'applicationId' => $tma->getApplication()->getId(),
                'tmaUrl' => sprintf(
                    'http://selfserve/%s/%d/transport-managers/details/%d/',
                    $tma->getApplication()->getIsVariation() ? 'variation' : 'application',
                    $tma->getApplication()->getId(),
                    $tma->getId()
                ),
            ];

            foreach ($recipients as $user) {
                $message = new \Dvsa\Olcs\Email\Data\Message(
                    $user->getContactDetails()->getEmailAddress(),
                    'email.transport-manager-submitted-form.subject'
                );
                $message->setTranslateToWelsh($user->getTranslateToWelsh());

                $this->sendEmailTemplate(
                    $message,
                    'transport-manager-submitted-form',
                    $emailData
                );
            }
        }
    }

    /**
     * Get list of users who should receive the email
     *
     * @param TransportManagerApplication $tma tma
     *
     * @return array Array of User indexed by id
     */
    private function getRecipients(TransportManagerApplication $tma)
    {
        $users = [];

        foreach ($tma->getApplication()->getLicence()->getOrganisation()->getAdminOrganisationUsers() as $orgUser) {
            $user = $orgUser->getUser();

            $users[$user->getId()] = $user;
        }

        if ($tma->getCreatedBy()) {
            // add TMA creator to the list
            $users[$tma->getCreatedBy()->getId()] = $tma->getCreatedBy();
        }

        return $users;
    }

    /**
     * Update tm type
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $tm     transport manager
     * @param string                                    $tmType transport manager type
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     * @return void
     */
    protected function updateTmType(TransportManager $tm, $tmType)
    {
        $tm->setTmType($this->getRepo()->getRefdataReference($tmType));
        $this->getRepo('TransportManager')->save($tm);
    }

    private function shouldCreateSnapshot($status): bool
    {
        return ($status === TransportManagerApplication::STATUS_OPERATOR_SIGNED) || ($status === TransportManagerApplication::STATUS_RECEIVED);
    }

    /**
     *
     *
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    private function createTask(Application $application)
    {
        $taskData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_TM1_DIGITAL,
            'description' => 'Transport Manager form submitted',
            'isClosed' => 'N',
            'urgent' => 'N',
            'application' => $application->getId(),
            'licence' => $application->getLicence()->getId()
        ];

        return $this->handleSideEffect(CreateTask::create($taskData));
    }

    private function shouldCreateTask(RefData $status): bool
    {
        return $status->getId() === Application::APPLICATION_STATUS_UNDER_CONSIDERATION;
    }
}
