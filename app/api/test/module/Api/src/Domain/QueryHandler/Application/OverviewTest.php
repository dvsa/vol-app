<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Overview;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Opposition as OppositionRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Query\Application\Overview as Qry;
use Dvsa\Olcs\Transfer\Query\Licence\Overview as LicenceOverviewQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(Overview::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Fee', FeeRepo::class);
        $this->mockRepo('Opposition', OppositionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicationId = 111;
        $licenceId = 7;
        $applicableAuthProperties = [
            'totAuthVehicles',
            'totAuthTrailers',
        ];

        $query = Qry::create(['id' => $applicationId, 'validateAppCompletion' => true]);

        $mockApplication = m::mock(ApplicationEntity::class)->makePartial();
        $mockApplication
            ->setId($applicationId)
            ->shouldReceive('serialize')
            ->andReturn(['id' => $applicationId]);
        $mockApplication
            ->shouldReceive('getLicence->getId')
            ->andReturn($licenceId);
        $mockApplication->shouldReceive('getOutOfOppositionDate')->with()->once()->andReturn('OOOD');
        $mockApplication->shouldReceive('getOutOfRepresentationDate')->with()->once()
            ->andReturn(new \DateTime('2015-07-27'));
        $mockApplication->shouldReceive('getOperatingCentresNetDelta')->once()->andReturn(9);
        $mockApplication
            ->shouldReceive('getActiveVehicles')
            ->once()
            ->andReturn(
                [
                    m::mock()
                        ->shouldReceive('serialize')
                        ->andReturn(['vehicle1'])
                        ->getMock()
                ]
            );

        $mockApplication
            ->shouldReceive('isVariation')
            ->andReturn(true)
            ->once();
        $mockApplication
            ->shouldReceive('getApplicableAuthProperties')
            ->withNoArgs()
            ->once()
            ->andReturn($applicableAuthProperties);
        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockApplication);

        $licenceResult = m::mock(Result::class)
            ->shouldReceive('serialize')
            ->andReturn(['id' => $licenceId])
            ->getMock();
        $this->sut->shouldReceive('getQueryHandler->handleQuery')
            ->with(m::type(LicenceOverviewQry::class), false)
            ->andReturn($licenceResult);

        $this->repoMap['Fee']
            ->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with($applicationId)
            ->once()
            ->andReturn(['fee1', 'fee2']);

        $this->repoMap['Opposition']
            ->shouldReceive('fetchByApplicationId')
            ->with($applicationId)
            ->once()
            ->andReturn(['oppo1', 'oppo2', 'oppo3']);

        $this->commandHandler
            ->shouldReceive('handleCommand')
            ->with(UpdateApplicationCompletionCmd::class)
            ->once()
            ->getMock();

        $this->sut->shouldReceive('getCommandHandler')
            ->andReturn($this->commandHandler)
            ->once()
            ->getMock();

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(
            [
                'id' => 111,
                'licence' => [
                    'id' => $licenceId,
                ],
                'feeCount' => 2,
                'oppositionCount' => 3,
                'valueOptions' => [
                    'tracking' => [
                        0 => '',
                        1 => 'Accepted',
                        2 => 'Not accepted',
                        3 => 'Not applicable',
                    ],
                ],
                'outOfOppositionDate' => 'OOOD',
                'outOfRepresentationDate' => '2015-07-27',
                'operatingCentresNetDelta' => 9,
                'licenceVehicles' => [
                    ['vehicle1'],
                ],
                'applicableAuthProperties' => $applicableAuthProperties,
            ],
            $result->serialize()
        );
    }
}
