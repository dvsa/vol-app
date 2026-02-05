<?php

declare(strict_types=1);

/**
 * DeleteTrailerTest.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Trailer;

use Dvsa\Olcs\Api\Domain\CommandHandler\Trailer\DeleteTrailer;
use Dvsa\Olcs\Api\Domain\Repository\Trailer as TrailerRepo;
use Dvsa\Olcs\Api\Entity\Licence\Trailer;
use Dvsa\Olcs\Transfer\Command\Trailer\DeleteTrailer as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class DeleteTrailerTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Trailer
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class DeleteTrailerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteTrailer();
        $this->mockRepo('Trailer', TrailerRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [1,2,3]
        ];

        $command = Cmd::create($data);

        $this->repoMap['Trailer']
            ->shouldReceive('fetchById')
            ->times(3)
            ->andReturn(m::mock(Trailer::class))
            ->shouldReceive('delete')
            ->times(3);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'trailer1' => 1,
                'trailer2' => 2,
                'trailer3' => 3
            ],
            'messages' => [
                'Trailer removed',
                'Trailer removed',
                'Trailer removed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
