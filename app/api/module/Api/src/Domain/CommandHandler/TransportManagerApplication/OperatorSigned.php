<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\TransportManagerSnapshot;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Domain\EmailAwareInterface;
use Dvsa\Olcs\Api\Domain\EmailAwareTrait;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Olcs\Logging\Log\Logger;

final class OperatorSigned extends AbstractCommandHandler implements TransactionedInterface, EmailAwareInterface
{
    use EmailAwareTrait;
    use TransportManagerSnapshot;

    protected $repoServiceName = 'TransportManagerApplication';

    protected $extraRepos = ['TransportManager'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
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

        $tma->setTmApplicationStatus(
            $this->getRepo()->getRefdataReference(TransportManagerApplication::STATUS_OPERATOR_SIGNED)
        );
        $this->getRepo()->save($tma);

        if ($tma->getTransportManager()->getTmType() === null) {
            $this->updateTmType($tma->getTransportManager(), $tma->getTmType());
        }

        $this->result->addMessage("Transport Manager Application ID {$tma->getId()} operator approved");
        if ($tma->getTransportManager()->getHomeCd()->getEmailAddress() !== null) {
            $this->sendConfirmedEmail($tma);
            $this->result->addMessage('Email is sent.');
        } else {
            Logger::warn('Empty email address for TM ' . $tma->getTransportManager()->getId());
            $this->result->addMessage('Email is not sent.');
        }

        $this->result->merge($this->createSnapshot($tma->getId(), $tma->getTransportManager()->getId()));

        return $this->result;
    }

    /**
     * Send an email to the TM
     *
     * @param TransportManagerApplication $tma tma
     *
     * @return void
     */
    private function sendConfirmedEmail(TransportManagerApplication $tma)
    {
        $message = new \Dvsa\Olcs\Email\Data\Message(
            $tma->getTransportManager()->getHomeCd()->getEmailAddress(),
            'email.transport-manager-confirmed.subject'
        );
        $message->setTranslateToWelsh($tma->getApplication()->getLicence()->getTranslateToWelsh());

        $this->sendEmailTemplate(
            $message,
            'transport-manager-confirmed',
            [
                'operatorName' => $tma->getApplication()->getLicence()->getOrganisation()->getName(),
                'licNo' => $tma->getApplication()->getLicence()->getLicNo(),
                'applicationId' => $tma->getApplication()->getId(),
                'tmaUrl' => sprintf(
                    'http://selfserve/application/%d/transport-managers/details/%d/',
                    $tma->getApplication()->getId(),
                    $tma->getId()
                ),
            ]
        );
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
}
