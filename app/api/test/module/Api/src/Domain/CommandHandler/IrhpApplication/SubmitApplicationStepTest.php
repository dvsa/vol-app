<?php

declare(strict_types=1);

/**
 * Submit Application Step test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\SubmitApplicationStep as Sut;
use Dvsa\Olcs\Api\Domain\FormControlServiceManager;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContextGenerator;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Strategy\FormControlStrategyInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplicationStep as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Submit Application Step test
 */
final class SubmitApplicationStepTest extends AbstractCommandHandlerTestCase
{
    public const int IRHP_APPLICATION_ID = 23;
    public const int IRHP_PERMIT_APPLICATION_ID = 457;
    public const string SLUG = 'removals-eligibility';
    public const string REPOSITORY_NAME = 'IrhpApplication';
    public const string DESTINATION_NAME = 'DESTINATION_NAME';

    private $qaEntity;

    private $command;

    public function setUp(): void
    {
        $this->sut = new Sut();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'QaContextGenerator' => m::mock(QaContextGenerator::class),
            'FormControlServiceManager' => m::mock(FormControlServiceManager::class)
        ];

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);

        $this->qaEntity = m::mock(QaEntityInterface::class);
        $this->qaEntity->shouldReceive('getRepositoryName')
            ->withNoArgs()
            ->andReturn(self::REPOSITORY_NAME);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStepEntity);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->qaEntity);

        $this->mockedSmServices['QaContextGenerator']->shouldReceive('generate')
            ->with(self::IRHP_APPLICATION_ID, self::IRHP_PERMIT_APPLICATION_ID, self::SLUG)
            ->andReturn($qaContext);

        $postData = [
            'fieldset123' => [
                'qaElement' => '123'
            ]
        ];

        $formControlStrategy = m::mock(FormControlStrategyInterface::class);
        $formControlStrategy->shouldReceive('saveFormData')
            ->with($qaContext, $postData)
            ->once()
            ->andReturn(self::DESTINATION_NAME);

        $this->mockedSmServices['FormControlServiceManager']->shouldReceive('getByApplicationStep')
            ->with($applicationStepEntity)
            ->andReturn($formControlStrategy);

        $this->command = Cmd::create(
            [
                'id' => self::IRHP_APPLICATION_ID,
                'irhpPermitApplication' => self::IRHP_PERMIT_APPLICATION_ID,
                'slug' => self::SLUG,
                'postData' => $postData
            ]
        );

        parent::setUp();
    }

    public function testHandleCommandEntityDeleted(): void
    {
        $this->repoMap[self::REPOSITORY_NAME]->shouldReceive('contains')
            ->with($this->qaEntity)
            ->andReturnFalse();

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            [self::DESTINATION_NAME],
            $result->getMessages()
        );
    }

    public function testHandleCommandEntityExists(): void
    {
        $this->repoMap[self::REPOSITORY_NAME]->shouldReceive('contains')
            ->with($this->qaEntity)
            ->andReturnTrue();
        $this->qaEntity->shouldReceive('onSubmitApplicationStep')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap[self::REPOSITORY_NAME]->shouldReceive('save')
            ->with($this->qaEntity)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            [self::DESTINATION_NAME],
            $result->getMessages()
        );
    }
}
