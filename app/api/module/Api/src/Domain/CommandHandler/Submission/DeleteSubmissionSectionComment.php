<?php

/**
 * Delete SubmissionSectionComment
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Submission;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete SubmissionSectionComment
 */
final class DeleteSubmissionSectionComment extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'SubmissionSectionComment';
}
