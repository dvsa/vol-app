<?php

declare(strict_types=1);

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\CancelLicenceFees as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CancelLicenceFees;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CancelLicenceFeesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelLicenceFees();
        $this->mockRepo('Licence', Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Fee::STATUS_OUTSTANDING,
            Fee::STATUS_CANCELLED
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithoutFees(): void
    {
        $data = [

        ];

        $command = Cmd::create($data);

        $fees = new ArrayCollection();

        /** @var Fee $cancelledFee */
        $cancelledFee = m::mock(Fee::class)->makePartial();
        $cancelledFee->setId(123);
        $cancelledFee->setFeeStatus($this->refData[Fee::STATUS_CANCELLED]);
        $fees->add($cancelledFee);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setFees($fees);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($licence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'No fees to remove'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommand(): void
    {
        $data = [

        ];

        $command = Cmd::create($data);

        $fees = new ArrayCollection();

        /** @var Fee $outstandingFee1 */
        $outstandingFee1 = m::mock(Fee::class)->makePartial();
        $outstandingFee1->setId(123);
        $outstandingFee1->setFeeStatus($this->refData[Fee::STATUS_OUTSTANDING]);
        $fees->add($outstandingFee1);

        /** @var Fee $cancelledFee */
        $cancelledFee = m::mock(Fee::class)->makePartial();
        $cancelledFee->setId(1);
        $cancelledFee->setFeeStatus($this->refData[Fee::STATUS_CANCELLED]);
        $fees->add($cancelledFee);

        /** @var Fee $outstandingFee2 */
        $outstandingFee2 = m::mock(Fee::class)->makePartial();
        $outstandingFee2->setId(124);
        $outstandingFee2->setFeeStatus($this->refData[Fee::STATUS_OUTSTANDING]);
        $fees->add($outstandingFee2);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setFees($fees);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($licence);

        $this->expectedSideEffect(
            CancelFeeCmd::class,
            ['id' => 123],
            (new Result())->addMessage('Fee 123 cancelled successfully')
        );
        $this->expectedSideEffect(
            CancelFeeCmd::class,
            ['id' => 124],
            (new Result())->addMessage('Fee 124 cancelled successfully')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Fee 123 cancelled successfully',
                'Fee 124 cancelled successfully',
                '2 fee(s) cancelled'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
