<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterChoice\Create as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterChoice as LetterChoiceRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as LetterChoiceEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Create as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterChoice', LetterChoiceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = ['lcat_gv'];

        parent::initReferences();
    }

    private function createAndCapture(array $data): LetterChoiceEntity
    {
        $saved = null;
        $this->repoMap['LetterChoice']->shouldReceive('save')
            ->with(m::type(LetterChoiceEntity::class))
            ->once()
            ->andReturnUsing(function (LetterChoiceEntity $choice) use (&$saved) {
                $saved = $choice;
                $choice->setId(9);
            });

        $this->sut->handleCommand(Cmd::create($data + ['choiceKey' => 'time-limited-interims', 'label' => 'Interims']));

        return $saved;
    }

    public function testGoodsOrPsvIsSet(): void
    {
        $choice = $this->createAndCapture(['goodsOrPsv' => 'lcat_gv']);

        $this->assertSame($this->refData['lcat_gv'], $choice->getGoodsOrPsv());
    }

    public function testNoGoodsOrPsvMeansBoth(): void
    {
        $choice = $this->createAndCapture([]);

        $this->assertNull($choice->getGoodsOrPsv());
    }
}
