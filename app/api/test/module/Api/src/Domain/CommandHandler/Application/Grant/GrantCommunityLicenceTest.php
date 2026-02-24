<?php

declare(strict_types=1);

/**
 * Grant Community Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantCommunityLicence as GrantCommunityLicenceCmd;
use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\GrantCommunityLicence;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Grant Community Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantCommunityLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantCommunityLicence();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('CommunityLic', \Dvsa\Olcs\Api\Domain\Repository\CommunityLic::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            CommunityLic::STATUS_ACTIVE,
            CommunityLic::STATUS_PENDING,
            CommunityLic::STATUS_RETURNDED,
            CommunityLic::STATUS_WITHDRAWN,
            CommunityLic::STATUS_SUSPENDED,
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantCommunityLicenceCmd::create($data);

        $communityLics = new ArrayCollection();

        /** @var CommunityLic $pendingRecord */
        $pendingRecord = m::mock(CommunityLic::class)->makePartial();
        $pendingRecord->setId(123);
        $pendingRecord->setStatus($this->refData[CommunityLic::STATUS_PENDING]);
        $communityLics->add($pendingRecord);

        /** @var CommunityLic $suspendedRecord */
        $suspendedRecord = m::mock(CommunityLic::class)->makePartial();
        $suspendedRecord->setId(1234);
        $suspendedRecord->setStatus($this->refData[CommunityLic::STATUS_SUSPENDED]);
        $communityLics->add($suspendedRecord);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->shouldReceive('canHaveCommunityLicences')
            ->andReturn(true);

        $licence->shouldReceive('getCommunityLics')
            ->andReturn($communityLics);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['CommunityLic']->shouldReceive('save')
            ->once()
            ->with($pendingRecord);

        $data = [
            'isBatchReprint' => false,
            'licence' => 222,
            'communityLicenceIds' => [123],
            'identifier' => null
        ];
        $result1 = new Result();
        $result1->addMessage('GenerateBatch');
        $this->expectedSideEffect(GenerateBatch::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 community licence(s) activated',
                'GenerateBatch'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->refData[CommunityLic::STATUS_ACTIVE], $pendingRecord->getStatus());
        $this->assertEquals(date('Y-m-d'), $pendingRecord->getSpecifiedDate()->format('Y-m-d'));
    }

    public function testHandleCommandCantHave(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantCommunityLicenceCmd::create($data);

        $communityLics = new ArrayCollection();

        /** @var CommunityLic $activeRecord */
        $activeRecord = m::mock(CommunityLic::class)->makePartial();
        $activeRecord->setId(123);
        $activeRecord->setStatus($this->refData[CommunityLic::STATUS_ACTIVE]);
        $communityLics->add($activeRecord);

        /** @var CommunityLic $withdrawnRecord */
        $withdrawnRecord = m::mock(CommunityLic::class)->makePartial();
        $withdrawnRecord->setId(1234);
        $withdrawnRecord->setStatus($this->refData[CommunityLic::STATUS_WITHDRAWN]);
        $communityLics->add($withdrawnRecord);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->shouldReceive('canHaveCommunityLicences')
            ->andReturn(false);

        $licence->shouldReceive('getCommunityLics')
            ->andReturn($communityLics);

        $licence->shouldReceive('setTotCommunityLicences')
            ->with(0)
            ->once();

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['CommunityLic']->shouldReceive('save')
            ->once()
            ->with($activeRecord);

        $this->repoMap['Licence']->shouldReceive('save')
            ->once()
            ->with($licence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Total community licence(s) count cleared',
                '1 community licence(s) voided'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->refData[CommunityLic::STATUS_RETURNDED], $activeRecord->getStatus());
        $this->assertEquals(date('Y-m-d'), $activeRecord->getExpiredDate()->format('Y-m-d'));
    }
}
