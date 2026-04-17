<?php

/**
 * Delete a Transport Manager Application for TM Responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Delete a Transport Manager Application for TM Responsibilities
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class DeleteForResponsibilities extends AbstractDeleteCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'TransportManagerApplication';

    #[\Override]
    protected function doDelete(array $ids)
    {
        $applications = [];
        foreach ($ids as $id) {
            /** @var TransportManagerApplication $record */
            $record = $this->getRepo()->fetchById($id);
            $application = $record->getApplication();

            $applications[$application->getId()] = $application;
        }

        $result = parent::doDelete($ids);

        /** @var Application $application */
        foreach ($applications as $application) {
            $completionData = ['id' => $application->getId(), 'section' => 'transportManagers'];
            $result->merge($this->handleSideEffect(UpdateApplicationCompletion::create($completionData)));
        }

        return $result;
    }
}
