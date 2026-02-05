<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\Terminate;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractUpdateDefinedValueTestCase;

class TerminateTest extends AbstractUpdateDefinedValueTestCase
{
    protected $repoServiceName = 'IrhpApplication';
    protected $entityMethodName = 'terminate';
    protected $definedValue = IrhpInterface::STATUS_TERMINATED;
    protected $entityClass = IrhpApplication::class;
    protected $repoClass = IrhpApplicationRepo::class;
    protected $sutClass = Terminate::class;
}
