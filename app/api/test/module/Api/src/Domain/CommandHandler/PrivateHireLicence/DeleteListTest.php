<?php

declare(strict_types=1);

/**
 * DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PrivateHireLicence;

use Dvsa\Olcs\Api\Domain\CommandHandler\PrivateHireLicence\DeleteList as CommandHandler;
use Dvsa\Olcs\Transfer\Command\PrivateHireLicence\DeleteList as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;

/**
 * DeleteListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeleteListTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('PrivateHireLicence', \Dvsa\Olcs\Api\Domain\Repository\PrivateHireLicence::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['ids' => [4323, 12373], 'licence' => 1, 'lva' => 'licence']);

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(new \Dvsa\Olcs\Api\Entity\System\RefData()),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $licence->setTrafficArea('FOO');
        $phl1 = new \Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence();
        $phl1->setLicence($licence);
        $phl1->setPrivateHireLicenceNo('number1');
        $phl2 = new \Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence();
        $phl2->setLicence($licence);
        $phl2->setPrivateHireLicenceNo('number2');

        $this->repoMap['PrivateHireLicence']->shouldReceive('fetchById')->with(4323)->once()->andReturn($phl1);
        $this->repoMap['PrivateHireLicence']->shouldReceive('delete')->with($phl1)->once()->andReturn();
        $this->repoMap['PrivateHireLicence']->shouldReceive('fetchById')->with(12373)->once()->andReturn($phl2);
        $this->repoMap['PrivateHireLicence']->shouldReceive('delete')->with($phl2)->once()->andReturn();

        $this->mockCreateTask();
        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'PrivateHireLicence ID 4323 deleted',
                'Task 1 created successfully',
                'PrivateHireLicence ID 12373 deleted',
                'Task 2 created successfully'
            ],
            $response->getMessages()
        );
    }

    public function testHandleCommandNotNullTrafficArea(): void
    {
        $command = Command::create(['ids' => [4323, 12373], 'licence' => 1, 'lva' => 'licence']);

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(new \Dvsa\Olcs\Api\Entity\System\RefData()),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $licence->setTrafficArea('FOO');
        $licence->addPrivateHireLicences('SOME');
        $phl1 = new \Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence();
        $phl1->setLicence($licence);
        $phl1->setPrivateHireLicenceNo('number1');
        $phl2 = new \Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence();
        $phl2->setLicence($licence);
        $phl2->setPrivateHireLicenceNo('number2');

        $this->repoMap['PrivateHireLicence']->shouldReceive('fetchById')->with(4323)->once()->andReturn($phl1);
        $this->repoMap['PrivateHireLicence']->shouldReceive('delete')->with($phl1)->once()->andReturn();
        $this->repoMap['PrivateHireLicence']->shouldReceive('fetchById')->with(12373)->once()->andReturn($phl2);
        $this->repoMap['PrivateHireLicence']->shouldReceive('delete')->with($phl2)->once()->andReturn();

        $this->mockCreateTask();
        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'PrivateHireLicence ID 4323 deleted',
                'Task 1 created successfully',
                'PrivateHireLicence ID 12373 deleted',
                'Task 2 created successfully'
            ],
            $response->getMessages()
        );
    }

    protected function mockCreateTask(): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->twice()
            ->with(Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $data1 = [
            'licence' => 1,
            'category' => CategoryEntity::CATEGORY_APPLICATION,
            'subCategory' => CategoryEntity::TASK_SUB_CATEGORY_CHANGE_TO_TAXI_PHV_DIGITAL,
            'description' => 'Taxi licence deleted - number1',
            'isClosed' => 0,
            'urgent' => 0,
            'actionDate' => null,
            'assignedToUser' => null,
            'assignedToTeam' => null,
            'application' => null,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null
        ];
        $result1 = new Result();
        $result1->addId('task', 1);
        $this->expectedSideEffect(CreateTaskCmd::class, $data1, $result1);

        $data2 = $data1;
        $data2['description'] = 'Taxi licence deleted - number2';
        $result2 = new Result();
        $result2->addId('task', 2);
        $this->expectedSideEffect(CreateTaskCmd::class, $data2, $result2);
    }
}
