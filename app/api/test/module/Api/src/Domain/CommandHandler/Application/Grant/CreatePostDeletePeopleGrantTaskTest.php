<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\Grant;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreatePostDeletePeopleGrantTask as CreatePostGrantPeopleTasksCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\Grant\CreatePostDeletePeopleGrantTask;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class CreatePostDeletePeopleGrantTaskTest
 */
class CreatePostDeletePeopleGrantTaskTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePostDeletePeopleGrantTask();
        $this->mockRepo('Application', ApplicationRepository::class);
        $this->mockRepo('OrganisationPerson', OrganisationPerson::class);

        parent::setUp();
    }

    public function testHandleCommandWithNoVariationType(): void
    {
        $command = CreatePostGrantPeopleTasksCommand::create(['applicationId' => 'TEST_APPLICATION_ID']);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with('TEST_APPLICATION_ID')
            ->andReturn($this->createMockApplication(null));

        $this->assertEquals(['id' => [], 'messages' => []], $this->sut->handleCommand($command)->toArray());
    }

    public function testHandleCommandWithNonDirectorChangeVariation(): void
    {
        $command = CreatePostGrantPeopleTasksCommand::create(['applicationId' => 'TEST_APPLICATION_ID']);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with('TEST_APPLICATION_ID')
            ->andReturn($this->createMockApplication('SomeOtherVariationType'));

        $this->assertEquals(['id' => [], 'messages' => []], $this->sut->handleCommand($command)->toArray());
    }

    public function testHandleCommandWhenPeopleRemain(): void
    {
        $command = CreatePostGrantPeopleTasksCommand::create(['applicationId' => 'TEST_APPLICATION_ID']);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with('TEST_APPLICATION_ID')
            ->andReturn($this->createMockApplication(Application::VARIATION_TYPE_DIRECTOR_CHANGE));

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchCountForOrganisation')
            ->with('TEST_ORGANISATION_ID')
            ->andReturn(1);

        $this->assertEquals(['id' => [], 'messages' => []], $this->sut->handleCommand($command)->toArray());
    }

    /**
     *
     * @param $organisationType
     * @param $expectedTaskDescription
     * @param $expectedSubCategory
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCreateTaskWhenNoPeopleRemainCases')]
    public function testCreateTaskWhenNoPeopleRemain(mixed $organisationType, mixed $expectedTaskDescription, mixed $expectedSubCategory): void
    {
        $command = CreatePostGrantPeopleTasksCommand::create(['applicationId' => 'TEST_APPLICATION_ID']);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with('TEST_APPLICATION_ID')
            ->andReturn($this->createMockApplication(Application::VARIATION_TYPE_DIRECTOR_CHANGE, $organisationType));

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchCountForOrganisation')
            ->with('TEST_ORGANISATION_ID')
            ->andReturn(0);

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => $expectedSubCategory,
                'description' => $expectedTaskDescription,
                'licence' => 'TEST_LICENCE_ID',
            ],
            new Result()
        );

        $this->assertEquals(
            ['id' => [], 'messages' => ['Task created as there are no people in the organisation',]],
            $this->sut->handleCommand($command)->toArray()
        );
    }

    public static function provideCreateTaskWhenNoPeopleRemainCases(): array
    {
        return [
            [
                Organisation::ORG_TYPE_REGISTERED_COMPANY,
                'Last director removed',
                Category::TASK_SUB_CATEGORY_DIRECTOR_CHANGE_DIGITAL
            ],
            [Organisation::ORG_TYPE_LLP, 'Last partner removed', Category::TASK_SUB_CATEGORY_PARTNER_CHANGE_DIGITAL],
            [Organisation::ORG_TYPE_OTHER, 'Last person removed', Category::TASK_SUB_CATEGORY_PERSON_CHANGE_DIGITAL],
            ['some-other-test-type', 'Last person removed', Category::TASK_SUB_CATEGORY_PERSON_CHANGE_DIGITAL],
        ];
    }

    private function createMockApplication(mixed $variationType, mixed $organisationType = null): mixed
    {
        /** @var Organisation|m\Mock $organisation */
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->setId('TEST_ORGANISATION_ID');
        if ($organisationType) {
            $organisation->setType(new RefData($organisationType));
        }

        /** @var Licence|m\Mock $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOrganisation($organisation);
        $licence->setId('TEST_LICENCE_ID');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setLicence($licence);
        $application->setVariationType(is_null($variationType) ? null : new RefData($variationType));

        return $application;
    }
}
