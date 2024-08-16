<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\DeleteTaxiPhv as Command;

/**
 * Delete one or more TaxiPhv
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class DeleteTaxiPhv extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Licence'];

    /**
     * @param Command $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $application = $this->getRepo()->fetchUsingId($command);

        $result = new Result();
        $result->merge($this->deletePrivateHireLicence($command));

        if ($application->getLicence()->getPrivateHireLicences()->isEmpty()) {
            $licence = $application->getLicence();
            $licence->setTrafficArea(null);
            $this->getRepo('Licence')->save($licence);
            $result->addMessage("Licence Traffic Area set to null");
        }

        $result->merge($this->updateApplicationCompletion($application));

        return $result;
    }

    /**
     * Create PrivateHireLicence entity
     *
     * @param Application $application
     *
     * @return Result
     */
    private function deletePrivateHireLicence(Command $command)
    {
        $data = [
            'ids' => $command->getIds(),
            'licence' => $command->getLicence(),
            'lva' => $command->getLva()
        ];

        return $this->handleSideEffect(\Dvsa\Olcs\Transfer\Command\PrivateHireLicence\DeleteList::create($data));
    }

    /**
     * Update the ApplicationCompletion
     *
     *
     * @return Result
     */
    private function updateApplicationCompletion(Application $application)
    {
        $data = [
            'id' => $application->getId(),
            'section' => 'taxiPhv',
        ];

        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::create($data)
        );
    }
}
