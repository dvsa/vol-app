<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OrganisationPerson;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\OrganisationPerson\Update as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson\Update::class)]
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public const ORG_ID = 9001;
    public const PERSON_ID = 8001;
    public const ORG_PERSON_ID = 7001;
    public const VERSION = 666;

    /** @var  CommandHandler\OrganisationPerson\Create */
    protected $sut;

    /** @var  Entity\Organisation\Organisation */
    private $mockOrg;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\OrganisationPerson\Update();

        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);
        $this->mockRepo('Person', Repository\Person::class);

        $this->mockOrg = m::mock(Entity\Organisation\Organisation::class)->makePartial();
        $this->mockOrg->setId(self::ORG_ID);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'TITLE',
            Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER,
        ];

        $this->references = [
            Entity\Organisation\Organisation::class => [
                self::ORG_ID => $this->mockOrg,
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(
            [
                'id' => self::ORG_PERSON_ID,
                'version' => self::VERSION,
                'position' => 'POSITION',
                'person' => [
                    'title' => 'TITLE',
                    'forename' => 'FORENAME',
                    'familyName' => 'FAMILY_NAME',
                    'otherName' => 'OTHER_NAME',
                    'birthDate' => '2015-07-24',
                ],
            ]
        );

        $organisationPerson = new Entity\Organisation\OrganisationPerson();
        $organisationPerson
            ->setId(self::ORG_PERSON_ID)
            ->setPerson(new Entity\Person\Person())
            ->setOrganisation($this->mockOrg);

        $this->repoMap['OrganisationPerson']
            ->shouldReceive('fetchUsingId')
            ->with($command, \Doctrine\ORM\Query::HYDRATE_OBJECT, self::VERSION)
            ->once()
            ->andReturn($organisationPerson);

        $this->repoMap['OrganisationPerson']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (Entity\Organisation\OrganisationPerson $saveOrganisationPerson) {
                    $this->assertSame('POSITION', $saveOrganisationPerson->getPosition());
                }
            );

        $this->repoMap['Person']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (Entity\Person\Person $person) {
                    $this->assertSame($this->refData['TITLE'], $person->getTitle());
                    $this->assertSame('FORENAME', $person->getForename());
                    $this->assertSame('FAMILY_NAME', $person->getFamilyName());
                    $this->assertSame('OTHER_NAME', $person->getOtherName());
                    $this->assertEquals(new \DateTime('2015-07-24'), $person->getBirthDate());
                }
            );

        //  check organisation name save
        $this->mockOrg->setType($this->refData[Entity\Organisation\Organisation::ORG_TYPE_SOLE_TRADER]);

        $result = new Result();
        $result->addMessage('Unit Generate Name Message');

        $this->expectedSideEffect(
            TransferCmd\Organisation\GenerateName::class,
            [
                'organisation' => self::ORG_ID,
            ],
            $result
        );

        //  call & check
        $response = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'organisationPerson'
                => self::ORG_PERSON_ID,
            ],
            $response->getIds()
        );
        $this->assertSame(
            [
                'Unit Generate Name Message',
                'OrganisationPerson updated',
            ],
            $response->getMessages()
        );
    }
}
