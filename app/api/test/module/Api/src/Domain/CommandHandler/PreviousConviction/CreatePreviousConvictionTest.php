<?php

declare(strict_types=1);

/**
 * Create Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PreviousConviction;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction\CreatePreviousConviction;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\CreatePreviousConviction as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as PrevConvictionEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;

/**
 * Create Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class CreatePreviousConvictionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePreviousConviction();
        $this->mockRepo('PreviousConviction', PreviousConviction::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'title_mr'
        ];

        $this->references = [
            Application::class => [
                50 => m::mock(Application::class)
            ],
            TransportManager::class => [
                150 => m::mock(TransportManager::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'title' => 'title_mr',
            'forename' => 'Test',
            'familyName' => 'Person',
            'convictionDate' => '2015-05-04',
            'categoryText' => 'text',
            'notes' => 'notes',
            'courtFpn' => 'court',
            'penalty' => 'penalty',
            'application' => 50,
            'transportManager' => 150,
        ];

        $command = Cmd::create($data);

        $previousConviction = null;

        $this->repoMap['PreviousConviction']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PrevConvictionEntity::class))
            ->andReturnUsing(
                function (PrevConvictionEntity $conviction) use (&$previousConviction) {
                    $conviction->setId(111);
                    $previousConviction = $conviction;
                }
            );

        $this->expectedSideEffect(
            UpdateApplicationCompletionCommand::class,
            ['id' => 50, 'section' => 'convictionsPenalties'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'previousConviction' => 111,
            ],
            'messages' => [
                'Previous conviction created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals('title_mr', $previousConviction->getTitle()->getId());
        $this->assertEquals(50, $previousConviction->getApplication()->getId());
        $this->assertEquals('Test', $previousConviction->getForename());
        $this->assertEquals('Person', $previousConviction->getFamilyName());
        $this->assertEquals(new \DateTime('2015-05-04'), $previousConviction->getConvictionDate());
        $this->assertEquals('text', $previousConviction->getCategoryText());
        $this->assertEquals('notes', $previousConviction->getNotes());
        $this->assertEquals('court', $previousConviction->getCourtFpn());
        $this->assertEquals('penalty', $previousConviction->getPenalty());
        $this->assertEquals(
            $this->references[TransportManager::class][150],
            $previousConviction->getTransportManager()
        );
    }
}
