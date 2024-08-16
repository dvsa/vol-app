<?php

/**
 * Delete Environmental Complaint
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\EnvironmentalComplaint;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete Environmental Complaint
 */
final class DeleteEnvironmentalComplaint extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Complaint';
}
