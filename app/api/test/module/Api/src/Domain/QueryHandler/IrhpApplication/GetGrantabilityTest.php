<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\GetGrantability as GetGrantabilityHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Permits\GrantabilityChecker;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\GetGrantability as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Get Grantability Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class GetGrantabilityTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GetGrantabilityHandler();
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsGrantabilityChecker' => m::mock(GrantabilityChecker::class),
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQuery')]
    public function testHandleQuery(mixed $isGrantable, mixed $canBeGranted, mixed $expected): void
    {
        $query = QryClass::create(['id' => 100011]);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with($query->getId())
            ->andReturn($irhpApplication);

        $this->mockedSmServices['PermitsGrantabilityChecker']->shouldReceive('isGrantable')
            ->with($irhpApplication)
            ->andReturn($isGrantable);

        $irhpApplication->shouldReceive('canBeGranted')
            ->once()
            ->withNoArgs()
            ->andReturn($canBeGranted);

        $this->assertEquals(
            $expected,
            $this->sut->handleQuery($query)
        );
    }

    public static function dpTestHandleQuery(): array
    {
        return [
            [
                true,
                true,
                [
                    'grantable' => 1,
                    'message' => '',
                ]
            ],
            [
                true,
                false,
                [
                    'grantable' => 0,
                    'message' => 'IRHP Application can not be granted',
                ]
            ],
            [
                false,
                true,
                [
                    'grantable' => 0,
                    'message' => 'Application requests too many permits from a range',
                ]
            ]
        ];
    }
}
