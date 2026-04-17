<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CloseTexTask;

/**
 * CloseTexTaskTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CloseTexTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseTexTask();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        $this->references = [
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = \Dvsa\Olcs\Api\Domain\Command\Application\CreateTexTask::create(['id' => 32]);

        $categoryApplication = new \Dvsa\Olcs\Api\Entity\System\Category();
        $categoryApplication->setId(\Dvsa\Olcs\Api\Entity\System\Category::CATEGORY_APPLICATION);

        $categoryOther = new \Dvsa\Olcs\Api\Entity\System\Category();
        $categoryOther->setId('foo');

        $subCategoryTex = new \Dvsa\Olcs\Api\Entity\System\SubCategory();
        $subCategoryTex->setId(\Dvsa\Olcs\Api\Entity\System\Category::TASK_SUB_CATEGORY_APPLICATION_TIME_EXPIRED);

        $subCategoryOther = new \Dvsa\Olcs\Api\Entity\System\SubCategory();
        $subCategoryOther->setId('bar');

        $task1 = new \Dvsa\Olcs\Api\Entity\Task\Task($categoryApplication, $subCategoryTex);
        $task1->setId(1)
            ->setIsClosed('N');
        $task2 = new \Dvsa\Olcs\Api\Entity\Task\Task($categoryOther, $subCategoryTex);
        $task2->setId(2)
            ->setIsClosed('N');
        $task3 = new \Dvsa\Olcs\Api\Entity\Task\Task($categoryApplication, $subCategoryOther);
        $task3->setId(3)
            ->setIsClosed('N');
        $task4 = new \Dvsa\Olcs\Api\Entity\Task\Task($categoryOther, $subCategoryOther);
        $task4->setId(4)
            ->setIsClosed('N');
        $task5 = new \Dvsa\Olcs\Api\Entity\Task\Task($categoryApplication, $subCategoryTex);
        $task5->setId(5)
            ->setIsClosed('N');

        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class),
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1
        );
        $application->addTasks(
            new \Doctrine\Common\Collections\ArrayCollection([$task1, $task2, $task3, $task4, $task5])
        );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Task\CloseTasks::class,
            ['ids' => [1, 5]],
            (new Result())->addMessage('RESULT')
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['RESULT'], $result->getMessages());
    }
}
