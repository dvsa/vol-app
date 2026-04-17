<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitRange\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\Olcs\Transfer\Command\IrhpPermitRange\Delete as DeleteCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as PermitRangeEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractDeleteCommandHandlerTestCase;

/**
 * Create IRHP Permit Range Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class DeleteTest extends AbstractDeleteCommandHandlerTestCase
{
    protected $cmdClass = DeleteCmd::class;
    protected $sutClass = DeleteHandler::class;
    protected $repoServiceName = 'IrhpPermitRange';
    protected $repoClass = PermitRangeRepo::class;
    protected $entityClass = PermitRangeEntity::class;
}
