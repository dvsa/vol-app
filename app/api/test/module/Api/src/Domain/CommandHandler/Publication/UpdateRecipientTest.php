<?php

declare(strict_types=1);

/**
 * Update Recipient Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\UpdateRecipient;
use Dvsa\Olcs\Api\Domain\Repository\Recipient;
use Dvsa\Olcs\Api\Entity\Publication\Recipient as RecipientEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\Publication\UpdateRecipient as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update Recipient Test
 */
class UpdateRecipientTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateRecipient();
        $this->mockRepo('Recipient', Recipient::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            TrafficArea::class => [
                'B' => m::mock(TrafficArea::class),
                'C' => m::mock(TrafficArea::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'version' => 1,
            'isObjector' => 'Y',
            'contactName' => 'test me',
            'emailAddress' => 'test@test.me',
            'sendAppDecision' => 'Y',
            'sendNoticesProcs' => 'Y',
            'trafficAreas' => ['B', 'C'],
            'isPolice' => 'Y',
        ];

        $command = Cmd::create($data);

        /** @var RecipientEntity $recipient */
        $recipient = m::mock(RecipientEntity::class)->makePartial();
        $recipient->setId(111);

        $this->repoMap['Recipient']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($recipient);

        /** @var RecipientEntity $savedRecipient */
        $savedRecipient = null;

        $this->repoMap['Recipient']->shouldReceive('save')
            ->once()
            ->with(m::type(RecipientEntity::class))
            ->andReturnUsing(
                function (RecipientEntity $recipient) use (&$savedRecipient) {
                    $savedRecipient = $recipient;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'recipient' => 111,
            ],
            'messages' => [
                'Recipient updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[TrafficArea::class]['B'], $savedRecipient->getTrafficAreas()[0]);
        $this->assertSame($this->references[TrafficArea::class]['C'], $savedRecipient->getTrafficAreas()[1]);
        $this->assertEquals($data['isObjector'], $savedRecipient->getIsObjector());
        $this->assertEquals($data['contactName'], $savedRecipient->getContactName());
        $this->assertEquals($data['emailAddress'], $savedRecipient->getEmailAddress());
        $this->assertEquals($data['sendAppDecision'], $savedRecipient->getSendAppDecision());
        $this->assertEquals($data['sendNoticesProcs'], $savedRecipient->getSendNoticesProcs());
        $this->assertEquals($data['isPolice'], $savedRecipient->getIsPolice());
    }
}
