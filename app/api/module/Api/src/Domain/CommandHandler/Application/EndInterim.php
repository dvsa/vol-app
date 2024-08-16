<?php

/**
 * End interim
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as Cmd;
use Dvsa\Olcs\Transfer\Query\Application\Application;

/**
 * End interim
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class EndInterim extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    /**
     * @param Cmd $command
     */
    public function handleCommand(CommandInterface $command)
    {
        $application = $this->getRepo()->fetchById($command->getId());

        $application->setInterimStatus(
            $this->getRepo()->getRefdataReference(ApplicationEntity::INTERIM_STATUS_ENDED)
        );
        $application->setInterimEnd(new DateTime());

        $this->getRepo()->save($application);
        $this->result->addMessage('Interim status updated');

        return $this->result;
    }
}
