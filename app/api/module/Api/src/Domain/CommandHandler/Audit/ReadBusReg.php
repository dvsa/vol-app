<?php

/**
 * Read Bus Reg
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Bus\BusRegReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Bus Reg
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadBusReg extends AbstractReadAudit
{
    protected $repoServiceName = 'BusRegReadAudit';

    protected $recordClass = BusRegReadAudit::class;

    protected $entityRepo = 'Bus';
}
