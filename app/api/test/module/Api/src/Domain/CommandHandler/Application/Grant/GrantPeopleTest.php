<?php

declare(strict_types=1);

/**
 * Grant People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostAddPeopleGrantTask;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostDeletePeopleGrantTask;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\GrantPeople as GrantPeopleCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\GrantPeople;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Grant People Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantPeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantPeople();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('Person', \Dvsa\Olcs\Api\Domain\Repository\Person::class);
        $this->mockRepo('OrganisationPerson', \Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandWithoutRecords(): void
    {
        $data = [
            'id' => 'TEST_APPLICATION_ID'
        ];

        $command = GrantPeopleCmd::create($data);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setApplicationOrganisationPersons(new ArrayCollection());

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutAdd(): void
    {
        $data = [
            'id' => 'TEST_APPLICATION_ID'
        ];

        $command = GrantPeopleCmd::create($data);

        $person1 = new Person();

        /** @var ApplicationOrganisationPerson $aop1 */
        $aop1 = m::mock(ApplicationOrganisationPerson::class)->makePartial();
        $aop1->setAction('A');
        $aop1->setPerson($person1);

        $aops = new ArrayCollection();
        $aops->add($aop1);

        $application = $this->createMockApplication($aops);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $savedPerson = null;

        $this->repoMap['Person']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Person $person) use ($person1, &$savedPerson) {
                    $this->assertNotSame($person1, $person);
                    $savedPerson = $person;
                    $savedPerson->setId(629);
                }
            );

        $this->repoMap['OrganisationPerson']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (OrganisationPerson $orgPerson) use ($aop1) {
                    $this->assertNotSame($aop1, $orgPerson);
                }
            );

        $this->expectCreatePostGrantTasks();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'createdPerson' => 629
            ],
            'messages' => [
                'Organisation person records have been copied',
                'create-post-delete-tasks-grant-message-1',
                'create-post-delete-tasks-grant-message-2',
                'create-post-add-tasks-grant-message-1',
                'create-post-add-tasks-grant-message-2',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutUpdate(): void
    {
        $data = [
            'id' => 'TEST_APPLICATION_ID'
        ];

        $command = GrantPeopleCmd::create($data);

        $person1 = new Person();
        $person2 = new Person();

        $org1 = m::mock(Organisation::class)->makePartial();

        /** @var ApplicationOrganisationPerson $aop1 */
        $aop1 = m::mock(ApplicationOrganisationPerson::class)->makePartial();
        $aop1->setAction('U');
        $aop1->setPerson($person1);
        $aop1->setOriginalPerson($person2);
        $aop1->setOrganisation($org1);

        $aops = new ArrayCollection();
        $aops->add($aop1);

        $application = $this->createMockApplication($aops);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $savedPerson = null;

        $this->repoMap['Person']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Person $person) use ($person1, &$savedPerson) {
                    $this->assertNotSame($person1, $person);
                    $savedPerson = $person;
                }
            );

        $orgPersonRecord = m::mock(OrganisationPerson::class)->makePartial();

        $orgPersonRecords = [
            $orgPersonRecord
        ];

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchByOrgAndPerson')
            ->with($org1, $person2)
            ->andReturn($orgPersonRecords)
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (OrganisationPerson $orgPerson) use ($aop1) {
                    $this->assertNotSame($aop1, $orgPerson);
                }
            )
            ->shouldReceive('delete')
            ->once()
            ->with($orgPersonRecord);

        $this->expectCreatePostGrantTasks();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Organisation person records have been copied',
                'create-post-delete-tasks-grant-message-1',
                'create-post-delete-tasks-grant-message-2',
                'create-post-add-tasks-grant-message-1',
                'create-post-add-tasks-grant-message-2',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithoutDelete(): void
    {
        $data = [
            'id' => 'TEST_APPLICATION_ID'
        ];

        $command = GrantPeopleCmd::create($data);

        $person2 = new Person();

        $org1 = m::mock(Organisation::class)->makePartial();

        /** @var ApplicationOrganisationPerson $aop1 */
        $aop1 = m::mock(ApplicationOrganisationPerson::class)->makePartial();
        $aop1->setAction('D');
        $aop1->setPerson($person2);
        $aop1->setOrganisation($org1);

        $aops = new ArrayCollection();
        $aops->add($aop1);
        $application = $this->createMockApplication($aops);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $orgPersonRecord = m::mock(OrganisationPerson::class)->makePartial();

        $orgPersonRecords = [
            $orgPersonRecord
        ];

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchByOrgAndPerson')
            ->with($org1, $person2)
            ->andReturn($orgPersonRecords)
            ->shouldReceive('delete')
            ->once()
            ->with($orgPersonRecord);

        $this->expectCreatePostGrantTasks();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Organisation person records have been copied',
                'create-post-delete-tasks-grant-message-1',
                'create-post-delete-tasks-grant-message-2',
                'create-post-add-tasks-grant-message-1',
                'create-post-add-tasks-grant-message-2',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    private function createMockApplication(mixed $applicationOrganisationPersons): mixed
    {
        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setApplicationOrganisationPersons($applicationOrganisationPersons);
        return $application;
    }

    private function expectCreatePostGrantTasks(): void
    {
        $deleteTaskResult = new Result();
        $deleteTaskResult->addMessage('create-post-delete-tasks-grant-message-1');
        $deleteTaskResult->addMessage('create-post-delete-tasks-grant-message-2');
        $this->expectedSideEffect(
            CreatePostDeletePeopleGrantTask::class,
            ['applicationId' => 'TEST_APPLICATION_ID'],
            $deleteTaskResult
        );

        $addTaskResults = new Result();
        $addTaskResults->addMessage('create-post-add-tasks-grant-message-1');
        $addTaskResults->addMessage('create-post-add-tasks-grant-message-2');
        $this->expectedSideEffect(
            CreatePostAddPeopleGrantTask::class,
            ['applicationId' => 'TEST_APPLICATION_ID'],
            $addTaskResults
        );
    }
}
