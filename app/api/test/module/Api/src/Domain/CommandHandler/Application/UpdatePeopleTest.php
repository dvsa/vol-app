<?php

declare(strict_types=1);

/**
 * UpdatePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Repository\Licence as  LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Person as  PersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as AppOrgPersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrgPersonRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdatePeople as CommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\UpdatePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * UpdatePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdatePeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', LicenceRepo::class);
        $this->mockRepo('Person', PersonRepo::class);
        $this->mockRepo('OrganisationPerson', OrgPersonRepo::class);
        $this->mockRepo('ApplicationOrganisationPerson', AppOrgPersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr', 'org_t_llp', 'org_t_st'
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandSole(): void
    {
        $data = [
            'id' => 52,
            'person' => 79,
            'version' => 122,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'CTO',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_st']);
        $organisation->setId('87');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setId(79);
        $person->setForename('FRED');
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 0);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Person']->shouldReceive('fetchById')->with(79, \Doctrine\ORM\Query::HYDRATE_OBJECT, 122)->once()
            ->andReturn($person);

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());
            }
        );

        $orgPerson = new \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson();
        $orgPerson->setId(47);

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchListForOrganisationAndPerson')->with(87, 79)->once()
            ->andReturn([$orgPerson]);

        $this->repoMap['OrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson $op) use ($data) {
                $this->assertSame($data['position'], $op->getPosition());
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['OrganisationPerson updated'], $response->getMessages());
        $this->assertSame(['person' => 79, 'organisationPerson' => 47], $response->getIds());
    }

    public function testHandleCommandLlpAppPerson(): void
    {
        $data = [
            'id' => 52,
            'person' => 79,
            'version' => 122,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'CTO',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_llp']);
        $organisation->setId('87');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setId(79);
        $person->setForename('FRED');
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 1);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Person']->shouldReceive('fetchById')->with(79, \Doctrine\ORM\Query::HYDRATE_OBJECT, 122)->once()
            ->andReturn($person);

        $appOrgPerson = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            new \Dvsa\Olcs\Api\Entity\Person\Person()
        );
        $appOrgPerson->setId(23);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 79)->once()->andReturn($appOrgPerson);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson $appOrgPerson) use ($data) {
                $this->assertSame($data['position'], $appOrgPerson->getPosition());
            }
        );

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['ApplicationOrganisationPerson updated'], $response->getMessages());
        $this->assertSame(['person' => 79, 'applicationOrganisationPerson' => 23], $response->getIds());
    }

    public function testHandleCommandLlpOrgPerson(): void
    {
        $data = [
            'id' => 52,
            'person' => 79,
            'version' => 122,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'CTO',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_llp']);
        $organisation->setId('87');
        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence($organisation, new \Dvsa\Olcs\Api\Entity\System\RefData());
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setId(79);
        $person->setForename('FRED');
        $application = new ApplicationEntity($licence, new \Dvsa\Olcs\Api\Entity\System\RefData(), 1);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Person']->shouldReceive('fetchById')->with(79, \Doctrine\ORM\Query::HYDRATE_OBJECT, 122)->once()
            ->andReturn($person);

        $appOrgPerson = new \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson(
            $application,
            $organisation,
            new \Dvsa\Olcs\Api\Entity\Person\Person()
        );
        $appOrgPerson->setId(23);

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('fetchForApplicationAndPerson')
            ->with(52, 79)->once()->andThrow(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());
                $person->setId(7353);
            }
        );

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson $appOrgPerson) use (
                $data,
                $organisation,
                $application,
                $person
            ) {
                $this->assertSame('U', $appOrgPerson->getAction());
                $this->assertSame($organisation, $appOrgPerson->getOrganisation());
                $this->assertSame($application, $appOrgPerson->getApplication());
                $this->assertSame($data['forename'], $appOrgPerson->getPerson()->getForename());
                $this->assertSame($person, $appOrgPerson->getOriginalPerson());
                $this->assertSame($data['position'], $appOrgPerson->getPosition());
                $appOrgPerson->setId(54);
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['ApplicationOrganisationPerson created'], $response->getMessages());
        $this->assertSame(['person' => 7353, 'applicationOrganisationPerson' => 54], $response->getIds());
    }
}
