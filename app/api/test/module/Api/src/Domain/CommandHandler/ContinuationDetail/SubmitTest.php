<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Licence\ContinueLicence;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\Submit as CommandHandler;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Submit as UpdateCommand;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Domain\Repository;
use Mockery as m;

/**
 * SubmitTest
 */
class SubmitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', Repository\ContinuationDetail::class);
        $this->mockRepo('Fee', Repository\Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::SIG_PHYSICAL_SIGNATURE,
            RefData::SIG_DIGITAL_SIGNATURE,
        ];

        parent::initReferences();
    }

    public function testHandleCommandPhysicalSignature(): void
    {
        $data = [
            'id' => 154,
            'version' => 7,
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setTotAuthVehicles(1);
        $continuationDetail->setTotCommunityLicences(1);
        $continuationDetail->setTotPsvDiscs(1);
        $continuationDetail->setId(154);
        $continuationDetail->setLicence(
            m::mock()->shouldReceive('getId')->with()->once()->andReturn(654)->getMock()
        );

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);
        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->with(654)->once()->andReturn(['FEE 1']);

        $result = $this->sut->handleCommand($command);

        $this->assertSame($this->refData[RefData::SIG_PHYSICAL_SIGNATURE], $continuationDetail->getSignatureType());
        $this->assertTrue($continuationDetail->getIsDigital());

        $this->assertEquals(['ContinuationDetail submitted'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandDigitalSignature(): void
    {
        $data = [
            'id' => 154,
            'version' => 7,
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setTotAuthVehicles(1);
        $continuationDetail->setTotCommunityLicences(1);
        $continuationDetail->setTotPsvDiscs(1);
        $continuationDetail->setId(154);
        $continuationDetail->setSignatureType($this->refData[RefData::SIG_DIGITAL_SIGNATURE]);
        $continuationDetail->setLicence(
            m::mock()->shouldReceive('getId')->with()->once()->andReturn(654)->getMock()
        );

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);
        $this->repoMap['ContinuationDetail']->shouldReceive('save')->once();

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->with(654)->once()->andReturn(['FEE 1']);

        $result = $this->sut->handleCommand($command);

        $this->assertSame($this->refData[RefData::SIG_DIGITAL_SIGNATURE], $continuationDetail->getSignatureType());
        $this->assertTrue($continuationDetail->getIsDigital());

        $this->assertEquals(['ContinuationDetail submitted'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandNoFees(): void
    {
        $data = [
            'id' => 154,
            'version' => 7,
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);
        $continuationDetail->setSignatureType($this->refData[RefData::SIG_DIGITAL_SIGNATURE]);
        $continuationDetail->setLicence(
            m::mock()
                ->shouldReceive('getId')->with()->twice()->andReturn(654)
                ->shouldReceive('getVersion')->with()->once()->andReturn(99)
                ->shouldReceive('getTotAuthVehicles')->with()->once()->andReturn(456)
                ->shouldReceive('getTotCommunityLicences')->with()->once()->andReturn(567)
                ->shouldReceive('getPsvDiscsNotCeased')->with()->once()->andReturn(new ArrayCollection([1, 2]))
                ->getMock()
        );

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);
        $this->repoMap['ContinuationDetail']->shouldReceive('save')->with($continuationDetail)->once();

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->with(654)->once()->andReturn([]);

        $this->expectedSideEffect(
            ContinueLicence::class,
            ['id' => 654, 'version' => 99],
            (new Result())->addMessage('CONTINUE_LICENCE')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(456, $continuationDetail->getTotAuthVehicles());
        $this->assertSame(567, $continuationDetail->getTotCommunityLicences());
        $this->assertSame(2, $continuationDetail->getTotPsvDiscs());

        $this->assertEquals(['CONTINUE_LICENCE', 'ContinuationDetail submitted'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }

    public function testHandleCommandNoFeesDontUseDefaultsForTotals(): void
    {
        $data = [
            'id' => 154,
            'version' => 7,
        ];
        $command = UpdateCommand::create($data);

        $continuationDetail = new ContinuationDetailEntity();
        $continuationDetail->setId(154);
        $continuationDetail->setTotAuthVehicles(1);
        $continuationDetail->setTotCommunityLicences(2);
        $continuationDetail->setTotPsvDiscs(3);
        $continuationDetail->setSignatureType($this->refData[RefData::SIG_DIGITAL_SIGNATURE]);
        $continuationDetail->setLicence(
            m::mock()
                ->shouldReceive('getId')->with()->twice()->andReturn(654)
                ->shouldReceive('getVersion')->with()->once()->andReturn(99)
                ->getMock()
        );

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')->with(154, Query::HYDRATE_OBJECT, 7)->once()
            ->andReturn($continuationDetail);
        $this->repoMap['ContinuationDetail']->shouldReceive('save')->with($continuationDetail)->once();

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingContinuationFeesByLicenceId')
            ->with(654)->once()->andReturn([]);

        $this->expectedSideEffect(
            ContinueLicence::class,
            ['id' => 654, 'version' => 99],
            (new Result())->addMessage('CONTINUE_LICENCE')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(1, $continuationDetail->getTotAuthVehicles());
        $this->assertSame(2, $continuationDetail->getTotCommunityLicences());
        $this->assertSame(3, $continuationDetail->getTotPsvDiscs());

        $this->assertEquals(['CONTINUE_LICENCE', 'ContinuationDetail submitted'], $result->getMessages());
        $this->assertEquals(['continuationDetail' => 154], $result->getIds());
    }
}
