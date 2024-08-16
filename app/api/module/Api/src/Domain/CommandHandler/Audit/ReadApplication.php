<?php

/**
 * Read Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadApplication extends AbstractReadAudit
{
    protected $repoServiceName = 'ApplicationReadAudit';

    protected $recordClass = ApplicationReadAudit::class;

    protected $entityRepo = 'Application';
}
