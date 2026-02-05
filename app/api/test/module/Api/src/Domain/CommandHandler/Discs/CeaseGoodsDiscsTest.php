<?php

declare(strict_types=1);

/**
 * CeaseGoodsDiscsTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as DiscRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs as Cmd;

/**
 * Class CeaseGoodsDiscsTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class CeaseGoodsDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CeaseGoodsDiscs();
        $this->mockRepo('GoodsDisc', DiscRepo::class);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => 123
        ];

        $command = Cmd::create($data);

        $this->repoMap['GoodsDisc']
            ->shouldReceive('ceaseDiscsForLicence')
            ->once()
            ->with(123);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Ceased discs for licence.'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
