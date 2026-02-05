<?php

declare(strict_types=1);

/**
 * DeletePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Person as  PersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as AppOrgPersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrgPersonRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeletePeople as CommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\DeletePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * DeletePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DeletePeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', LicenceRepo::class);
        $this->mockRepo('Person', PersonRepo::class);
        $this->mockRepo('ApplicationOrganisationPerson', AppOrgPersonRepo::class);
        $this->mockRepo('OrganisationPerson', OrgPersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr', 'org_t_rc', 'org_t_st'
        ];

        $this->references = [
            \Dvsa\Olcs\Api\Entity\Person\Person::class => [
                234 => m::mock(\Dvsa\Olcs\Api\Entity\Person\Person::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandNotDelta(): void
    {
        $data = [
            'id' => 52,
            'personIds' => [79, 234]
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_st']);
        $organisation->setId(87);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 0);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $orgPerson1 = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $orgPerson1->setId(179);
        $orgPerson2 = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $orgPerson2->setId(1234);

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisationAndPerson')
            ->with(87, 79)->once()->andReturn([$orgPerson1]);
        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisationAndPerson')
            ->with(87, 234)->once()->andReturn([$orgPerson2]);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList::class,
            ['ids' => [179]],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('179 DELETED')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\OrganisationPerson\DeleteList::class,
            ['ids' => [1234]],
            (new \Dvsa\Olcs\Api\Domain\Command\Result())->addMessage('1234 DELETED')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);
        $this->assertSame(
            [
                '179 DELETED',
                '1234 DELETED',
            ],
            $response->getMessages()
        );
    }

    public function testHandleCommandDelta(): void
    {
        $data = [
            'id' => 52,
            'personIds' => [79, 234]
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_rc']);
        $organisation->setId(87);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 1);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $appOrgPerson1 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            new \Dvsa\Olcs\Api\Entity\Person\Person()
        );
        $appOrgPerson1->setId(179);
        $appOrgPerson1->setPerson((new \Dvsa\Olcs\Api\Entity\Person\Person())->setId(79));
        $appOrgPerson1->setAction(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::ACTION_ADD);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 79)->once()->andReturn($appOrgPerson1);
        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('delete')
            ->with($appOrgPerson1)->once();
        $this->repoMap['Person']->shouldReceive('delete')
            ->with($appOrgPerson1->getPerson())->once();

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 234)->once()->andThrow(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson $appOrgPerson) use (
                $organisation,
                $application
            ) {
                $this->assertSame(
                    \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::ACTION_DELETE,
                    $appOrgPerson->getAction()
                );
                $this->assertSame($organisation, $appOrgPerson->getOrganisation());
                $this->assertSame($application, $appOrgPerson->getApplication());
                $this->assertSame(234, $appOrgPerson->getPerson()->getId());
                $appOrgPerson->setId(923);
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'ApplicationOrganisationPerson ID 179 deleted',
                'ApplicationOrganisationPerson ID 923 delete delta created',
            ],
            $response->getMessages()
        );
    }

    public function testHandleCommandDeltaAlreadyDeleted(): void
    {
        $data = [
            'id' => 52,
            'personIds' => [79]
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_rc']);
        $organisation->setId(87);
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 1);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $appOrgPerson1 = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            new \Dvsa\Olcs\Api\Entity\Person\Person()
        );
        $appOrgPerson1->setId(179);
        $appOrgPerson1->setPerson((new \Dvsa\Olcs\Api\Entity\Person\Person())->setId(79));
        $appOrgPerson1->setAction(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::ACTION_DELETE);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 79)->once()->andReturn($appOrgPerson1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [],
            $response->getMessages()
        );
    }
}
