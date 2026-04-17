<?php

declare(strict_types=1);

/**
 * Create Bus Fee Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\CreateBusFee;
use Dvsa\Olcs\Api\Domain\Command\Bus\CreateBusFee as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;

/**
 * Create Bus Fee Test
 */
class CreateBusFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateBusFee();
        $this->mockRepo('Bus', BusRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            FeeTypeEntity::FEE_TYPE_BUSVAR,
            FeeTypeEntity::FEE_TYPE_BUSAPP,
            Licence::LICENCE_CATEGORY_PSV,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        parent::initReferences();
    }

    /**
     *
     * @param int $variationNumber
     * @param string $feeType
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $variationNumber, mixed $feeType): void
    {
        $busRegId = 111;
        $regNo = 12345;

        $receivedDate = '2015-01-01';
        $feeTypeRef = $this->refData[$feeType];
        $goodsOrPsv = $this->refData[Licence::LICENCE_CATEGORY_PSV];
        $licenceType = $this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL];

        $command = Cmd::create(['id' => $busRegId]);

        /** @var TrafficArea $ta */
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setId('M');
        $ta->setIsScotland(true);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setGoodsOrPsv($goodsOrPsv);
        $licence->setLicenceType($licenceType);
        $licence->setTrafficArea($ta);

        /** @var BusRegEntity $busReg */
        $busReg = m::mock(BusRegEntity::class)->makePartial();
        $busReg->setId($busRegId);
        $busReg->setRegNo($regNo);
        $busReg->setLicence($licence);
        $busReg->setReceivedDate($receivedDate);
        $busReg->setVariationNo($variationNumber);

        /** @var FeeTypeEntity $feeType */
        $feeTypeEntity = m::mock(FeeTypeEntity::class)->makePartial();
        $feeTypeEntity->setId(444);
        $feeTypeEntity->setDescription('Fee description');
        $feeTypeEntity->setFixedValue(10.5);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $feeTypeRef,
                $goodsOrPsv,
                $licenceType,
                m::type('\DateTime'),
                TrafficArea::SCOTTISH_TRAFFIC_AREA_CODE
            )
            ->andReturn($feeTypeEntity);

        $this->repoMap['Bus']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($busReg);

        $feeData = [
            'task' => null,
            'application' => null,
            'licence' => 222,
            'invoicedDate' => date('Y-m-d'),
            'description' => 'Fee description ' . $regNo . ' V' . $variationNumber,
            'feeType' => 444,
            'amount' => 10.5,
            'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
            'busReg' => $busRegId,
            'irfoGvPermit' => null,
            'irfoPsvAuth' => null,
            'user' => null,
        ];

        $result = new Result();
        $this->expectedSideEffect(CreateFee::class, $feeData, $result);

        /**
         * @var \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee
         */
        $this->sut->handleCommand($command);
    }

    /**
     * Provider for testHandleCommand
     *
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            [0, FeeTypeEntity::FEE_TYPE_BUSAPP],
            [1, FeeTypeEntity::FEE_TYPE_BUSVAR]
        ];
    }
}
