<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OrganisationPerson;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson\Create::class)]
class CreateTest extends AbstractCommandHandlerTestCase
{
    public const ORG_ID = 9001;
    public const PERSON_ID = 8001;
    public const ORG_PERSON_ID = 7001;

    /** @var  CommandHandler\OrganisationPerson\Create */
    protected $sut;

    /** @var  Entity\Organisation\Organisation */
    private $mockOrg;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\OrganisationPerson\Create();

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
        $command = TransferCmd\OrganisationPerson\Create::create(
            [
                'organisation' => self::ORG_ID,
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

        $this->repoMap['Person']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (Entity\Person\Person $person) {
                    $person->setId(self::PERSON_ID);
                    $this->assertSame($this->refData['TITLE'], $person->getTitle());
                    $this->assertSame('FORENAME', $person->getForename());
                    $this->assertSame('FAMILY_NAME', $person->getFamilyName());
                    $this->assertSame('OTHER_NAME', $person->getOtherName());
                    $this->assertEquals(new \DateTime('2015-07-24'), $person->getBirthDate());
                }
            );

        $this->repoMap['OrganisationPerson']
            ->shouldReceive('save')->once()->andReturnUsing(
                function (Entity\Organisation\OrganisationPerson $organisationPerson) {
                    $organisationPerson->setId(self::ORG_PERSON_ID);
                    $this->assertSame('POSITION', $organisationPerson->getPosition());
                    $this->assertSame(
                        $this->references[Entity\Organisation\Organisation::class][self::ORG_ID],
                        $organisationPerson->getOrganisation()
                    );
                    $this->assertSame(self::PERSON_ID, $organisationPerson->getPerson()->getId());
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
                'organisationPerson' => self::ORG_PERSON_ID,
                'person' => self::PERSON_ID,
            ],
            $response->getIds()
        );
        $this->assertSame(
            [
                'Unit Generate Name Message',
                'Organisation Person ID ' . self::ORG_PERSON_ID . ' created',
            ],
            $response->getMessages()
        );
    }
}
