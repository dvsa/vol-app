<?php

declare(strict_types=1);

/**
 * Create Person Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Person;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Person\Create as CreatePerson;
use Dvsa\Olcs\Api\Domain\Repository\Person as PersonRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Person\Create as Cmd;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;

/**
 * Create Person Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePerson();
        $this->mockRepo('Person', PersonRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr'
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'firstName'  => 'fname',
                'lastName'   => 'lname',
                'title'      => 'title_mr',
                'birthDate'  => '2015-01-01',
                'birthPlace' => 'bplace'
            ]
        );

        $person = null;

        $this->repoMap['Person']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PersonEntity::class))
            ->andReturnUsing(
                function (PersonEntity $pers) use (&$person) {
                    $pers->setId(111);
                    $person = $pers;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'person' => 111
            ],
            'messages' => [
                'Person ID 111 created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('fname', $person->getForename());
        $this->assertEquals('lname', $person->getFamilyName());
        $this->assertEquals(new \DateTime('2015-01-01'), $person->getBirthDate());
        $this->assertEquals('bplace', $person->getBirthPlace());
    }
}
