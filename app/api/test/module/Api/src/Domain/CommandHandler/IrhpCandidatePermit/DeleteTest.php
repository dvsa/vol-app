<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpCandidatePermit\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Delete as DeleteCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractDeleteCommandHandlerTestCase;

/**
 * Delete IRHP Candidate Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class DeleteTest extends AbstractDeleteCommandHandlerTestCase
{
    protected $cmdClass = DeleteCmd::class;
    protected $sutClass = DeleteHandler::class;
    protected $repoServiceName = 'IrhpCandidatePermit';
    protected $repoClass = IrhpCandidatePermitRepo::class;
    protected $entityClass = IrhpCandidatePermitEntity::class;
}
