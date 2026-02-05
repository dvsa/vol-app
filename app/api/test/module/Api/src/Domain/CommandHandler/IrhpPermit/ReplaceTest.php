<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermit;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermit\ReplacementIrhpPermit;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange\ByPermitNumber as RangeByPermitNumber;
use Dvsa\Olcs\Api\Domain\Query\IrhpPermit\ByPermitNumber as PermitByPermitNumber;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpPermit\Replace;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit\Replace as ReplaceHandler;
use Mockery as m;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit\CreateReplacement as CreateReplacementHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create Replacement IRHP Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ReplaceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {

        $this->sut = m::mock(ReplaceHandler::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpPermit', PermitRangeRepo::class);
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpPermit::STATUS_CEASED,
            IrhpPermit::STATUS_PRINTED
        ];

        parent::initReferences();
    }

    /**
     * Test
     */
    public function testHandleCommand(): void
    {
        $cmdData = [
            'id' => 9,
            'replacementIrhpPermit' => 1245
        ];

        $command = Replace::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setIrhpCandidatePermit(m::mock(IrhpCandidatePermit::class)->makePartial());
        $oldPermit->setStatus(new RefData(IrhpPermit::STATUS_PRINTED));
        /** @var IrhpPermitRange $oldRange */
        $oldRange = m::mock(IrhpPermitRange::class)->makePartial();
        /** @var IrhpPermitStock $oldStock */
        $oldStock = m::mock(IrhpPermitStock::class)->makePartial();
        $oldStock->setValidTo(new DateTime());
        $oldRange->setIrhpPermitStock($oldStock);
        $oldPermit->setIrhpPermitRange($oldRange);
        $oldPermit->setId($cmdData['id']);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($oldPermit);

        $oldPermit->shouldReceive('getStatus->getId')
            ->andReturn(IrhpPermit::STATUS_PRINTED);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturnUsing(function ($query) {
                $targetRange = m::mock(IrhpPermitRange::class)->makePartial();
                $targetRange->setId(7);
                return [
                    $targetRange
                ];
            });

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturn([]);

        $result = new Result();
        $createCmdData = [
            'replaces' => 9,
            'irhpPermitRange' => 7,
            'permitNumber' => $cmdData['replacementIrhpPermit']
        ];

        $this->expectedSideEffect(ReplacementIrhpPermit::class, $createCmdData, $result);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpPermit::class))
            ->andReturnUsing(
                function (IrhpPermit $irhpPermit) {
                    $irhpPermit->setId(1);
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermit' => 1],
            'messages' => ['The replacement permit has been successfully issued and can now be printed']
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests exception thrown for wrong status
     */
    public function testWrongStatusException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $cmdData = [
            'id' => '9',
            'replacementIrhpPermit' => '1001'
        ];

        $command = Replace::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setStatus(new RefData(IrhpPermit::STATUS_CEASED));

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($oldPermit);

        $oldPermit->shouldReceive('isPrinted')
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }

    /**
     * Tests exception thrown for wrong status
     */
    public function testBadRangeException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $cmdData = [
            'id' => '9',
            'replacementIrhpPermit' => '1001'
        ];

        $command = Replace::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setStatus(new RefData(IrhpPermit::STATUS_CEASED));

        $oldRange = m::mock(IrhpPermitRange::class)->makePartial();
        /** @var IrhpPermitStock $oldStock */
        $oldStock = m::mock(IrhpPermitStock::class)->makePartial();
        $oldStock->setValidTo(new DateTime());
        $oldRange->setIrhpPermitStock($oldStock);
        $oldPermit->setIrhpPermitRange($oldRange);
        $oldPermit->setId($cmdData['id']);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($oldPermit);

        $oldPermit->shouldReceive('isPrinted')
            ->andReturn(true);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturn([]);

        $this->sut->handleCommand($command);
    }

    public function testNewPermitException(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The inputted Permit Number is not available. Please input a different number from the same range');

        $cmdData = [
            'id' => 9,
            'replacementIrhpPermit' => 1245
        ];

        $command = Replace::create($cmdData);

        /** @var IrhpPermit $oldPermit */
        $oldPermit = m::mock(IrhpPermit::class)->makePartial();
        $oldPermit->setIrhpCandidatePermit(m::mock(IrhpCandidatePermit::class)->makePartial());
        $oldPermit->setStatus(new RefData(IrhpPermit::STATUS_PRINTED));
        /** @var IrhpPermitRange $oldRange */
        $oldRange = m::mock(IrhpPermitRange::class)->makePartial();
        /** @var IrhpPermitStock $oldStock */
        $oldStock = m::mock(IrhpPermitStock::class)->makePartial();
        $oldStock->setValidTo(new DateTime());
        $oldRange->setIrhpPermitStock($oldStock);
        $oldPermit->setIrhpPermitRange($oldRange);
        $oldPermit->setId($cmdData['id']);

        $this->repoMap['IrhpPermit']
            ->shouldReceive('fetchById')
            ->with($cmdData['id'])
            ->andReturn($oldPermit);

        $oldPermit->shouldReceive('getStatus->getId')
            ->andReturn(IrhpPermit::STATUS_PRINTED);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturnUsing(function ($query) {
                $targetRange = m::mock(IrhpPermitRange::class)->makePartial();
                $targetRange->setId(7);
                return [
                    $targetRange
                ];
            });

        $newPermit = m::mock(IrhpPermit::class);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->andReturn([$newPermit]);

        $this->sut->handleCommand($command);
    }
}
