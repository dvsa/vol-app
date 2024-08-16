<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete EBSR Submission
 */
final class DeleteSubmission extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'EbsrSubmission';
}
