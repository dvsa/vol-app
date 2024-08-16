<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\ReviveFromUnsuccessful;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;

class ReviveFromUnsuccessfulTest extends AbstractReviveFromUnsuccessfulTest
{
    protected $applicationRepoServiceName = 'IrhpApplication';
    protected $applicationRepoClass = IrhpApplicationRepo::class;
    protected $sutClass = ReviveFromUnsuccessful::class;
    protected $entityClass = IrhpApplication::class;
}
