<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler\CommunityLic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLic as CommunityLicRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspension as CommunityLicSuspensionRepo;
use Dvsa\Olcs\Api\Domain\Repository\CommunityLicSuspensionReason as CommunityLicSuspensionReasonRepo;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Cli\Domain\Command\CommunityLic\Activate as Cmd;
use Dvsa\Olcs\Cli\Domain\CommandHandler\CommunityLic\Activate;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Activate command handle test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ActivateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Activate();
        $this->mockRepo('CommunityLic', CommunityLicRepo::class);
        $this->mockRepo('CommunityLicSuspension', CommunityLicSuspensionRepo::class);
        $this->mockRepo('CommunityLicSuspensionReason', CommunityLicSuspensionReasonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLic::STATUS_ACTIVE
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $params = [
            'communityLicenceIds' => [1]
        ];

        $mockCommunityLicenceSuspensionReason = m::mock();

        $reasons = new ArrayCollection();
        $reasons->add($mockCommunityLicenceSuspensionReason);

        $mockCommunityLicenceSuspension = m::mock()
            ->shouldReceive('getCommunityLicSuspensionReasons')
            ->andReturn($reasons)
            ->once()
            ->getMock();

        $communityLicenceSuspensions = new ArrayCollection();
        $communityLicenceSuspensions->add($mockCommunityLicenceSuspension);

        $mockCommunityLicence = m::mock()
            ->shouldReceive('setStatus')
            ->with($this->refData[CommunityLic::STATUS_ACTIVE])
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->once()
            ->shouldReceive('getCommunityLicSuspensions')
            ->andReturn($communityLicenceSuspensions)
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

        $this->repoMap['CommunityLicSuspension']
            ->shouldReceive('delete')
            ->with($mockCommunityLicenceSuspension)
            ->once()
            ->getMock();

        $this->repoMap['CommunityLicSuspensionReason']
            ->shouldReceive('delete')
            ->with($mockCommunityLicenceSuspensionReason)
            ->once()
            ->getMock();

        $response = $this->sut->handleCommand(Cmd::create($params));

        $expected = [
            'id' => [],
            'messages' => [
                'Community licence 1 activated'
            ]
        ];
        $this->assertEquals($expected, $response->toArray());
    }
}
