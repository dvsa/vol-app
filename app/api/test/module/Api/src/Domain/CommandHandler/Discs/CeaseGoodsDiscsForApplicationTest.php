<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as DiscRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\CeaseGoodsDiscsForApplication;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscsForApplication as Cmd;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Discs\CeaseGoodsDiscsForApplication
 */
class CeaseGoodsDiscsForApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CeaseGoodsDiscsForApplication();
        $this->mockRepo('GoodsDisc', DiscRepo::class);
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        parent::setUp();
    }

    public function testHandleCommandNewApp(): void
    {
        $data = [
            'application' => 123
        ];

        $command = Cmd::create($data);

        $application = m::mock();
        $application->shouldReceive('isNew')->with()->once()->andReturn(true);
        $application->shouldReceive('getLicence->getId')->with()->once()->andReturn(87);

        $this->repoMap['Application']->shouldReceive('fetchById')->with(123)->once()->andReturn($application);
        $this->repoMap['GoodsDisc']
            ->shouldReceive('ceaseDiscsForLicence')
            ->once()
            ->with(87);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Ceased discs for Application.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandVariationApp(): void
    {
        $data = [
            'application' => 123
        ];

        $command = Cmd::create($data);

        $application = m::mock();
        $application->shouldReceive('isNew')->with()->once()->andReturn(false);
        $application->shouldReceive('getId')->with()->once()->andReturn(123);

        $this->repoMap['Application']->shouldReceive('fetchById')->with(123)->once()->andReturn($application);
        $this->repoMap['GoodsDisc']
            ->shouldReceive('ceaseDiscsForApplication')
            ->once()
            ->with(123);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Ceased discs for Application.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
