<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Cache\ClearForOrganisation as ClearCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Clear the caches for an organisation
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ClearForOrganisation extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    public const UPDATE_MSG = 'Cache cleared for %s, Organisation ID %d';

    protected $repoServiceName = 'Organisation';

    /**
     * Handle command to clear the cache for an organisation
     *
     * @param CommandInterface|ClearCmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var Organisation $organisation */
        $organisation = $this->getRepo()->fetchById($command->getId());
        $this->clearOrganisationCaches($organisation);

        $message = sprintf(self::UPDATE_MSG, $organisation->getName(), $organisation->getId());

        $this->result->addMessage($message);

        return $this->result;
    }
}
