<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermit;

use DateTime;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\ReplacementIrhpPermit;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit\CreateReplacement as CreateReplacementHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create Replacement IRHP Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateReplacementTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateReplacementHandler();
        $this->mockRepo('IrhpPermit', PermitRangeRepo::class);
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermit::STATUS_PENDING
        ];

        parent::initReferences();
    }

    /**
     * Test
     */
    public function testHandleCommand(): void
    {
        $cmdData = [
            'replaces' => '201',
            'irhpPermitRange' => '7',
            'permitNumber' => '1600'
        ];

        $command = ReplacementIrhpPermit::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setIrhpCandidatePermit(m::mock(IrhpCandidatePermit::class)->makePartial());
        /** @var IrhpPermitRange $oldRange */
        $oldRange = m::mock(IrhpPermitRange::class)->makePartial();
        /** @var IrhpPermitStock $oldStock */
        $oldStock = m::mock(IrhpPermitStock::class)->makePartial();
        $oldStock->setValidTo(new DateTime());
        $oldRange->setIrhpPermitStock($oldStock);
        $oldPermit->setIrhpPermitRange($oldRange);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['replaces'])
            ->andReturn($oldPermit);

        $newRange = m::mock(IrhpPermitRange::class);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitRange'])
            ->andReturn($newRange);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpPermit::class))
            ->andReturnUsing(
                function (IrhpPermit $irhpPermit) use (&$savedIrhpPermit) {
                    $irhpPermit->setId(1);
                    $savedIrhpPermit = $irhpPermit;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermit' => 1],
            'messages' => ['Permit ' . $cmdData['permitNumber'] . ' Created']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests exception thrown for missing config
     */
    public function testCantSaveException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'replaces' => '201',
            'irhpPermitRange' => '7',
            'permitNumber' => '1600'
        ];

        $command = ReplacementIrhpPermit::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setIrhpCandidatePermit(m::mock(IrhpCandidatePermit::class)->makePartial());
        /** @var IrhpPermitRange $oldRange */
        $oldRange = m::mock(IrhpPermitRange::class)->makePartial();
        /** @var IrhpPermitStock $oldStock */
        $oldStock = m::mock(IrhpPermitStock::class)->makePartial();
        $oldStock->setValidTo(new DateTime());
        $oldRange->setIrhpPermitStock($oldStock);
        $oldPermit->setIrhpPermitRange($oldRange);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['replaces'])
            ->andReturn($oldPermit);

        $newRange = m::mock(IrhpPermitRange::class);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitRange'])
            ->andReturn($newRange);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpPermit::class))
            ->andThrow(new ValidationException(['An error occurred saving the replacement permit']));

        $this->sut->handleCommand($command);
    }
}
