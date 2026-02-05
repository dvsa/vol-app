<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\Cancel as CancelIrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCancelApplicationHandlerTestCase;

class CancelTest extends AbstractCancelApplicationHandlerTestCase
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityClass = IrhpApplicationEntity::class;
    protected $repoClass = IrhpApplicationRepo::class;
    protected $sutClass = CancelIrhpApplication::class;
    protected $cancelStatus = IrhpInterface::STATUS_CANCELLED;
}
