<?php

declare(strict_types=1);

/**
 * Create Community Licence Test / Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic\Licence;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Licence\Create as CreateCmdHandler;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Licence\Create as Cmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\Licence\CreateOfficeCopy as CreateOfficeCopyCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTclCommandCmd;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;

/**
 * Create Community Licence Test / Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateCommunityLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateCmdHandler();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_ACTIVE
        ];

        $this->references = [
            LicenceEntity::class => [
                1 => m::mock(LicenceEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 1;
        $data = [
            'licence' => $licenceId,
            'totalLicences' => 1
        ];

        $command = Cmd::create($data);

        $mockLicence = m::mock()
            ->shouldReceive('getIssueNo')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(10)
            ->once()
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn('A')
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->andReturn($mockLicence)
            ->twice()
            ->getMock();

        $communityLic = null;

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->andReturn([$mockLicence])
            ->once()
            ->shouldReceive('fetchOfficeCopy')
            ->andReturn(null)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(CommunityLicEntity::class))
            ->andReturnUsing(
                function (CommunityLicEntity $lic) use (&$communityLic) {
                    $lic->setId(111);
                    $communityLic = $lic;
                }
            );

        $this->expectedSideEffect(
            GenerateBatchCmd::class,
            [
                'isBatchReprint' => false,
                'licence' => $licenceId,
                'communityLicenceIds' => [111],
                'identifier' => null
            ],
            new Result()
        );

        $this->expectedSideEffect(
            CreateOfficeCopyCmd::class,
            [
                'licence' => $licenceId,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateTclCommandCmd::class,
            [
                'id' => $licenceId,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'communityLic111' => 111
            ],
            'messages' => [
                'Community licence created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('A', $communityLic->getSerialNoPrefix());
        $this->assertEquals($licenceId, $communityLic->getLicence()->getId());
        $this->assertEquals(3, $communityLic->getIssueNo());
        $this->assertEquals(CommunityLicEntity::STATUS_ACTIVE, $communityLic->getStatus()->getId());
    }

    public function testHandleCommandNotValid(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licenceId = 1;
        $data = [
            'licence' => $licenceId,
            'totalLicences' => 1
        ];

        $mockLicence = m::mock()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(1)
            ->once()
            ->getMock();

        $command = Cmd::create($data);
        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->andReturn([$mockLicence])
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
