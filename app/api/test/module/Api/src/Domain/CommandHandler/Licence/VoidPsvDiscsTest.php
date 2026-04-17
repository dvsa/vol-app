<?php

declare(strict_types=1);

/**
 * Void Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\VoidPsvDiscs;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Transfer\Command\Licence\VoidPsvDiscs as Cmd;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc as PsvDiscEntity;

/**
 * Void Psv Discs Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VoidPsvDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new VoidPsvDiscs();
        $this->mockRepo('PsvDisc', PsvDisc::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [
                111, 222
            ],
            'licence' => 123
        ];

        $command = Cmd::create($data);

        $psvDisc1 = m::mock(PsvDiscEntity::class)->makePartial();
        $psvDisc1->shouldReceive('cease')->once();
        $psvDisc2 = m::mock(PsvDiscEntity::class)->makePartial();
        $psvDisc2->shouldReceive('cease')->once();

        $this->repoMap['PsvDisc']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($psvDisc1)
            ->shouldReceive('fetchById')
            ->with(222)
            ->andReturn($psvDisc2)
            ->shouldReceive('save')
            ->once()
            ->with($psvDisc1)
            ->shouldReceive('save')
            ->once()
            ->with($psvDisc2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 PSV Disc(s) voided'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
