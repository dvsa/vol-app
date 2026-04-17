<?php

declare(strict_types=1);

/**
 * Create Recipient Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\CreateRecipient;
use Dvsa\Olcs\Api\Domain\Repository\Recipient;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Publication\Recipient as RecipientEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\Publication\CreateRecipient as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create Recipient Test
 */
class CreateRecipientTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateRecipient();
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
            'isObjector' => 'Y',
            'contactName' => 'test me',
            'emailAddress' => 'test@test.me',
            'sendAppDecision' => 'Y',
            'sendNoticesProcs' => 'Y',
            'trafficAreas' => ['B', 'C'],
            'isPolice' => 'Y',
        ];

        /** @var RecipientEntity $savedRecipient */
        $savedRecipient = null;

        $command = Cmd::create($data);

        $this->repoMap['Recipient']->shouldReceive('save')
            ->once()
            ->with(m::type(RecipientEntity::class))
            ->andReturnUsing(
                function (RecipientEntity $recipient) use (&$savedRecipient) {
                    $recipient->setId(111);
                    $savedRecipient = $recipient;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'recipient' => 111,
            ],
            'messages' => [
                'Recipient created successfully'
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

    public function testHandleCommandWithInvalidSubscriptionDetails(): void
    {
        $this->expectException(Exception\ValidationException::class);

        $data = [
            'sendAppDecision' => 'N',
            'sendNoticesProcs' => 'N',
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
