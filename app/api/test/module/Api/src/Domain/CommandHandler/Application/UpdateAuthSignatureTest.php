<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Application\UpdateAuthSignature as Cmd;

/**
 * UpdateAuthSignatureTestt
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateAuthSignatureTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateAuthSignature();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['id' => 1066, 'version' => 12, 'authSignature' => 'Y']);

        $application = $this->getTestingApplication();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command, Query::HYDRATE_OBJECT, 12)
            ->once()->andReturn($application);
        $this->repoMap['Application']->shouldReceive('save')->with($application)->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['id' => 1066, 'section' => 'declarationsInternal'],
            (new Result())->addMessage('UPDATE_APP_COMPLETION')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertTrue($application->getAuthSignature());

        $this->assertSame(['UPDATE_APP_COMPLETION', 'Auth signature updated'], $result->getMessages());
    }
}
