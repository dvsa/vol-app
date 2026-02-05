<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\VehicleDeclaration as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepository;
use Dvsa\Olcs\Transfer\Query\Application\VehicleDeclaration as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class VehicleDeclarationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', ApplicationRepository::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1066]);
        $isOperatingSmallPsvAsPartOfLarge = true;

        $expected = [
            0 => 'RESULT',
            'isOperatingSmallPsvAsPartOfLarge' => $isOperatingSmallPsvAsPartOfLarge,
        ];

        $mock = \Mockery::mock(BundleSerializableInterface::class);
        $mock->expects('serialize')->with(
            [
                'licence' => [
                    'trafficArea'
                ]
            ]
        )->andReturn($expected);
        $mock->expects('isOperatingSmallPsvAsPartOfLarge')->withNoArgs()->andReturn($isOperatingSmallPsvAsPartOfLarge);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mock);

        $this->assertSame($expected, $this->sut->handleQuery($query)->serialize());
    }
}
