<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Disqualification;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification\Create as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Disqualification\Create as Command;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * CreateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Disqualification', \Dvsa\Olcs\Api\Domain\Repository\Disqualification::class);
        $this->mockRepo('Person', \Dvsa\Olcs\Api\Domain\Repository\Person::class);
        $this->mockRepo('ContactDetails', \Dvsa\Olcs\Api\Domain\Repository\ContactDetails::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS];

        $this->references = [
            Organisation::class => [
                154 => m::mock(Organisation::class)
            ],
            Person::class => [
                43 => m::mock(Person::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandOrganisation(): void
    {
        $data = [
            'organisation' => 154,
            'notes' => 'NOTES',
            'startDate' => '2015-08-07',
            'isDisqualified' => 'Y',
            'period' => 12,
        ];
        $command = Command::create($data);

        $this->repoMap['Disqualification']->shouldReceive('save')->once()->andReturnUsing(
            function (Disqualification $saveDisqualification) use ($data) {
                $saveDisqualification->setId(154);
                $this->assertSame(
                    $this->references[Organisation::class][154],
                    $saveDisqualification->getOrganisation()
                );
                $this->assertSame($data['notes'], $saveDisqualification->getNotes());
                $this->assertEquals(new \DateTime($data['startDate']), $saveDisqualification->getStartDate());
                $this->assertSame($data['isDisqualified'], $saveDisqualification->getIsDisqualified());
                $this->assertSame($data['period'], $saveDisqualification->getPeriod());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Disqualification created'], $result->getMessages());
        $this->assertEquals(['disqualification' => 154], $result->getIds());
    }

    public function testHandleCommandOfficerWithContactDetails(): void
    {
        $data = [
            'person' => 43,
            'notes' => 'NOTES',
            'startDate' => '2015-08-07',
            'isDisqualified' => 'Y',
            'period' => 12,
        ];
        $command = Command::create($data);

        $this->repoMap['Disqualification']->shouldReceive('save')->once()->andReturnUsing(
            function (Disqualification $saveDisqualification) use ($data) {
                $saveDisqualification->setId(154);
                $this->assertSame($this->references[Person::class][43], $saveDisqualification->getPerson());
                $this->assertSame($data['notes'], $saveDisqualification->getNotes());
                $this->assertEquals(new \DateTime($data['startDate']), $saveDisqualification->getStartDate());
                $this->assertSame($data['isDisqualified'], $saveDisqualification->getIsDisqualified());
                $this->assertSame($data['period'], $saveDisqualification->getPeriod());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Disqualification created'], $result->getMessages());
        $this->assertEquals(['disqualification' => 154], $result->getIds());
    }
}
