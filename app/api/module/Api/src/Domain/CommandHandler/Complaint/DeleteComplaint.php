<?php

/**
 * Delete Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Complaint;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete Complaint
 */
final class DeleteComplaint extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Complaint';
}
