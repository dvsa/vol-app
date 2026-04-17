<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpCandidatePermit\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as IrhpPermitRangeEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Create IRHP Candidate Permit Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('IrhpPermitRange', PermitRangeRepo::class);
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);
        $this->mockRepo('IrhpPermitApplication', IrhpPermitApplicationRepo::class);

        parent::setUp();
    }

    /**
     * Test the Happy Path
     */
    public function testHandleCommand(): void
    {
        $cmdData = [
            'irhpPermitApplication' => 2211,
            'irhpPermitRange' => 99,
        ];

        $command = CreateCmd::create($cmdData);

        $irhpPermitRange = m::mock(IrhpPermitRangeEntity::class);
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);

        $irhpPermitRange->shouldReceive('getEmissionsCategory')
            ->withNoArgs()
            ->once()
            ->andReturn(RefData::EMISSIONS_CATEGORY_EURO6_REF);

        $this->repoMap['IrhpPermitRange']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitRange'])
            ->once()
            ->andReturn($irhpPermitRange);

        $this->repoMap['IrhpPermitApplication']
            ->shouldReceive('fetchById')
            ->with($cmdData['irhpPermitApplication'])
            ->once()
            ->andReturn($irhpPermitApplication);

        $this->repoMap['IrhpCandidatePermit']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(IrhpCandidatePermitEntity::class))
            ->andReturnUsing(
                function (IrhpCandidatePermitEntity $irhpCandidatePermit) use ($irhpPermitApplication, $irhpPermitRange) {
                    $irhpCandidatePermit->setId(1212);
                    $this->assertSame($irhpPermitRange, $irhpCandidatePermit->getIrhpPermitRange());
                    $this->assertSame($irhpPermitApplication, $irhpCandidatePermit->getIrhpPermitApplication());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpCandidatePermit' => 1212],
            'messages' => ['IRHP Candidate Permit \'1212\' created']
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
