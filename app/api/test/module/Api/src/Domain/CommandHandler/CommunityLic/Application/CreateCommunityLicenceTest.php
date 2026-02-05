<?php

declare(strict_types=1);

/**
 * Create Community Licence Test / Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Application\Create as CreateCmdHanlder;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Application\Create as Cmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\Application\CreateOfficeCopy as CreateOfficeCopyCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTclCommandCmd;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;

/**
 * Create Community Licence Test / Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateCommunityLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateCmdHanlder();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_PENDING,
            CommunityLicEntity::STATUS_ACTIVE
        ];

        $this->references = [
            LicenceEntity::class => [
                1 => m::mock(LicenceEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandInterimNotInForce(): void
    {
        $licenceId = 1;
        $identifier = 2;
        $data = [
            'licence' => $licenceId,
            'identifier' => $identifier,
            'totalLicences' => 1
        ];

        $command = Cmd::create($data);

        $mockLicence = m::mock()
            ->shouldReceive('getIssueNo')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn('A')
            ->once()
            ->getMock();

        $mockApplication = m::mock()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(10)
            ->once()
            ->shouldReceive('getInterimStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(ApplicationEntity::INTERIM_STATUS_REQUESTED)
                ->once()
                ->getMock()
            )
            ->twice()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->andReturn($mockApplication)
            ->twice()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
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
            CreateOfficeCopyCmd::class,
            [
                'licence' => $licenceId,
                'identifier' => $identifier
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

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => $identifier,
                'section' => 'communityLicences'
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
        $this->assertEquals(CommunityLicEntity::STATUS_PENDING, $communityLic->getStatus()->getId());
    }

    public function testHandleCommandInterimInForce(): void
    {
        $licenceId = 1;
        $identifier = 2;
        $data = [
            'licence' => $licenceId,
            'identifier' => $identifier,
            'totalLicences' => 1
        ];

        $command = Cmd::create($data);

        $mockLicence = m::mock()
            ->shouldReceive('getIssueNo')
            ->andReturn(2)
            ->once()
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn('A')
            ->once()
            ->getMock();

        $mockApplication = m::mock()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(10)
            ->once()
            ->shouldReceive('getInterimStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(ApplicationEntity::INTERIM_STATUS_INFORCE)
                ->once()
                ->getMock()
            )
            ->twice()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->andReturn($mockApplication)
            ->twice()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
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
            CreateOfficeCopyCmd::class,
            [
                'licence' => $licenceId,
                'identifier' => $identifier
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

        $this->expectedSideEffect(
            GenerateBatchCmd::class,
            [
                'isBatchReprint' => false,
                'licence' => $licenceId,
                'communityLicenceIds' => [111],
                'identifier' => $identifier
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => $identifier,
                'section' => 'communityLicences'
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
        $identifier = 2;
        $data = [
            'licence' => $licenceId,
            'totalLicences' => 1,
            'identifier' => $identifier
        ];

        $mockApplication = m::mock()
            ->shouldReceive('getTotAuthVehicles')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getInterimStatus')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(ApplicationEntity::INTERIM_STATUS_REQUESTED)
                ->once()
                ->getMock()
            )
            ->twice()
            ->getMock();

        $command = Cmd::create($data);
        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->andReturn(['licence'])
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->andReturn($mockApplication)
            ->twice()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
