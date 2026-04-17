<?php

declare(strict_types=1);

/**
 * Create Office Copy Test / Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Application\CreateOfficeCopy;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\Application\CreateOfficeCopy as Cmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCmd;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;

/**
 * Create Office Copy Test / Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateOfficeCopyTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateOfficeCopy();
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
            'identifier' => $identifier
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock()
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

        $mockLicence = m::mock()
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn('A')
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->andReturn($mockApplication)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $communityLic = null;

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchOfficeCopy')
            ->andReturn(null)
            ->once()
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CommunityLicEntity::class))
            ->andReturnUsing(
                function (CommunityLicEntity $lic) use (&$communityLic) {
                    $lic->setId(111);
                    $communityLic = $lic;
                }
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
                'Office copy created successfully'
            ]
        ];
        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('A', $communityLic->getSerialNoPrefix());
        $this->assertEquals($licenceId, $communityLic->getLicence()->getId());
        $this->assertEquals(0, $communityLic->getIssueNo());
        $this->assertEquals(CommunityLicEntity::STATUS_PENDING, $communityLic->getStatus()->getId());
    }

    public function testHandleCommandInterimInForce(): void
    {
        $licenceId = 1;
        $identifier = 2;
        $data = [
            'licence' => $licenceId,
            'identifier' => $identifier
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock()
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

        $mockLicence = m::mock()
            ->shouldReceive('getSerialNoPrefixFromTrafficArea')
            ->andReturn('A')
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->andReturn($mockApplication)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $communityLic = null;

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchOfficeCopy')
            ->andReturn(null)
            ->once()
            ->shouldReceive('save')
            ->once()
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
                'identifier' => $identifier,
                'communityLicenceIds' => [111]
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
                'Office copy created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('A', $communityLic->getSerialNoPrefix());
        $this->assertEquals($licenceId, $communityLic->getLicence()->getId());
        $this->assertEquals(0, $communityLic->getIssueNo());
        $this->assertEquals(CommunityLicEntity::STATUS_ACTIVE, $communityLic->getStatus()->getId());
    }

    public function testHandleCommandOfficeCopyExists(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licenceId = 1;
        $identifier = 2;
        $data = [
            'licence' => $licenceId,
            'identifier' => $identifier
        ];

        $command = Cmd::create($data);
        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchOfficeCopy')
            ->andReturn('officeCopy')
            ->once();

        $this->sut->handleCommand($command);
    }
}
