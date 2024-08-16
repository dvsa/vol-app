<?php

/**
 * Read Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Licence\LicenceReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadLicence extends AbstractReadAudit
{
    protected $repoServiceName = 'LicenceReadAudit';

    protected $recordClass = LicenceReadAudit::class;

    protected $entityRepo = 'Licence';
}
