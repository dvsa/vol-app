<?php

namespace Dvsa\Olcs\Api\Domain\Util\DoctrineExtension;

/**
 * Class Logger
 * @package Dvsa\Olcs\Api\Domain\Util\DoctrineExtension
 */
class Logger extends \Doctrine\DBAL\Logging\DebugStack
{
    /**
     * Stop Query
     *
     * @return void
     */
    #[\Override]
    public function stopQuery()
    {
        parent::stopQuery();
        \Olcs\Logging\Log\Logger::debug('SQL Query', ['query' => $this->queries[$this->currentQuery]]);
    }
}
