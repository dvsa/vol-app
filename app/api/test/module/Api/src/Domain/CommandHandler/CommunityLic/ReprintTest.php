<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Reprint;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Reprint as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCommand;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Reprint::class)]
class ReprintTest extends AbstractCommandHandlerTestCase
{
    /** @var Reprint */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Reprint();

        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_ACTIVE
        ];

        parent::initReferences();
    }

    /**
     * Tests handle command in situations where db updates are made
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommand')]
    public function testHandleCommand(mixed $isBatchReprint, mixed $dbUpdateDisabled, mixed $dbParamCheckedTimes): void
    {
        $licenceId = 1;
        $communityLicenceId1 = 10;
        $communityLicenceId2 = 20;
        $communityLicenceIds = [$communityLicenceId1, $communityLicenceId2];
        $issueNo1 = 100;
        $issueNo2 = 200;
        $serialNoPrefix = 'A';

        $data = [
            'isBatchReprint' => $isBatchReprint,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds
        ];

        $command = Cmd::create($data);

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DISABLE_COM_LIC_BULK_REPRINT_DB)
            ->andReturn($dbUpdateDisabled)
            ->times($dbParamCheckedTimes)
            ->getMock();

        $mockCommunityLicence1 = m::mock(CommunityLicEntity::class)
            ->shouldReceive('isActive')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getIssueNo')
            ->once()
            ->andReturn($issueNo1)
            ->getMock();

        $mockCommunityLicence2 = m::mock(CommunityLicEntity::class)
            ->shouldReceive('isActive')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getIssueNo')
            ->once()
            ->andReturn($issueNo2)
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchLicencesByIds')
            ->with($communityLicenceIds)
            ->andReturn([$mockCommunityLicence1, $mockCommunityLicence2])
            ->once()
            ->shouldReceive('save')
            ->with(m::type(CommunityLicEntity::class))
            ->andReturnUsing(
                function (CommunityLicEntity $lic) use (&$communityLic, $communityLicenceId1) {
                    $lic->setId($communityLicenceId1);
                    $communityLic = $lic;
                }
            )
            ->once()
            ->shouldReceive('save')
            ->with(m::type(CommunityLicEntity::class))
            ->andReturnUsing(
                function (CommunityLicEntity $lic2) use (&$communityLic2, $communityLicenceId2) {
                    $lic2->setId($communityLicenceId2);
                    $communityLic2 = $lic2;
                }
            )
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            TransferCmd\CommunityLic\Annul::class,
            [
                'licence' => $licenceId,
                'communityLicenceIds' => $communityLicenceIds,
                'checkOfficeCopy' => false
            ],
            new Result()
        );

        $this->expectedSideEffect(UpdateTotalCommunityLicencesCommand::class, ['id' => $licenceId], new Result());

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn($serialNoPrefix)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            GenerateBatchCmd::class,
            [
                'licence' => $licenceId,
                'communityLicenceIds' => $communityLicenceIds,
                'identifier' => null
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'communityLic' . $communityLicenceId1 => $communityLicenceId1,
                'communityLic' . $communityLicenceId2 => $communityLicenceId2
            ],
            'messages' => [
                'The selected licence with issue number ' . $issueNo1 . ' has been generated',
                'The selected licence with issue number ' . $issueNo2 . ' has been generated'
            ]
        ];

        //check confirmation messages
        $this->assertEquals($expected, $result->toArray());

        //first new community licence record
        $this->assertEquals($communityLicenceId1, $communityLic->getId());
        $this->assertEquals($serialNoPrefix, $communityLic->getSerialNoPrefix());
        $this->assertEquals($mockLicence, $communityLic->getLicence());
        $this->assertEquals($issueNo1, $communityLic->getIssueNo());
        $this->assertEquals(CommunityLicEntity::STATUS_ACTIVE, $communityLic->getStatus()->getId());

        //second new community licence record
        $this->assertEquals($communityLicenceId2, $communityLic2->getId());
        $this->assertEquals($serialNoPrefix, $communityLic2->getSerialNoPrefix());
        $this->assertEquals($mockLicence, $communityLic2->getLicence());
        $this->assertEquals($issueNo2, $communityLic2->getIssueNo());
        $this->assertEquals(CommunityLicEntity::STATUS_ACTIVE, $communityLic2->getStatus()->getId());
    }

    public static function dpHandleCommand(): array
    {
        return [
            [false, 0, 0],
            [false, 1, 0],
            [true, 0, 1]
        ];
    }

    public function testHandleCommandWithoutDbUpdate(): void
    {
        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'isBatchReprint' => true,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds
        ];

        $command = Cmd::create($data);

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DISABLE_COM_LIC_BULK_REPRINT_DB)
            ->once()
            ->andReturn(1)
            ->getMock();

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('isActive')
            ->once()
            ->andReturn(true)
            ->shouldReceive('getIssueNo')
            ->once()
            ->andReturn(999)
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchLicencesByIds')
            ->with($communityLicenceIds)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            GenerateBatchCmd::class,
            [
                'licence' => $licenceId,
                'communityLicenceIds' => $communityLicenceIds,
                'identifier' => null
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Community licences reprinted without updating DB'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'isBatchReprint' => false,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds
        ];

        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('isActive')
            ->once()
            ->andReturn(false)
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchLicencesByIds')
            ->with($communityLicenceIds)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
