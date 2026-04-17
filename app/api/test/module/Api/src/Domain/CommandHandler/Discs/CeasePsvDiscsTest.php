<?php

declare(strict_types=1);

/**
 * CeasePsvDiscsTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as DiscRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\CeasePsvDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs as Cmd;

/**
 * Class CeasePsvDiscsTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CeasePsvDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CeasePsvDiscs();
        $this->mockRepo('PsvDisc', DiscRepo::class);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['licence' => 1]);
        $this->repoMap['PsvDisc']
            ->shouldReceive('ceaseDiscsForLicence')
            ->with(1)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Discs ceased'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
