<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\Expire as ExpireIrhpApplication;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Terminate Permit
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class Terminate extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpPermit';

    /**
     * Handle terminate permit command
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $permit = $this->getRepo()->fetchById($command->getId());

        try {
            // update the status
            $permit->proceedToStatus($this->refData(IrhpPermit::STATUS_TERMINATED));
        } catch (ForbiddenException) {
            $this->result->addMessage('You cannot terminate an inactive permit.');
            return $this->result;
        }

        $this->getRepo()->save($permit);

        $this->result->addId('IrhpPermit', $permit->getId());
        $this->result->addMessage('The selected permit has been terminated.');

        $application = $permit->getIrhpPermitApplication()->getIrhpApplication();

        if ($application->canBeExpired()) {
            // set the application as expired
            $data = [
                'id' => $application->getId()
            ];

            $command = ExpireIrhpApplication::create($data);

            $this->result->merge($this->handleSideEffect($command));
        }

        return $this->result;
    }
}
