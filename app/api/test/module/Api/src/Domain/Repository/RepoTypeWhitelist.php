<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Dvsa\Olcs\Api\Domain\Repository\DataDvaNi;
use Dvsa\Olcs\Api\Domain\Repository\DataGovUk;
use Dvsa\Olcs\Api\Domain\Repository\GetDbValue;
use Dvsa\Olcs\Api\Domain\Repository\ReadonlyRepositoryInterface;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;

class RepoTypeWhitelist
{
    public const TYPES = [
        CompaniesHouseVsOlcsDiffs::class,
        DataDvaNi::class,
        DataGovUk::class,
        GetDbValue::class,
        ReadonlyRepositoryInterface::class,
        RepositoryInterface::class,
    ];
}
