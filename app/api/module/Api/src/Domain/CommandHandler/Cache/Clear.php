<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cache;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

class Clear extends AbstractCommandHandler implements CacheAwareInterface
{
    use CacheAwareTrait;

    public function handleCommand(CommandInterface $command): Result
    {
        $cacheIds = $command->getCacheIds();

        if (empty($cacheIds)) {
            $this->cacheService->clearAllItems();
            $this->result->addMessage('All caches cleared');
            return $this->result;
        }

        foreach ($cacheIds as $cacheId) {
            try {
                if ($cacheId === CacheEncryption::CQRS_IDENTIFIER) {
                    $this->cacheService->clearCqrsItems();
                    $this->result->addMessage('CQRS caches cleared');
                } elseif ($cacheId === CacheEncryption::DOCTRINE_IDENTIFIER) {
                    $this->cacheService->clearDoctrineItems();
                    $this->result->addMessage('Doctrine caches cleared');
                } elseif (isset(CacheEncryption::CUSTOM_CACHE_TYPE[$cacheId])) {
                    $this->cacheService->clearItemsByType($cacheId);
                    $this->result->addMessage(sprintf('Cache type cleared: %s', $cacheId));
                }
            } catch (\RuntimeException $e) {
                $this->result->addMessage(sprintf('Failed to clear %s: %s', $cacheId, $e->getMessage()));
            }
        }

        return $this->result;
    }
}
