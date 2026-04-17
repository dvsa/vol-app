<?php

declare(strict_types=1);

/**
 * Grant Transport Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantTransportManager as GrantTransportManagerCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\TmNominatedTask;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\GrantTransportManager;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Grant Transport Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantTransportManagerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantTransportManager();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('TransportManagerLicence', \Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence::class);
        $this->mockRepo('OtherLicence', \Dvsa\Olcs\Api\Domain\Repository\OtherLicence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandRestricted(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantTransportManagerCmd::create($data);

        /** @var TransportManagerLicence $tmLicence */
        $tmLicence = m::mock(TransportManagerLicence::class)->makePartial();

        $tmLicences = [
            $tmLicence
        ];

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setTmLicences($tmLicences);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);

        $licence->shouldReceive('isRestricted')
            ->andReturn(true);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['TransportManagerLicence']->shouldReceive('delete')
            ->with($tmLicence);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'All transport managers removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandAdd(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantTransportManagerCmd::create($data);

        /** @var TransportManager $ltm */
        $ltm = m::mock(TransportManager::class)->makePartial();

        $matchingTms = new ArrayCollection();
        $matchingTms->add($ltm);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getTmLicences->matching')
            ->with(m::type(Criteria::class))
            ->andReturn($matchingTms);

        /** @var TransportManager $tm */
        $tm = m::mock(TransportManager::class)->makePartial();

        $otherLicence = new OtherLicence();
        $otherLicence->setId(123);

        $otherLicences = [
            $otherLicence
        ];

        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setId(111);
        $tma->setAction('A');
        $tma->setTransportManager($tm);
        $tma->setOtherLicences($otherLicences);

        $tmas = [
            $tma
        ];

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setTransportManagers($tmas);

        $licence->shouldReceive('isRestricted')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['TransportManagerLicence']->shouldReceive('delete')
            ->with($ltm)
            ->shouldReceive('save')
            ->once();

        $this->repoMap['OtherLicence']->shouldReceive('save')
            ->once();

        $result = new Result();
        $data = [
            'entityId' => 111,
            'type' => Queue::TYPE_TM_SNAPSHOT,
            'status' => Queue::STATUS_QUEUED
        ];
        $this->expectedSideEffect(Create::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Transport managers copied to licence'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandDelete(): void
    {
        $data = [
            'id' => 111
        ];

        $command = GrantTransportManagerCmd::create($data);

        /** @var TransportManager $ltm */
        $ltm = m::mock(TransportManager::class)->makePartial();

        $matchingTms = new ArrayCollection();
        $matchingTms->add($ltm);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->shouldReceive('getTmLicences->matching')
            ->with(m::type(Criteria::class))
            ->andReturn($matchingTms);

        /** @var TransportManager $tm */
        $tm = m::mock(TransportManager::class)->makePartial();

        /** @var TransportManagerApplication $tma */
        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->setAction('D');
        $tma->setTransportManager($tm);

        $tmas = [
            $tma
        ];

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicence($licence);
        $application->setTransportManagers($tmas);
        $application->setIsVariation(true);

        $licence->shouldReceive('isRestricted')
            ->andReturn(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['TransportManagerLicence']->shouldReceive('delete')
            ->with($ltm);

        $result1 = new Result();
        $result1->addMessage('TmNominatedTask');
        $data = [
            'ids' => [222]
        ];
        $this->expectedSideEffect(TmNominatedTask::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'TmNominatedTask',
                'Transport managers copied to licence'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
