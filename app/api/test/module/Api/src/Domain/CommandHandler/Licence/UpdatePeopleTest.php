<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdatePeople;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdatePeople::class)]
class UpdatePeopleTest extends AbstractCommandHandlerTestCase
{
    public const PERSON_ID = 7001;
    public const VERSION = 666;

    /** @var  UpdatePeople */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockPersonRepo;
    /** @var  m\MockInterface */
    private $mockOrgPersonRepo;
    /** @var  m\MockInterface */
    private $mockLicRepo;

    public function setUp(): void
    {
        $this->sut = new UpdatePeople();

        $this->mockPersonRepo = $this->mockRepo('Person', Repository\Person::class);
        $this->mockOrgPersonRepo = $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);
        $this->mockLicRepo = $this->mockRepo('Licence', Repository\Licence::class);

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
            'id' => 52,
            'person' => self::PERSON_ID,
            'version' => self::VERSION,
            'forename' => 'Foo',
            'familyName' => 'Bar',
            'title' => 'title_mr',
            'birthDate' => '1966-05-21',
            'otherName' => 'unit_OtherName',
            'position' => 'unit_Position',
        ];
        $command = TransferCmd\Licence\UpdatePeople::create($data);

        //  mock licence
        $organisation = new Entity\Organisation\Organisation();
        $organisation->setType($this->refData['org_t_p']);

        $licence = new Entity\Licence\Licence($organisation, new Entity\System\RefData());

        $this->mockLicRepo->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($licence);

        //  mock person
        $person = new Entity\Person\Person();
        $person->setId(self::PERSON_ID);
        $person->setForename('FRED');

        $this->mockPersonRepo
            ->shouldReceive('fetchById')
            ->with(self::PERSON_ID, \Doctrine\ORM\Query::HYDRATE_OBJECT, self::VERSION)
            ->once()
            ->andReturn($person);

        $this->mockPersonRepo
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\Person\Person $person) use ($data) {
                    $this->assertSame($data['forename'], $person->getForename());
                    $this->assertSame($data['familyName'], $person->getFamilyName());
                    $this->assertSame($this->refData[$data['title']], $person->getTitle());
                    $this->assertEquals(new \DateTime($data['birthDate']), $person->getBirthDate());
                    $this->assertSame($data['otherName'], $person->getOtherName());
                }
            );

        //  mock organisation person
        $orgPerson1 = new Entity\Organisation\OrganisationPerson();

        $this->mockOrgPersonRepo
            ->shouldReceive('fetchByOrgAndPerson')
            ->with($organisation, $person)
            ->once()
            ->andReturn([$orgPerson1, clone $orgPerson1]);

        $this->mockOrgPersonRepo
            ->shouldReceive('save')
            ->twice()
            ->andReturnUsing(
                function (Entity\Organisation\OrganisationPerson $orgPerson) use ($data) {
                    static::assertSame($data['position'], $orgPerson->getPosition());
                }
            );

        $actual = $this->sut->handleCommand($command);

        $this->assertSame(['Person ID ' . self::PERSON_ID . ' updated'], $actual->getMessages());
        $this->assertSame(['person' => self::PERSON_ID], $actual->getIds());
    }
}
