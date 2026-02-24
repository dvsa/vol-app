<?php

declare(strict_types=1);

/**
 * CreatePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreatePeople as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as ApplicationOrganisationPersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrganisationPersonRepo;
use Dvsa\Olcs\Api\Domain\Repository\Person as PersonRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreatePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * CreatePeopleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreatePeopleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('OrganisationPerson', OrganisationPersonRepo::class);
        $this->mockRepo('ApplicationOrganisationPerson', ApplicationOrganisationPersonRepo::class);
        $this->mockRepo('Person', PersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr', 'org_t_llp', 'org_t_p'
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandNoDelta(): void
    {
        $data = [
            'id' => 52,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'CFO',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_p']);
        $licence = new LicenceEntity($organisation, new RefData());
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), 0);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());
                $person->setId(753);
            }
        );

        $this->repoMap['OrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson $op) use ($data, $organisation) {
                $this->assertSame($organisation, $op->getOrganisation());
                $this->assertSame(753, $op->getPerson()->getId());
                $this->assertSame('CFO', $op->getPosition());
                $op->setId(43);
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['organisatonPerson' => 43, 'person' => 753], $result->getIds());
    }

    public function testHandleCommandDelta(): void
    {
        $data = [
            'id' => 52,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'CFO',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_llp']);
        $licence = new LicenceEntity($organisation, new RefData());
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), 1);
        $application->setId(52);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($application);

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());
                $person->setId(753);
            }
        );

        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson $aop) use (
                $data,
                $organisation,
                $application
            ) {
                $this->assertSame('A', $aop->getAction());
                $this->assertSame($organisation, $aop->getOrganisation());
                $this->assertSame($application, $aop->getApplication());
                $this->assertSame(753, $aop->getPerson()->getId());
                $this->assertSame($data['position'], $aop->getPosition());
                $aop->setId(43);
            }
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion::class,
            ['section' => 'people', 'id' => 52],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['applicationOrganisationPerson' => 43, 'person' => 753], $result->getIds());
    }
}
