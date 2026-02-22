<?php

declare(strict_types=1);

/**
 * Delete Licence History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\DeleteOtherLicence;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Transfer\Command\OtherLicence\DeleteOtherLicence as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DeleteOtherLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteOtherLicence();
        $this->mockRepo('OtherLicence', OtherLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = $this->getCommand();

        $otherLicence = m::mock(OtherLicenceEntity::class)->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(1)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCmd::class,
            ['id' => 1, 'section' => 'licenceHistory'],
            new Result()
        );

        $this->repoMap['OtherLicence']->shouldReceive('fetchById')
            ->andReturn($otherLicence)
            ->times(4)
            ->shouldReceive('delete')
            ->with($otherLicence)
            ->times(3)
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'otherLicence1' => 1,
                'otherLicence2' => 2,
                'otherLicence3' => 3
            ],
            'messages' => [
                'Other licence removed',
                'Other licence removed',
                'Other licence removed'
            ]
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getCommand(): mixed
    {
        $data = [
            'ids' => [1, 2, 3]
        ];

        return Cmd::create($data);
    }
}
