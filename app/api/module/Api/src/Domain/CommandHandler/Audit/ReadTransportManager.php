<?php

/**
 * Read Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Transport Manager
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadTransportManager extends AbstractReadAudit
{
    protected $repoServiceName = 'TransportManagerReadAudit';

    protected $recordClass = TransportManagerReadAudit::class;

    protected $entityRepo = 'TransportManager';
}
