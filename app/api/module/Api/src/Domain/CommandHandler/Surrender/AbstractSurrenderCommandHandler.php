<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;

abstract class AbstractSurrenderCommandHandler extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface, ToggleRequiredInterface
{
    use AuthAwareTrait;
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::BACKEND_SURRENDER];

    protected $repoServiceName = 'Surrender';

    protected function getSurrender($licenceId)
    {
        return $this->getRepo('Surrender')->fetchOneByLicenceId($licenceId, Query::HYDRATE_OBJECT);
    }

    protected function handleEventHistory(Licence $licence, string $eventHistoryType)
    {
        $this->extraRepos[] = 'EventHistory';
        $this->extraRepos[] = 'EventHistoryType';

        $eventType = $this->getRepo('EventHistoryType')
            ->fetchOneByEventCode($eventHistoryType);

        // create event history record
        $eventHistory = new EventHistory(
            $this->getUser(),
            $eventType
        );
        $eventHistory->setLicence($licence);
        $eventHistory->setEntityType('licence');
        $eventHistory->setEntityPk($licence->getId());

        $this->getRepo('EventHistory')->save($eventHistory);
        $this->result->addMessage('Event history added for licence ' . $licence->getId());
    }
}
