<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Licence\UpdateTotalCommunityLicences as UpdateTotalCommunityLicencesCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic as CommunityLicEntity;
use Dvsa\Olcs\Transfer\Command\CommunityLic\Annul as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic\Annul::class)]
class AnnulTest extends AbstractCommandHandlerTestCase
{
    /** @var  CommandHandler\CommunityLic\Annul */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\CommunityLic\Annul();

        $this->mockRepo('CommunityLic', Repository\CommunityLic::class);
        $this->mockRepo('Licence', Repository\Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLicEntity::STATUS_ANNUL,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds,
            'checkOfficeCopy' => true
        ];

        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('getId')
            ->andReturn(10)
            ->twice()
            ->shouldReceive('changeStatusAndExpiryDate')
            ->with($this->refData[CommunityLicEntity::STATUS_ANNUL], m::type(DateTime::class))
            ->once()
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('hasCommunityLicenceOfficeCopy')
            ->with($communityLicenceIds)
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->with($licenceId)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->shouldReceive('fetchLicencesByIds')
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateTotalCommunityLicencesCmd::class,
            [
                'id' => $licenceId,
            ],
            new Result()
        );

        $expected = [
            'id' => [
                'communityLic10' => 10
            ],
            'messages' => [
                'Community Licence 10 annulled',
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithApplication(): void
    {
        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'application' => 111,
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds,
            'checkOfficeCopy' => true
        ];

        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock(CommunityLicEntity::class)
            ->shouldReceive('getId')
            ->andReturn(10)
            ->twice()
            ->shouldReceive('changeStatusAndExpiryDate')
            ->with($this->refData[CommunityLicEntity::STATUS_ANNUL], m::type(DateTime::class))
            ->once()
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('hasCommunityLicenceOfficeCopy')
            ->with($communityLicenceIds)
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->with($licenceId)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->shouldReceive('fetchLicencesByIds')
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateTotalCommunityLicencesCmd::class,
            [
                'id' => $licenceId,
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateApplicationCompletion::class,
            [
                'id' => 111,
                'section' => 'communityLicences'
            ],
            new Result()
        );

        $expected = [
            'id' => [
                'communityLic10' => 10
            ],
            'messages' => [
                'Community Licence 10 annulled'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testCommandHandlerWithException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licenceId = 1;
        $communityLicenceIds = [10];

        $data = [
            'licence' => $licenceId,
            'communityLicenceIds' => $communityLicenceIds,
            'checkOfficeCopy' => true
        ];
        $command = Cmd::create($data);

        $mockCommunityLicence = m::mock()
            ->shouldReceive('getId')
            ->andReturn(11)
            ->once()
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('hasCommunityLicenceOfficeCopy')
            ->with($communityLicenceIds)
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchValidLicences')
            ->with($licenceId)
            ->andReturn([$mockCommunityLicence])
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }
}
