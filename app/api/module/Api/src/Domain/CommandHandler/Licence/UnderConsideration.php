<?php

/**
 * UnderConsideration.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Set a licence to be under consideration.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class UnderConsideration extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchById($command->getId());

        $licence->setStatus(
            $this->getRepo()->getRefdataReference(
                Licence::LICENCE_STATUS_UNDER_CONSIDERATION
            )
        );

        $this->getRepo()->save($licence);

        $licenceId = $licence->getId();
        $result = new Result();
        $result->addMessage('Licence ' . $licenceId . ' has been set to under consideration');
        $result->merge(
            $this->clearLicenceCacheSideEffect($licenceId)
        );

        return $result;
    }
}
