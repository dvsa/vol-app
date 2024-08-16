<?php

/**
 * Read Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Case
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadCase extends AbstractReadAudit
{
    protected $repoServiceName = 'CasesReadAudit';

    protected $recordClass = CasesReadAudit::class;

    protected $entityRepo = 'Cases';
}
