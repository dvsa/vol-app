<?php

declare(strict_types=1);

/**
 * Create Fee Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateFee;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

/**
 * Create Fee Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CreateFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateFee();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('FeeType', \Dvsa\Olcs\Api\Domain\Repository\FeeType::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'FEE_TYPE',
            'GOODS_OR_PSV',
            'LICENCE_TYPE',
        ];

        $this->references = [
            TrafficArea::class => [
                TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => m::mock(TrafficArea::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 834, 'feeTypeFeeType' => 'FEE_TYPE']);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(32);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(834);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setGoodsOrPsv($this->mapRefData('GOODS_OR_PSV'));
        $application->setLicenceType($this->mapRefData('LICENCE_TYPE'));
        $application->setReceivedDate('2015-06-01');

        $feeType = new FeeType();
        $feeType->setId(223);
        $feeType->setDescription('DESCRIPTION');
        $feeType->setFixedValue(123.33);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($application);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')->with(
            $this->mapRefData('FEE_TYPE'),
            $this->mapRefData('GOODS_OR_PSV'),
            $this->mapRefData('LICENCE_TYPE'),
            m::type('\DateTime'),
            null,
            null
        )->once()->andReturn($feeType);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'application' => 834,
                'licence' => 32,
                'task' => null,
                'amount' => 123.33,
                'invoicedDate' => date('Y-m-d'),
                'feeType' => 223,
                'description' => 'DESCRIPTION for application 834',
                'feeStatus' => 'lfs_ot',
                'busReg' => null,
                'irfoGvPermit' => null,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithTask(): void
    {
        $command = Cmd::create(['id' => 834, 'feeTypeFeeType' => 'FEE_TYPE', 'task' => 111]);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(32);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(834);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setGoodsOrPsv($this->mapRefData('GOODS_OR_PSV'));
        $application->setLicenceType($this->mapRefData('LICENCE_TYPE'));
        $application->setReceivedDate('2015-06-01');

        $feeType = new FeeType();
        $feeType->setId(223);
        $feeType->setDescription('DESCRIPTION');
        $feeType->setFixedValue(123.33);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($application);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')->with(
            $this->mapRefData('FEE_TYPE'),
            $this->mapRefData('GOODS_OR_PSV'),
            $this->mapRefData('LICENCE_TYPE'),
            m::type('\DateTime'),
            null,
            null
        )->once()->andReturn($feeType);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'application' => 834,
                'licence' => 32,
                'task' => 111,
                'amount' => 123.33,
                'invoicedDate' => date('Y-m-d'),
                'feeType' => 223,
                'description' => 'DESCRIPTION for application 834',
                'feeStatus' => 'lfs_ot',
                'busReg' => null,
                'irfoGvPermit' => null,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandReceivedDateNull(): void
    {
        $command = Cmd::create(['id' => 834, 'feeTypeFeeType' => 'FEE_TYPE']);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(32);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(834);
        $application->setLicence($licence);
        $application->setNiFlag('Y');
        $application->setGoodsOrPsv($this->mapRefData('GOODS_OR_PSV'));
        $application->setLicenceType($this->mapRefData('LICENCE_TYPE'));
        $application->setReceivedDate(null);
        $application->setCreatedOn('2014-12-23');

        $feeType = m::mock(FeeType::class)->makePartial();
        $feeType->setId(223);
        $feeType->setDescription('DESCRIPTION');
        $feeType->setFixedValue(123.33);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($application);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')->with(
            $this->mapRefData('FEE_TYPE'),
            $this->mapRefData('GOODS_OR_PSV'),
            $this->mapRefData('LICENCE_TYPE'),
            m::type('\DateTime'),
            $this->mapReference(TrafficArea::class, TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE),
            null
        )->once()->andReturn($feeType);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'application' => 834,
                'licence' => 32,
                'task' => null,
                'amount' => 123.33,
                'invoicedDate' => date('Y-m-d'),
                'feeType' => 223,
                'description' => 'DESCRIPTION for application 834',
                'feeStatus' => 'lfs_ot',
                'busReg' => null,
                'irfoGvPermit' => null,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandNi(): void
    {
        $command = Cmd::create(['id' => 834, 'feeTypeFeeType' => 'FEE_TYPE']);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(32);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(834);
        $application->setLicence($licence);
        $application->setNiFlag('Y');
        $application->setGoodsOrPsv($this->mapRefData('GOODS_OR_PSV'));
        $application->setLicenceType($this->mapRefData('LICENCE_TYPE'));
        $application->setReceivedDate('2015-06-01');

        $feeType = m::mock(FeeType::class)->makePartial();
        $feeType->setId(223);
        $feeType->setDescription('DESCRIPTION');
        $feeType->setFixedValue(123.33);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($application);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')->with(
            $this->mapRefData('FEE_TYPE'),
            $this->mapRefData('GOODS_OR_PSV'),
            $this->mapRefData('LICENCE_TYPE'),
            m::type('\DateTime'),
            $this->mapReference(TrafficArea::class, TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE),
            null
        )->once()->andReturn($feeType);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee::class,
            [
                'application' => 834,
                'licence' => 32,
                'task' => null,
                'amount' => 123.33,
                'invoicedDate' => date('Y-m-d'),
                'feeType' => 223,
                'description' => 'DESCRIPTION for application 834',
                'feeStatus' => 'lfs_ot',
                'busReg' => null,
                'irfoGvPermit' => null,
            ],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithOptinal(): void
    {
        $command = Cmd::create(['id' => 834, 'feeTypeFeeType' => 'FEE_TYPE', 'optional' => true]);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(32);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(834);
        $application->setLicence($licence);
        $application->setNiFlag('N');
        $application->setGoodsOrPsv($this->mapRefData('GOODS_OR_PSV'));
        $application->setLicenceType($this->mapRefData('LICENCE_TYPE'));
        $application->setReceivedDate('2015-06-01');

        $feeType = new FeeType();
        $feeType->setId(223);
        $feeType->setDescription('DESCRIPTION');
        $feeType->setFixedValue(123.33);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()
            ->andReturn($application);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')->with(
            $this->mapRefData('FEE_TYPE'),
            $this->mapRefData('GOODS_OR_PSV'),
            $this->mapRefData('LICENCE_TYPE'),
            m::type('\DateTime'),
            null,
            true
        )->once()->andReturn(null);

        $this->sut->handleCommand($command);
    }
}
