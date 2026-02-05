<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreatePeople as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePeople as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreatePeople::class)]
class CreatePeopleTest extends AbstractCommandHandlerTestCase
{
    public const ORG_PERSON_ID = 9001;
    public const PERSON_ID = 8001;
    public const LIC_ID = 7001;

    /** @var  CommandHandler */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CommandHandler();

        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);
        $this->mockRepo('Person', Repository\Person::class);

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

    public function testHandleCommand(): void
    {
        $data = [
            'id' => self::LIC_ID,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'Jerry',
            'position' => 'unit_postion',
        ];
        $command = Command::create($data);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_p']);

        $licence = new LicenceEntity($organisation, new Entity\System\RefData());
        $licence->setId(self::LIC_ID);
        $this->expectedLicenceCacheClearSideEffect(self::LIC_ID);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);

        $this->repoMap['Person']->shouldReceive('save')->once()->andReturnUsing(
            function (\Dvsa\Olcs\Api\Entity\Person\Person $person) use ($data) {
                $this->assertSame($data['forename'], $person->getForename());
                $this->assertSame($data['familyName'], $person->getFamilyName());
                $this->assertSame($this->refData[$data['title']], $person->getTitle());
                $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                $this->assertSame($data['otherName'], $person->getOtherName());

                $person->setId(self::PERSON_ID);
            }
        );

        $this->repoMap['OrganisationPerson']->shouldReceive('save')->once()->andReturnUsing(
            function (Entity\Organisation\OrganisationPerson $op) use ($data, $organisation) {
                $this->assertSame($organisation, $op->getOrganisation());
                $this->assertSame(self::PERSON_ID, $op->getPerson()->getId());
                $this->assertSame($data['position'], $op->getPosition());
                $op->setId(self::ORG_PERSON_ID);
            }
        );

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['OrganisationPerson created'], $response->getMessages());
        $this->assertSame(
            [
                'organisationPerson' => self::ORG_PERSON_ID,
                'person' => self::PERSON_ID,
            ],
            $response->getIds()
        );
    }
}
