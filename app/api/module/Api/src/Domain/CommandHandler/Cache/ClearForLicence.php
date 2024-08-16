<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForLicence as ClearCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Clear the caches for a licence
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ClearForLicence extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    public const UPDATE_MSG = 'Cache cleared for Lic No %s, ID %d';

    protected $repoServiceName = 'Licence';

    /**
     * Handle command to clear the cache for an organisation
     *
     * @param CommandInterface|ClearCmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchById($command->getId());
        $this->clearLicenceCaches($licence);

        $message = sprintf(self::UPDATE_MSG, $licence->getLicNo(), $licence->getId());

        $this->result->addMessage($message);

        return $this->result;
    }
}
