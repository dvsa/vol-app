<?php

declare(strict_types=1);

/**
 * Delete Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PreviousConviction;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\PreviousConviction\DeletePreviousConviction;
use Dvsa\Olcs\Transfer\Command\PreviousConviction\DeletePreviousConviction as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Application\PreviousConviction as PrevConvictionEntity;

/**
 * Delete Previous Conviction Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DeletePreviousConvictionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeletePreviousConviction();
        $this->mockRepo('PreviousConviction', PreviousConviction::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [1,2,3]
        ];

        $command = Cmd::create($data);

        $this->repoMap['PreviousConviction']
            ->shouldReceive('fetchById')
            ->times(3)
            ->andReturn(m::mock(PreviousConvictionEntity::class))
            ->shouldReceive('delete')
            ->times(3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'previousConviction1' => 1,
                'previousConviction2' => 2,
                'previousConviction3' => 3
            ],
            'messages' => [
                'Previous conviction removed',
                'Previous conviction removed',
                'Previous conviction removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
