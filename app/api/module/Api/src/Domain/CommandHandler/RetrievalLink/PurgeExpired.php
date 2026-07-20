<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Olcs\Logging\Log\Logger;

/**
 * Deletes expired retrieval links. Child rows (documents, OTPs, audit events) are removed by the
 * database ON DELETE CASCADE foreign keys, so a single bulk delete is enough.
 */
final class PurgeExpired extends AbstractCommandHandler
{
    protected $repoServiceName = 'RetrievalLink';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        $deleted = $this->getRepo()->deleteExpired(new \DateTimeImmutable());

        Logger::info(sprintf('Retrieve-via-Link purge: deleted %d expired link(s)', $deleted));
        $this->result->addMessage(sprintf('Purged %d expired retrieval link(s)', $deleted));

        return $this->result;
    }
}
