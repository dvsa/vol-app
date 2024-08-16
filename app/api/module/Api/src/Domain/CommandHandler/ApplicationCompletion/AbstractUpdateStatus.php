<?php

/**
 * Abstract Update Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Abstract Update Status
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractUpdateStatus extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    protected $section;

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $completion = $application->getApplicationCompletion();

        $currentStatus = $completion->{'get' . $this->section . 'Status'}();

        if ($this->isSectionValid($application)) {
            $newStatus = ApplicationCompletion::STATUS_COMPLETE;
        } else {
            $newStatus = ApplicationCompletion::STATUS_INCOMPLETE;
        }

        $result = new Result();

        // Statuses are the same so we can bail
        if ($newStatus === $currentStatus) {
            $result->addMessage($this->section . ' section status is unchanged');
        } else {
            $result->addMessage($this->section . ' section status has been updated');
            $completion->{'set' . $this->section . 'Status'}($newStatus);
            $this->getRepo()->save($application);
        }

        return $result;
    }

    abstract protected function isSectionValid(Application $application);
}
