<?php

declare(strict_types=1);

/**
 * Create Translate To Welsh Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Task;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTranslateToWelshTask as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Task\CreateTranslateToWelshTask;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Translate To Welsh Task Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateTranslateToWelshTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateTranslateToWelshTask();

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['licence' => 111, 'description' => 'foo']);

        $result = new Result();

        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_LICENSING_GENERAL_TASK,
            'description' => 'Welsh translation required: foo',
            'urgent' => 'Y',
            'licence' => 111,
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'actionDate' => null,
            'isClosed' => false,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];

        $this->expectedSideEffect(CreateTask::class, $data, $result);

        $this->assertSame($result, $this->sut->handleCommand($command));
    }
}
