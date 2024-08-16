<?php

/**
 * Refuse a licence
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
 * Refuse a licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class Refuse extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchById($command->getId());

        $licence->setStatus(
            $this->getRepo()->getRefdataReference(
                Licence::LICENCE_STATUS_REFUSED
            )
        );

        $this->getRepo()->save($licence);

        $result = new Result();
        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );
        $result->addMessage('Licence ' . $licence->getId() . ' refused');

        return $result;
    }
}
