<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\ReviveFromUnsuccessful;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class ReviveFromUnsuccessfulTest extends AbstractReviveFromUnsuccessfulTestCase
{
    protected $applicationRepoServiceName = 'IrhpApplication';
    protected $applicationRepoClass = IrhpApplicationRepo::class;
    protected $sutClass = ReviveFromUnsuccessful::class;
    protected $entityClass = IrhpApplication::class;
}
