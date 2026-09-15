<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Command\RetrievalLink;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Delete expired Retrieve-via-Link links. Dispatched by the scheduled batch:retrieval-link-purge
 * CLI command. Takes no parameters — "now" is resolved in the handler.
 */
final class PurgeExpired extends AbstractCommand
{
}
