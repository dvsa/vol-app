<?php

declare(strict_types=1);

/**
 * DeleteGracePeriodTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\GracePeriod as GracePeriodRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod\DeleteGracePeriod;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod;
use Dvsa\Olcs\Transfer\Command\GracePeriod\DeleteGracePeriod as Cmd;

/**
 * Class DeleteGracePeriodTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class DeleteGracePeriodTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteGracePeriod();
        $this->mockRepo('GracePeriod', GracePeriodRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [1,2,3]
        ];

        $command = Cmd::create($data);

        $this->repoMap['GracePeriod']
            ->shouldReceive('fetchById')
            ->times(3)
            ->andReturn(m::mock(GracePeriod::class))
            ->shouldReceive('delete')
            ->times(3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'graceperiod1' => 1,
                'graceperiod2' => 2,
                'graceperiod3' => 3
            ],
            'messages' => [
                'Grace period removed',
                'Grace period removed',
                'Grace period removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
