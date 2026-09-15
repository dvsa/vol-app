<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService as QueryService;
use Common\Service\Data\AbstractDataServiceServices;
use Dvsa\Olcs\Transfer\Query\QueryContainerInterface;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class AbstractDataServiceTestCase extends MockeryTestCase
{
    /** @var  m\MockInterface */
    protected $query;

    /** @var  m\MockInterface */
    protected $transferAnnotationBuilder;

    /** @var  m\MockInterface */
    protected $queryService;

    /** @var  m\MockInterface */
    protected $commandService;

    /** @var  AbstractDataServiceServices */
    protected $abstractDataServiceServices;

    protected const SINGLE_EXPECTED = [
        'val-1' => 'Value 1',
        'val-2' => 'Value 2',
        'val-3' => 'Value 3',
    ];

    protected const SINGLE_EXPECTED_WITH_ID = [
        'val-1' => 'val-1 - Value 1',
        'val-2' => 'val-2 - Value 2',
        'val-3' => 'val-3 - Value 3',
    ];

    protected const SINGLE_SOURCE = [
        ['id' => 'val-1', 'description' => 'Value 1'],
        ['id' => 'val-2', 'description' => 'Value 2'],
        ['id' => 'val-3', 'description' => 'Value 3'],
    ];

    #[\Override]
    protected function setUp(): void
    {
        $this->query = m::mock(QueryContainerInterface::class);

        $this->transferAnnotationBuilder = m::mock(TransferAnnotationBuilder::class);
        $this->queryService = m::mock(QueryService::class);
        $this->commandService = m::mock(CommandService::class);

        $this->abstractDataServiceServices = new AbstractDataServiceServices(
            $this->transferAnnotationBuilder,
            $this->queryService,
            $this->commandService
        );
    }

    public function mockHandleQuery($mockResponse, $query = null): void
    {
        $query ??= $this->query;

        $this->queryService->shouldReceive('send')
            ->with($query)
            ->once()
            ->andReturn($mockResponse);
    }
}
