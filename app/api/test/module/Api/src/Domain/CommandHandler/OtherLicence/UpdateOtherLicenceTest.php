<?php

declare(strict_types=1);

/**
 * Update Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\OtherLicence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\OtherLicence\UpdateOtherLicence;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as OtherLicenceRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\OtherLicence\UpdateOtherLicence as Cmd;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as OtherLicenceEntity;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;

/**
 * Update Other Licence Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UpdateOtherLicenceTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateOtherLicence();
        $this->mockRepo('OtherLicence', OtherLicenceRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = $this->getCommand();

        $otherLicence = $this->getOtherLicence()
            ->shouldReceive('updateOtherLicence')
            ->with('123', 'foo', 'Y', '01/01/2015', '2', '01/01/2014')
            ->once()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(1)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['OtherLicence']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($otherLicence)
            ->once()
            ->shouldReceive('save')
            ->with($otherLicence)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            UpdateApplicationCompletionCmd::class,
            ['id' => 1, 'section' => 'licenceHistory'],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => ['Other licence record has been updated']
        ];

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getCommand(): mixed
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'licNo' => '123',
            'holderName' => 'foo',
            'willSurrender' => 'Y',
            'disqualificationDate' => '01/01/2015',
            'disqualificationLength' => '2',
            'purchaseDate' => '01/01/2014'
        ];

        return Cmd::create($data);
    }

    protected function getOtherLicence(): m\MockInterface
    {
        return m::mock(OtherLicenceEntity::class)->makePartial();
    }
}
