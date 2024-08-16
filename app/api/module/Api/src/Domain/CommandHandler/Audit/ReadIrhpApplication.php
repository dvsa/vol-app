<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Audit;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplicationReadAudit;

/**
 * Read IRHP Application
 */
final class ReadIrhpApplication extends AbstractReadAudit
{
    protected $repoServiceName = 'IrhpApplicationReadAudit';

    protected $recordClass = IrhpApplicationReadAudit::class;

    protected $entityRepo = 'IrhpApplication';
}
