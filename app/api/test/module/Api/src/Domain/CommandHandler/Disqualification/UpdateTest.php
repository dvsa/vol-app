<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Disqualification;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification\Update as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Disqualification\Update as Command;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification;

/**
 * UpdateTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Disqualification', \Dvsa\Olcs\Api\Domain\Repository\Disqualification::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 154,
            'version' => 43,
            'notes' => 'NOTES',
            'startDate' => '2015-08-07',
            'isDisqualified' => 'Y',
            'period' => 12,
        ];
        $command = Command::create($data);

        $disqualification = new Disqualification(m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class));
        $disqualification->setId(154);

        $this->repoMap['Disqualification']->shouldReceive('fetchUsingId')->with($command, Query::HYDRATE_OBJECT, 43)
            ->once()->andReturn($disqualification);

        $this->repoMap['Disqualification']->shouldReceive('save')->once()->andReturnUsing(
            function (Disqualification $saveDisqualification) use ($data) {
                $this->assertSame($data['notes'], $saveDisqualification->getNotes());
                $this->assertEquals(new \DateTime($data['startDate']), $saveDisqualification->getStartDate());
                $this->assertSame($data['isDisqualified'], $saveDisqualification->getIsDisqualified());
                $this->assertSame($data['period'], $saveDisqualification->getPeriod());
            }
        );

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Disqualification updated'], $result->getMessages());
        $this->assertEquals(['disqualification' => 154], $result->getIds());
    }
}
