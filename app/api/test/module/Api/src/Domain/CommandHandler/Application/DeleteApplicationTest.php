<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\DeleteApplication as Command;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteApplication as CommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * DeleteApplicationTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        parent::initReferences();
    }

    public function testHandleCommandNotVariation(): void
    {
        $data = [
            'id' => 52,
        ];
        $command = Command::create($data);

        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(false);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommandWrongStatus')]
    public function testHandleCommandWrongStatus(mixed $status): void
    {
        $data = [
            'id' => 52,
        ];
        $command = Command::create($data);

        $application = m::mock(Application::class)->makePartial();
        $application->setIsVariation(true);
        $application->setStatus((new RefData())->setId($status));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $this->sut->handleCommand($command);
    }

    public static function dpTestHandleCommandWrongStatus(): array
    {
        return [
            [Application::APPLICATION_STATUS_CURTAILED],
            [Application::APPLICATION_STATUS_GRANTED],
            [Application::APPLICATION_STATUS_NOT_TAKEN_UP],
            [Application::APPLICATION_STATUS_REFUSED],
            [Application::APPLICATION_STATUS_UNDER_CONSIDERATION],
            [Application::APPLICATION_STATUS_VALID],
            [Application::APPLICATION_STATUS_WITHDRAWN],
        ];
    }
    public function testHandleCommand(): void
    {
        $data = [
            'id' => 52,
        ];
        $command = Command::create($data);

        $application = m::mock(Application::class)->makePartial();
        $application->setId($data['id']);
        $application->setIsVariation(true);
        $application->setStatus((new RefData())->setId(Application::APPLICATION_STATUS_NOT_SUBMITTED));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Application']->shouldReceive('delete')->with($application)->once();

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'id' => [],
                'messages' => [
                    'Application 52 deleted.'
                ]
            ],
            $response->toArray()
        );
    }
}
