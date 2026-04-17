<?php

declare(strict_types=1);

/**
 * End interim test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\EndInterim;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Variation\EndInterim as Cmd;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * End interim test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EndInterimTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EndInterim();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ApplicationEntity::INTERIM_STATUS_ENDED
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licenceId' => 1
        ];

        $command = Cmd::create($data);

        $mockApplication = m::mock()
            ->shouldReceive('setInterimStatus')
            ->with($this->refData[ApplicationEntity::INTERIM_STATUS_ENDED])
            ->once()
            ->shouldReceive('setInterimEnd')
            ->with(m::type(DateTime::class))
            ->once()
            ->shouldReceive('getId')
            ->andReturn(2)
            ->once()
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('getApplications')
            ->andReturn([$mockApplication])
            ->once()
            ->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchWithVariationsAndInterimInforce')
            ->with(1)
            ->andReturn([$mockLicence])
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('save')
            ->with($mockApplication)
            ->once()
            ->getMock();

        $expected = [
            'id' => [],
            'messages' => [
                'Interim ended for variations with ids: 2'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithNoVariation(): void
    {
        $data = [
            'licenceId' => 1
        ];

        $command = Cmd::create($data);
        $this->repoMap['Licence']->shouldReceive('fetchWithVariationsAndInterimInforce')
            ->with(1)
            ->andReturn([])
            ->once()
            ->getMock();

        $expected = [
            'id' => [],
            'messages' => [
                'No variations with interim status in force found'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }
}
