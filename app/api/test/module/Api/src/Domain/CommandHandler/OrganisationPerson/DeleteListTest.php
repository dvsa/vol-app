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
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\OrganisationPerson\DeleteList::class)]
class DeleteListTest extends AbstractCommandHandlerTestCase
{
    public const ORG_ID = 6001;

    public const ORG_PERSON_ID = 9001;
    public const ORG_PERSON_2_ID = 9002;

    public const PERSON_ID = 7001;
    public const PERSON_2_ID = 7002;

    /** @var CommandHandler\OrganisationPerson\DeleteList  */
    protected $sut;

    /** @var  Entity\Organisation\Organisation */
    private $mockOrg;

    public function setUp(): void
    {
        $this->sut = new CommandHandler\OrganisationPerson\DeleteList();

        $this->mockRepo('OrganisationPerson', Repository\OrganisationPerson::class);
        $this->mockRepo('Person', Repository\Person::class);
        $this->mockRepo('ApplicationOrganisationPerson', Repository\ApplicationOrganisationPerson::class);

        $this->mockOrg = m::mock(Entity\Organisation\Organisation::class)->makePartial();
        $this->mockOrg->setId(self::ORG_ID);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
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
        $person1 = (new Entity\Person\Person())->setId(self::PERSON_ID);
        $op1 = m::mock();
        $op1->shouldReceive('getPerson')->with()->atLeast(1)->andReturn($person1)
            ->shouldReceive('getOrganisation')->with()->atLeast(1)->andReturn($this->mockOrg);

        $person2 = (new Entity\Person\Person())->setId(self::PERSON_2_ID);
        $op2 = m::mock();
        $op2->shouldReceive('getPerson')->with()->atLeast(1)->andReturn($person2)
            ->shouldReceive('getOrganisation')->with()->atLeast(1)->andReturn($this->mockOrg);

        $this->repoMap['OrganisationPerson']
            ->shouldReceive('fetchById')->with(self::ORG_PERSON_ID)->once()->andReturn($op1)
            ->shouldReceive('delete')->with($op1)->once()->andReturn()
            ->shouldReceive('fetchListForPerson')->with(self::PERSON_ID)->once()->andReturn([]);

        $this->repoMap['Person']->shouldReceive('delete')->with($person1)->once();
        $this->repoMap['ApplicationOrganisationPerson']->shouldReceive('deleteForPerson')->with($person1)->once();

        $this->repoMap['OrganisationPerson']
            ->shouldReceive('fetchById')->with(self::ORG_PERSON_2_ID)->once()->andReturn($op2)
            ->shouldReceive('delete')->with($op2)->once()->andReturn()
            ->shouldReceive('fetchListForPerson')->with(self::PERSON_2_ID)->once()->andReturn(['FOO']);

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
        $response = $this->sut->handleCommand(
            TransferCmd\OrganisationPerson\DeleteList::create(
                ['ids' => [self::ORG_PERSON_ID, self::ORG_PERSON_2_ID]]
            )
        );

        $this->assertSame(
            [
                'OrganisationPerson ID ' . self::ORG_PERSON_ID . ' deleted',
                'Person ID ' . self::PERSON_ID . ' deleted',
                'OrganisationPerson ID ' . self::ORG_PERSON_2_ID . ' deleted',
                'Unit Generate Name Message',
            ],
            $response->getMessages()
        );
    }
}
