<?php

declare(strict_types=1);

/**
 * Print goods discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GoodsDisc;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\DiscSequence;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\GoodsDisc\PrintDiscs;
use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as DiscSequenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\GoodsDisc\PrintDiscs as Cmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as CreateVehicleListDocumentCommand;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * Print goods discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PrintDiscs();
        $this->mockRepo('DiscSequence', DiscSequenceRepo::class);
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandNoDiscsToPrint(): void
    {
        $this->expectException(ValidationException::class);
        $this->mockAuthService();

        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $maxResults = 1;
        $startNumber = 1;
        $discSequence = 2;
        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'user' => 1,
            'maxPages' => $maxResults
        ];
        $command = Cmd::create($data);

        $this->repoMap['GoodsDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($niFlag, $licenceType, $maxResults * DiscSequence::DISCS_ON_PAGE)
            ->andReturn([])
            ->once()
            ->getMock();

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandDecreasing(): void
    {
        $this->expectException(ValidationException::class);
        $this->mockAuthService();

        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $maxResults = 1;
        $startNumber = 1;
        $discSequence = 2;
        $data = [
            'niFlag' => $niFlag,
            'licenceType' => $licenceType,
            'startNumber' => $startNumber,
            'discSequence' => $discSequence,
            'user' => 1,
            "maxPages" => $maxResults
        ];
        $command = Cmd::create($data);

        $this->repoMap['GoodsDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($niFlag, $licenceType, $maxResults * DiscSequence::DISCS_ON_PAGE)
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
        $niFlag = 'N';
        $licenceType = 'ltyp_r';
        $maxPages = 36;
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

        $disc = ['id' => 12, 'licenceVehicle' => ['licence' => ['id' => 3]]];

        $this->repoMap['GoodsDisc']
            ->shouldReceive('fetchDiscsToPrint')
            ->with($niFlag, $licenceType, $maxPages * DiscSequence::DISCS_ON_PAGE)
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
            'type' => 'Goods',
            'startNumber' => $startNumber,
            'user' => 1
        ];
        $params = [
            'type' => Queue::TYPE_DISC_PRINTING,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $params, new Result());

        $licences = [
            3 => [
                'id' => 3,
                'type' => 'dp'
            ]
        ];
        $options = [
            'licences' => $licences,
            'user' => 1
        ];
        $params = [
            'type' => Queue::TYPE_CREATE_GOODS_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $params, new Result());

        $expected = [
            'id' => [],
            'messages' => [
                'Goods discs printed'
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
