<?php

/**
 * Read Organisation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Organisation\OrganisationReadAudit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Read Organisation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ReadOrganisation extends AbstractReadAudit
{
    protected $repoServiceName = 'OrganisationReadAudit';

    protected $recordClass = OrganisationReadAudit::class;

    protected $entityRepo = 'Organisation';
}
