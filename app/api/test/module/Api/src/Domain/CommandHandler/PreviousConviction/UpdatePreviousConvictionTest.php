<?php

declare(strict_types=1);

/**
 * Update Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PreviousConviction;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction\UpdatePreviousConviction;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\UpdatePreviousConviction as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as PrevConvictionEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;

/**
 * Update Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UpdatePreviousConvictionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdatePreviousConviction();
        $this->mockRepo('PreviousConviction', PreviousConviction::class);

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
        $data = [
            'id' => 123,
            'version' => 2,
            'title' => 'title_mr',
            'forename' => 'Test',
            'familyName' => 'Person',
            'convictionDate' => '2015-05-04',
            'categoryText' => 'text',
            'notes' => 'notes',
            'courtFpn' => 'court',
            'penalty' => 'penalty'
        ];

        $command = Cmd::create($data);

        $this->repoMap['PreviousConviction']
            ->shouldReceive('fetchById')
            ->with(123, \Doctrine\Orm\Query::HYDRATE_OBJECT, 2)
            ->andReturn(
                m::mock(PrevConvictionEntity::class)
                ->shouldReceive('setTitle')
                //->with('title_mr')
                ->shouldReceive('setForename')
                ->with('Test')
                ->shouldReceive('setFamilyName')
                ->with('Person')
                ->shouldReceive('setConvictionDate')
                ->with(m::type('DateTime'))
                ->shouldReceive('setCategoryText')
                ->with('text')
                ->shouldReceive('setNotes')
                ->with('notes')
                ->shouldReceive('setCourtFpn')
                ->with('court')
                ->shouldReceive('setPenalty')
                ->with('penalty')
                ->shouldReceive('getApplication')
                ->andReturn(
                    m::mock(Application::class)
                    ->shouldReceive('getId')
                    ->andReturn(50)
                    ->getMock()
                )
                ->shouldReceive('getId')
                ->andReturn(123)
                ->getMock()
            )
            ->shouldReceive('save')
            ->once();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 50, 'section' => 'convictionsPenalties'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'previousConviction' => 123
            ],
            'messages' => [
                'Previous conviction updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
