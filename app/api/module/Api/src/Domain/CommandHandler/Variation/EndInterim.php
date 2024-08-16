<?php

/**
 * End interim for variations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * End interim for variations
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EndInterim extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $licence = $this->getRepo('Licence')->fetchWithVariationsAndInterimInforce($command->getLicenceId());
        if (count($licence)) {
            $licence = $licence[0];
            $ids = [];
            foreach ($licence->getApplications() as $application) {
                $application->setInterimStatus(
                    $this->getRepo()->getRefdataReference(ApplicationEntity::INTERIM_STATUS_ENDED)
                );
                $application->setInterimEnd(new DateTime());
                $ids[] = $application->getId();
                $this->getRepo()->save($application);
            }
            $result->addMessage('Interim ended for variations with ids: ' . implode(', ', $ids));
        } else {
            $result->addMessage('No variations with interim status in force found');
        }

        return $result;
    }
}
