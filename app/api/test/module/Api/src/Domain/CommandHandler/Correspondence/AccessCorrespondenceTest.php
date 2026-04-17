<?php

declare(strict_types=1);

/**
 * AccessCorrespondence.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Correspondence;

use Dvsa\Olcs\Api\Domain\CommandHandler\Correspondence\AccessCorrespondence;
use Dvsa\Olcs\Api\Domain\Repository\GracePeriod as CorrespondenceRepo;
use Dvsa\Olcs\Api\Entity\Organisation\CorrespondenceInbox;
use Dvsa\Olcs\Transfer\Command\Correspondence\AccessCorrespondence as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class AccessCorrespondenceTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Correspondence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class AccessCorrespondenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AccessCorrespondence();
        $this->mockRepo('Correspondence', CorrespondenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'startDate' => '2015-01-01',
            'endDate' => '2015-01-02',
            'description' => 'description'
        ];

        $command = Cmd::create($data);

        $this->repoMap['Correspondence']
            ->shouldReceive('fetchById')
            ->once()
            ->andReturn(
                m::mock(CorrespondenceInbox::class)
                    ->shouldReceive('getId')
                    ->once()
                    ->shouldReceive('setAccessed')
                    ->with('Y')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('save')
            ->once()
            ->with(m::type(CorrespondenceInbox::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'correspondence' => null
            ],
            'messages' => [
                'Correspondence updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
