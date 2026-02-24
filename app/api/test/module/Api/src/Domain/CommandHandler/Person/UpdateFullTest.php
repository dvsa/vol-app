<?php

declare(strict_types=1);

/**
 * Update Person test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Person;

use Dvsa\Olcs\Api\Domain\CommandHandler\Person\UpdateFull as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Person;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Domain\Command\Person\UpdateFull as UpdateFullCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Person test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateFullTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Person', Person::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title'
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = UpdateFullCmd::create(
            [
                'id' => 1,
                'version' => 2,
                'firstName' => 'fn',
                'lastName' => 'ln',
                'title' => 'title',
                'birthDate' => '2015-01-01',
                'birthPlace' => 'bp'
            ]
        );

        $person = new PersonEntity();
        $person->setId(1);

        $this->repoMap['Person']
            ->shouldReceive('fetchById')
            ->with(1)
            ->once()
            ->andReturn($person)
            ->shouldReceive('save')
            ->with($person)
            ->once()
            ->getMock();

        $response = $this->sut->handleCommand($command);

        $this->assertSame(['person' => 1], $response->getIds());
        $this->assertSame(['Person updated'], $response->getMessages());
        $this->assertEquals(new \DateTime('2015-01-01'), $person->getBirthDate());
        $this->assertEquals('bp', $person->getBirthPlace());
        $this->assertEquals('fn', $person->getForename());
        $this->assertEquals('ln', $person->getFamilyName());
        $this->assertEquals('title', $person->getTitle()->getId());
    }
}
