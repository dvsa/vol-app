<?php

/**
 * Delete Submission
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete Submission
 */
final class DeleteSubmission extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Submission';
}
