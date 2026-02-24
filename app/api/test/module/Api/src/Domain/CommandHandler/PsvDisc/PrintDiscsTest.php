<?php

declare(strict_types=1);

/**
 * Print PSV discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\PsvDisc;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\DiscSequence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\PsvDisc\PrintDiscs;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as PsvDiscRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\PsvDisc\PrintDiscs as Cmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Print PSV discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PrintDiscs();
        $this->mockRepo('DiscSequence', DiscSequenceRepo::class);
        $this->mockRepo('PsvDisc', PsvDiscRepo::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandNoDiscsToPrint(): void
    {
        $this->expectException(ValidationException::class);

        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $maxPages = 1;
        $startNumber = 1;
        $discSequence = 2;
        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'user' => 1,
            'maxPages' => $maxPages
        ];
        $command = Cmd::create($data);

        $this->repoMap['PsvDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($licenceType, $maxPages * DiscSequence::DISCS_ON_PAGE)
            ->andReturn([])
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandDecreasing(): void
    {
        $this->expectException(ValidationException::class);
        $maxPages = 1;
        $licenceType = 'ltyp_r';
        $startNumber = 1;
        $discSequence = 2;
        $data = [
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'user' => 1,
            'maxPages' => 1
        ];
        $command = Cmd::create($data);

        $this->repoMap['PsvDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($licenceType, $maxPages * DiscSequence::DISCS_ON_PAGE)
            ->andReturn(['disc1'])
            ->once()
            ->getMock();

        $mockDiscSequence = m::mock()
            ->shouldReceive('getDiscNumber')
            ->with($licenceType)
            ->andReturn(2)
            ->once()
            ->getMock();

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchById')
            ->with($discSequence)
            ->andReturn($mockDiscSequence)
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $this->mockAuthService();
        $licenceType = 'ltyp_r';
        $maxPages = 1;
        $startNumber = 1;
        $discSequence = 2;
        $data = [
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'user' => 1,
            'maxPages' =>  $maxPages
        ];
        $command = Cmd::create($data);

        $disc = ['id' => 12, 'licence' => ['id' => 3]];

        $this->repoMap['PsvDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($licenceType, $maxPages * DiscSequence::DISCS_ON_PAGE)
            ->andReturn([$disc])
            ->once()
            ->getMock();

        $mockDiscSequence = m::mock()
            ->shouldReceive('getDiscNumber')
            ->with($licenceType)
            ->andReturn(1)
            ->once()
            ->getMock();

        $this->repoMap['DiscSequence']
            ->shouldReceive('fetchById')
            ->with($discSequence)
            ->andReturn($mockDiscSequence)
            ->once()
            ->getMock();

        $options = [
            'discs' => [12],
            'type' => 'PSV',
            'startNumber' => $startNumber,
            'user' => 1
        ];
        $params = [
            'type' => Queue::TYPE_DISC_PRINTING,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $params, new Result());

        $options = [
            'bookmarks' => [
                3 => ['NO_DISCS_PRINTED' => ['count' => 1]]
            ],
            'queries' => [
                3 => ['id' => 3]
            ],
            'user' => 1
        ];
        $params = [
            'type' => Queue::TYPE_CREATE_PSV_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $params, new Result());

        $expected = [
            'id' => [],
            'messages' => [
                'PSV discs printed'
            ]
        ];
        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function mockAuthService(): void
    {
        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);
    }
}
