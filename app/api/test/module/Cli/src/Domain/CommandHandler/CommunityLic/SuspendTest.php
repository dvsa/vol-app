<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Suspend as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\CommunityLic\Suspend;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Suspend command handle test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SuspendTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Suspend();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLic::STATUS_SUSPENDED
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'communityLicenceIds' => [1]
        ];

        $mockCommunityLicence = m::mock()
            ->shouldReceive('setStatus')
            ->with($this->refData[CommunityLic::STATUS_SUSPENDED])
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLic']
            ->shouldReceive('fetchByIds')
            ->with([1])
            ->once()
            ->andReturn([$mockCommunityLicence])
            ->shouldReceive('save')
            ->with($mockCommunityLicence)
            ->once()
            ->getMock();

        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => [
                'Community licence 1 suspended'
            ]
        ];
        $this->assertEquals($expected, $response->toArray());
    }
}
