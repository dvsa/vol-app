<?php

declare(strict_types=1);

/**
 * DeletePartner Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Repository\Partner as PartnerRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\User\DeletePartner;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Transfer\Command\User\DeletePartner as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Class DeletePartnerTest
 */
class DeletePartnerTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeletePartner();
        $this->mockRepo('Partner', PartnerRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1
        ];

        $command = Cmd::create($data);

        $contactDetailsEntity = m::mock(ContactDetails::class)->makePartial();
        $contactDetailsEntity->setId(1);

        $this->repoMap['Partner']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->andReturn($contactDetailsEntity)
            ->shouldReceive('delete')
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'partner' => 1
            ],
            'messages' => [
                'Partner deleted successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
