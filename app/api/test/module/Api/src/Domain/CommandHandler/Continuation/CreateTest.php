<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Continuation;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Continuation\Create as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Continuation\Create as Command;
use Dvsa\Olcs\Api\Domain\Repository\Continuation as ContinuationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Entity\Licence\Continuation as ContinuationEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Mockery as m;

/**
 * Create continuations test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Continuation', ContinuationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContinuationDetailEntity::STATUS_PREPARED,
        ];
        $this->references = [
            TrafficAreaEntity::class => [
                'B' => m::mock(TrafficAreaEntity::class)
            ],
            LicenceEntity::class => [
                333 => m::mock(LicenceEntity::class)->makePartial()
            ],
            ContinuationEntity::class => [
                11 => m::mock(ContinuationEntity::class)->makePartial()
            ]
        ];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([])
            ->once()
            ->getMock();
        $this->repoMap['Continuation']->shouldReceive('save')
            ->with(m::type(ContinuationEntity::class))
            ->andReturnUsing(
                function (ContinuationEntity $continuation) {
                    $continuation->setId(11);
                    $this->assertSame(2015, $continuation->getYear());
                    $this->assertSame(1, $continuation->getMonth());
                    $this->assertSame(
                        $this->mapReference(TrafficAreaEntity::class, 'B'),
                        $continuation->getTrafficArea()
                    );

                    return $continuation;
                }
            );

        $lic1 = ['id' => 111];
        $lic2 = ['id' => 222];
        $lic3 = ['id' => 333];
        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuation')
            ->with($year, $month, $trafficArea)
            ->andReturn([$lic1, $lic2, $lic3])
            ->once()
            ->getMock();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchLicenceIdsForContinuationAndLicences')
            ->with(11, [111, 222, 333])
            ->once()
            ->andReturn([111, 222]);

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('createContinuationDetails')
            ->with([2 => 333], false, ContinuationDetailEntity::STATUS_PREPARED, 11)
            ->once();

        $expected = [
            'id' => [
                'continuation' => 11,
            ],
            'messages' => ['Continuation created']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }

    public function testContinuationExists(): void
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([$this->mapReference(ContinuationEntity::class, 11)])
            ->once()
            ->getMock();

        $lic1 = ['id' => 111];
        $lic2 = ['id' => 222];
        $lic3 = ['id' => 333];
        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuation')
            ->with($year, $month, $trafficArea)
            ->andReturn([$lic1, $lic2, $lic3])
            ->once()
            ->getMock();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchLicenceIdsForContinuationAndLicences')
            ->with(11, [111, 222, 333])
            ->once()
            ->andReturn([111, 222, 333]);

        $expected = [
            'id' => [
                'continuation' => 11,
            ],
            'messages' => ['Continuation created']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }

    public function testNoLicencesFound(): void
    {
        $month = 1;
        $year = 2015;
        $trafficArea = 'B';
        $data = [
            'month' => $month,
            'year'  => $year,
            'trafficArea' => $trafficArea
        ];
        $command = Command::create($data);

        $this->repoMap['Continuation']
            ->shouldReceive('fetchContinuation')
            ->with($month, $year, $trafficArea)
            ->andReturn([$this->mapReference(ContinuationEntity::class, 11)])
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchForContinuation')
            ->with($year, $month, $trafficArea)
            ->andReturn([])
            ->once()
            ->getMock();

        $expected = [
            'id' => [
                'continuation' => 0,
            ],
            'messages' => ['No licences found']
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($result->toArray(), $expected);
    }
}
