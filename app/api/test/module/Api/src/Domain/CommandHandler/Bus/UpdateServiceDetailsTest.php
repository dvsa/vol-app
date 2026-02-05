<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\UpdateServiceDetails;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\BusRegOtherService as BusRegOtherServiceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\UpdateServiceDetails as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as CmdCreateBusFee;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService as BusRegOtherServiceEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;

class UpdateServiceDetailsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateServiceDetails();
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('BusRegOtherService', BusRegOtherServiceRepo::class);

        $this->references = [
            BusNoticePeriodEntity::class => [
                2 => m::mock(BusNoticePeriodEntity::class)
            ],
        ];

        parent::setUp();
    }

    /**
     * testHandleCommand
     *
     * @note we don't test the two dates here relate to each other properly, that is tested elsewhere
     * First date is included for completeness
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('createFeeProvider')]
    public function testHandleCommand(bool $createFee): void
    {
        $busRegId = 99;
        $serviceNumber = 12345;
        $startPoint = 'start point';
        $finishPoint = 'finish point';
        $via = 'via';
        $otherDetails = 'other details';
        $effectiveDate = '2020-12-25';
        $receivedDate = '2019-12-25';
        $applicationCompleteDate = '2020-06-25';
        $endDate = '2021-12-25';
        $busNoticePeriod = 2;
        $otherServices = [
            0 => [
                'id' => 1,
                'serviceNo' => 99999,
            ],
            1 => [
                'id' => null,
                'serviceNo' => 88888,
            ],
            2 => [
                'id' => 2,
                'serviceNo' => null, //filtered
            ],
            3 => [
                'id' => 3,
                'serviceNo' => 0, //would have been filtered under previous null/empty checks - OLCS-22939
            ],
            4 => [
                'id' => 4,
                'serviceNo' => "0", //would have been filtered under previous null/empty checks - OLCS-22939
            ],
            5 => [
                'id' => 5,
                'serviceNo' => "ab12",
            ],
            6 => [
                'id' => 6,
                'serviceNo' => '', //filtered
            ],
        ];
        $busServiceTypes = [
            0 => 5
        ];
        $version = 555;

        $command = Cmd::Create(
            [
                'id' => $busRegId,
                'serviceNo' => $serviceNumber,
                'startPoint' => $startPoint,
                'finishPoint' => $finishPoint,
                'via' => $via,
                'otherDetails' => $otherDetails,
                'receivedDate' => $receivedDate,
                'effectiveDate' => $effectiveDate,
                'endDate' => $endDate,
                'applicationCompleteDate' => $applicationCompleteDate,
                'busNoticePeriod' => $busNoticePeriod,
                'otherServices' => $otherServices,
                'busServiceTypes' => $busServiceTypes,
                'version' => $version,
            ]
        );

        $busRegOtherServiceEntity1 = m::mock(BusRegOtherServiceEntity::class);
        $busRegOtherServiceEntity1->expects('setServiceNo')->with(99999)->andReturnSelf();
        $busRegOtherServiceEntity1->expects('getId')->times(3)->withNoArgs()->andReturn(1);

        $busRegOtherServiceEntity2 = m::mock(BusRegOtherServiceEntity::class);
        $busRegOtherServiceEntity2->expects('setServiceNo')->with(0)->andReturnSelf();
        $busRegOtherServiceEntity2->expects('getId')->times(3)->withNoArgs()->andReturn(3);

        $busRegOtherServiceEntity3 = m::mock(BusRegOtherServiceEntity::class);
        $busRegOtherServiceEntity3->expects('setServiceNo')->with("0")->andReturnSelf();
        $busRegOtherServiceEntity3->expects('getId')->times(3)->withNoArgs()->andReturn(4);

        $busRegOtherServiceEntity4 = m::mock(BusRegOtherServiceEntity::class);
        $busRegOtherServiceEntity4->expects('setServiceNo')->with("ab12")->andReturnSelf();
        $busRegOtherServiceEntity4->expects('getId')->times(3)->withNoArgs()->andReturn(5); //pre existing, delete check

        //data to be deleted
        $busRegOtherServiceEntityToDelete = m::mock(BusRegOtherServiceEntity::class);
        $busRegOtherServiceEntityToDelete->expects('getId')->withNoArgs()->andReturn(1111111);

        $mockOtherServiceEntities = [
            $busRegOtherServiceEntity1,
            $busRegOtherServiceEntity2,
            $busRegOtherServiceEntity3,
            $busRegOtherServiceEntity4,
            $busRegOtherServiceEntityToDelete,
        ];

        $mockOtherServices = new ArrayCollection($mockOtherServiceEntities);

        //updates of existing data
        $this->repoMap['BusRegOtherService']->expects('fetchById')->with(1)->andReturn($busRegOtherServiceEntity1);
        $this->repoMap['BusRegOtherService']->expects('save')->with($busRegOtherServiceEntity1);
        $this->repoMap['BusRegOtherService']->expects('fetchById')->with(3)->andReturn($busRegOtherServiceEntity2);
        $this->repoMap['BusRegOtherService']->expects('save')->with($busRegOtherServiceEntity2);
        $this->repoMap['BusRegOtherService']->expects('fetchById')->with(4)->andReturn($busRegOtherServiceEntity3);
        $this->repoMap['BusRegOtherService']->expects('save')->with($busRegOtherServiceEntity3);
        $this->repoMap['BusRegOtherService']->expects('fetchById')->with(5)->andReturn($busRegOtherServiceEntity4);
        $this->repoMap['BusRegOtherService']->expects('save')->with($busRegOtherServiceEntity4);

        //adding new data
        $this->repoMap['BusRegOtherService']->expects('save')
            ->andReturnUsing(
                function (BusRegOtherServiceEntity $entity) {
                    self::assertSame(88888, $entity->getServiceNo());
                }
            );

        //deleting data
        $this->repoMap['BusRegOtherService']->expects('delete')
            ->with($busRegOtherServiceEntityToDelete);

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->expects('updateServiceDetails')
            ->with(
                $serviceNumber,
                $startPoint,
                $finishPoint,
                $via,
                $otherDetails,
                $receivedDate,
                $effectiveDate,
                $endDate,
                $applicationCompleteDate,
                $this->references[BusNoticePeriodEntity::class][$busNoticePeriod]
            );
        $busReg->expects('getId')->withNoArgs()->andReturn($busRegId);
        $busReg->expects('setBusServiceTypes')->with(m::type(ArrayCollection::class));
        $busReg->expects('getOtherServices')->withNoArgs()->andReturn($mockOtherServices);
        $busReg->expects('shouldCreateFee')->withNoArgs()->andReturn($createFee);

        $this->repoMap['Bus']->expects('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, $version)
            ->andReturn($busReg);
        $this->repoMap['Bus']->expects('save')->with($busReg);

        //side effect only happens when create fee is true
        $createFeeResult = new Result();
        $createFeeResult
            ->addId('fee', 99)
            ->addMessage('bus reg fee created');
        $this->expectedSideEffect(
            CmdCreateBusFee::class,
            ['id' => $busRegId],
            $createFeeResult,
            $createFee ? 1 : 0
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    public static function createFeeProvider(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
