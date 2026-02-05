<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Command\Licence\CreateSurrenderPsvLicenceTasks as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateSurrenderPsvLicenceTasks;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;

/**
 * Create surrender psv licence tasks test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateSurrenderPsvLicenceTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateSurrenderPsvLicenceTasks();

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['ids' => [111, 222]]);

        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'description' => Task::TASK_DESCRIPTION_LICENCE_EXPIRED,
            'actionDate' => (new DateTime('now'))->format('Y-m-d'),
            'licence' => 111
        ];
        $this->expectedSideEffect(CreateTask::class, $data, new Result());

        $data = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'description' => Task::TASK_DESCRIPTION_LICENCE_EXPIRED,
            'actionDate' => (new DateTime('now'))->format('Y-m-d'),
            'licence' => 222
        ];
        $this->expectedSideEffect(CreateTask::class, $data, new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => [
                '2 task(s) created',
            ],
            'id' => [],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
