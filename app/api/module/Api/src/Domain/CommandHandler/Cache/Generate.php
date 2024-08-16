<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\Command\Cache\Generate as GenerateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Query\Cache\ById;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Generate a cache based on the provided details
 * Essentially this just acts as a wrapper to run the appropriate query
 * At the point the query is run it will also repopulate the necessary caches
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Generate extends AbstractCommandHandler
{
    public const UPDATE_MSG = 'Cache updated for %s without a unique id';
    public const UPDATE_UNIQUE_MSG = 'Cache updated for %s with unique id of %s';
    public const UPDATE_ERROR_MSG = 'Cache update failed for %s with error message: %s';

    /**
     * Handle command to generate a cache
     *
     * @param CommandInterface|GenerateCmd $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $cacheId = $command->getId();
        $uniqueId = $command->getUniqueId();

        $queryParams = [
            'id' => $cacheId,
            'uniqueId' => $uniqueId,
            'shouldRegen' => true,
        ];

        $cacheQuery = ById::create($queryParams);

        try {
            $this->handleQuery($cacheQuery);

            if ($uniqueId) {
                $message = sprintf(self::UPDATE_UNIQUE_MSG, $cacheId, $uniqueId);
            } else {
                $message = sprintf(self::UPDATE_MSG, $cacheId);
            }
        } catch (\Exception $e) {
            $message = sprintf(self::UPDATE_ERROR_MSG, $cacheId, $e->getMessage());
            Logger::err($message);
        }

        $this->result->addMessage($message);

        return $this->result;
    }
}
