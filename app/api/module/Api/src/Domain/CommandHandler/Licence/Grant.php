<?php

/**
 * Grant a licence
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
 * Grant a licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class Grant extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchById($command->getId());

        $licence->setStatus(
            $this->getRepo()->getRefdataReference(
                Licence::LICENCE_STATUS_GRANTED
            )
        );

        $this->getRepo()->save($licence);

        $result = new Result();
        $result->addMessage('Licence ' . $licence->getId() . ' has been granted');
        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        return $result;
    }
}
