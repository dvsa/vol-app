<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Command\Batch;

use Dvsa\Olcs\Cli\Domain\Query\CompaniesHouse\Organisations;
use Dvsa\Olcs\Cli\Command\Batch\EnqueueCompaniesHouseCompareCommand;
use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Enqueue;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class EnqueueCompaniesHouseCompareCommandTest extends AbstractBatchCommandCases
{
    use QueryHandlerExceptionTestsTrait;

    protected function getCommandClass(): string
    {
        return EnqueueCompaniesHouseCompareCommand::class;
    }

    protected function getCommandName(): string
    {
        return 'batch:enqueue-companies-house-compare';
    }

    protected function getCommandDTOs(): array
    {
        return [
            Enqueue::create([
                'messageData' => ['org1', 'org2'],
                'queueType' => \Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile::class,
                'messageType' => \Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile::class,
            ]),
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockQueryHandlerManager->method('handleQuery')
            ->willReturnCallback(function ($query) {
                $this->assertInstanceOf(Organisations::class, $query, "Query is not of type Organisation");
                return ['org1', 'org2'];
            });
    }
}
