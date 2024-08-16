<?php

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUser;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse\CompanyProfile;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\CompaniesHouse\ProcessInsolvency;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Consumer\TransXChange\TransXChangeConsumer;
use Dvsa\Olcs\Cli\Domain\CommandHandler\MessageQueue\Enqueue;

return [
    Enqueue::class => IsSystemUser::class,
    CompanyProfile::class => IsSystemUser::class,
    ProcessInsolvency::class => IsSystemUser::class,
    TransXChangeConsumer::class => IsSystemUser::class,
];
