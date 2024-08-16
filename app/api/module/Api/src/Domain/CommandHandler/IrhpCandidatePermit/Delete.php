<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete an IRHP Candidate Permit
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'IrhpCandidatePermit';
}
