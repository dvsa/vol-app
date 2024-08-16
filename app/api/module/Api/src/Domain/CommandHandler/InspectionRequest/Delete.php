<?php

/**
 * Inspection Request / Delete
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Inspection Request / Delete
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'InspectionRequest';
}
