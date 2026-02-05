<?php

declare(strict_types=1);

/**
 * End Interim Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\EndInterim;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * End Interim Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EndInterimTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EndInterim();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('GoodsDisc', \Dvsa\Olcs\Api\Domain\Repository\GoodsDisc::class);
        $this->mockRepo('CommunityLic', \Dvsa\Olcs\Api\Domain\Repository\CommunityLic::class);

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
        $command = Cmd::create(['id' => 111]);

        $application = m::mock()
            ->shouldReceive('setInterimStatus')
            ->with($this->refData[ApplicationEntity::INTERIM_STATUS_ENDED])
            ->once()
            ->shouldReceive('setInterimEnd')
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($application)
            ->once()
            ->shouldReceive('save')
            ->with($application)
            ->once()
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Interim status updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
