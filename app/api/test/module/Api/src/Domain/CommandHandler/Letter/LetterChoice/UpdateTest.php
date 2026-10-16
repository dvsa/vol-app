<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterChoice\Update as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterChoice as LetterChoiceRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as LetterChoiceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Letter\LetterChoice\Update as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

final class UpdateTest extends AbstractCommandHandlerTestCase
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
        $this->refData = ['lcat_psv'];

        parent::initReferences();
    }

    private function update(LetterChoiceEntity $choice, array $data): void
    {
        $command = Cmd::create($data + ['id' => 9, 'version' => 1, 'choiceKey' => 'time-limited-interims', 'label' => 'Interims']);

        $this->repoMap['LetterChoice']->shouldReceive('fetchUsingId')->with($command)->once()->andReturn($choice);
        $this->repoMap['LetterChoice']->shouldReceive('save')->with($choice)->once();

        $this->sut->handleCommand($command);
    }

    public function testGoodsOrPsvIsSet(): void
    {
        $choice = new LetterChoiceEntity();
        $choice->setId(9);

        $this->update($choice, ['goodsOrPsv' => 'lcat_psv']);

        $this->assertSame($this->refData['lcat_psv'], $choice->getGoodsOrPsv());
    }

    public function testGoodsOrPsvIsClearedBackToBoth(): void
    {
        $goods = new RefData();
        $goods->setId('lcat_gv');

        $choice = new LetterChoiceEntity();
        $choice->setId(9);
        $choice->setGoodsOrPsv($goods);

        $this->update($choice, []);

        $this->assertNull($choice->getGoodsOrPsv());
    }
}
