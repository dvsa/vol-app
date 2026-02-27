<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\TrafficArea;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as Entity;
use Dvsa\Olcs\Api\Entity\Publication\Recipient as RecipientEntity;
use Mockery as m;

/**
 * TrafficArea Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TrafficAreaEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public const PUB_RECIPIENT_NAME1 = 'name 1';
    public const PUB_RECIPIENT_NAME2 = 'name 2';
    public const PUB_RECIPIENT_NAME3 = 'name 3';
    public const PUB_RECIPIENT_NAME4 = 'name 4';
    public const PUB_RECIPIENT_NAME5 = 'name 5';
    public const PUB_RECIPIENT_NAME6 = 'name 6';
    public const PUB_RECIPIENT_NAME7 = 'name 7';
    public const PUB_RECIPIENT_NAME8 = 'name 8';
    public const PUB_RECIPIENT_EMAIL1 = 'email1@foo.bar';
    public const PUB_RECIPIENT_EMAIL2 = 'email2@foo.bar';
    public const PUB_RECIPIENT_EMAIL3 = 'email3@foo.bar';
    public const PUB_RECIPIENT_EMAIL4 = 'email4@foo.bar';
    public const PUB_RECIPIENT_EMAIL5 = 'email5@foo.bar';
    public const PUB_RECIPIENT_EMAIL6 = 'email6@foo.bar';
    public const PUB_RECIPIENT_EMAIL7 = 'email7@foo.bar';
    public const PUB_RECIPIENT_EMAIL8 = 'email8@foo.bar';

    /**
     * Test getPublicationRecipients
     *
     *
     * @param $pubType
     * @param $isPolice
     * @param $policeTimes
     * @param $nonPoliceTimes
     * @param $expectedRecipients
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('publicationRecipientsProvider')]
    public function testGetPublicationRecipients(
        mixed $pubType,
        mixed $isPolice,
        mixed $adPoliceTimes,
        mixed $adNonPoliceTimes,
        mixed $npPoliceTimes,
        mixed $npNonPoliceTimes,
        mixed $expectedRecipients
    ): void {
        $entity = new Entity();

        $recipient1 = m::mock(RecipientEntity::class);
        $recipient1->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL1);
        $recipient1->shouldReceive('getContactName')->times($adNonPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME1);
        $recipient1->shouldReceive('getIsPolice')->once()->andReturn('N');
        $recipient1->shouldReceive('getSendAppDecision')->andReturn('Y');
        $recipient1->shouldReceive('getSendNoticesProcs')->andReturn('N');

        $recipient2 = m::mock(RecipientEntity::class);
        $recipient2->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL2);
        $recipient2->shouldReceive('getContactName')->times($adPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME2);
        $recipient2->shouldReceive('getIsPolice')->once()->andReturn('Y');
        $recipient2->shouldReceive('getSendAppDecision')->andReturn('Y');
        $recipient2->shouldReceive('getSendNoticesProcs')->andReturn('N');

        $recipient3 = m::mock(RecipientEntity::class);
        $recipient3->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL3);
        $recipient3->shouldReceive('getContactName')->times($adNonPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME3);
        $recipient3->shouldReceive('getIsPolice')->once()->andReturn('N');
        $recipient3->shouldReceive('getSendAppDecision')->andReturn('Y');
        $recipient3->shouldReceive('getSendNoticesProcs')->andReturn('N');

        $recipient4 = m::mock(RecipientEntity::class);
        $recipient4->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL4);
        $recipient4->shouldReceive('getContactName')->times($adPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME4);
        $recipient4->shouldReceive('getIsPolice')->once()->andReturn('Y');
        $recipient4->shouldReceive('getSendAppDecision')->andReturn('Y');
        $recipient4->shouldReceive('getSendNoticesProcs')->andReturn('N');

        $recipient5 = m::mock(RecipientEntity::class);
        $recipient5->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL5);
        $recipient5->shouldReceive('getContactName')->times($npNonPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME5);
        $recipient5->shouldReceive('getIsPolice')->once()->andReturn('N');
        $recipient5->shouldReceive('getSendAppDecision')->andReturn('N');
        $recipient5->shouldReceive('getSendNoticesProcs')->andReturn('Y');

        $recipient6 = m::mock(RecipientEntity::class);
        $recipient6->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL6);
        $recipient6->shouldReceive('getContactName')->times($npPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME6);
        $recipient6->shouldReceive('getIsPolice')->once()->andReturn('Y');
        $recipient6->shouldReceive('getSendAppDecision')->andReturn('N');
        $recipient6->shouldReceive('getSendNoticesProcs')->andReturn('Y');

        $recipient7 = m::mock(RecipientEntity::class);
        $recipient7->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL7);
        $recipient7->shouldReceive('getContactName')->times($npNonPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME7);
        $recipient7->shouldReceive('getIsPolice')->once()->andReturn('N');
        $recipient7->shouldReceive('getSendAppDecision')->andReturn('N');
        $recipient7->shouldReceive('getSendNoticesProcs')->andReturn('Y');

        $recipient8 = m::mock(RecipientEntity::class);
        $recipient8->shouldReceive('getEmailAddress')->once()->andReturn(self::PUB_RECIPIENT_EMAIL8);
        $recipient8->shouldReceive('getContactName')->times($npPoliceTimes)->andReturn(self::PUB_RECIPIENT_NAME8);
        $recipient8->shouldReceive('getIsPolice')->once()->andReturn('Y');
        $recipient8->shouldReceive('getSendAppDecision')->andReturn('N');
        $recipient8->shouldReceive('getSendNoticesProcs')->andReturn('Y');

        $recipientArray = [
            $recipient1,
            $recipient2,
            $recipient3,
            $recipient4,
            $recipient5,
            $recipient6,
            $recipient7,
            $recipient8
        ];

        $recipientCollection = new ArrayCollection($recipientArray);
        $entity->setRecipients($recipientCollection);

        $this->assertEquals($expectedRecipients, $entity->getPublicationRecipients($isPolice, $pubType));
    }

    /**
     * Data provider for testGetPublicationRecipients
     *
     * @return array
     */
    public static function publicationRecipientsProvider(): array
    {
        $adPoliceRecipients = [
            self::PUB_RECIPIENT_EMAIL2 => self::PUB_RECIPIENT_NAME2,
            self::PUB_RECIPIENT_EMAIL4 => self::PUB_RECIPIENT_NAME4
        ];

        $adNonPoliceRecipients = [
            self::PUB_RECIPIENT_EMAIL1 => self::PUB_RECIPIENT_NAME1,
            self::PUB_RECIPIENT_EMAIL3 => self::PUB_RECIPIENT_NAME3
        ];

        $npPoliceRecipients = [
            self::PUB_RECIPIENT_EMAIL6 => self::PUB_RECIPIENT_NAME6,
            self::PUB_RECIPIENT_EMAIL8 => self::PUB_RECIPIENT_NAME8
        ];

        $npNonPoliceRecipients = [
            self::PUB_RECIPIENT_EMAIL5 => self::PUB_RECIPIENT_NAME5,
            self::PUB_RECIPIENT_EMAIL7 => self::PUB_RECIPIENT_NAME7
        ];

        return [
            ['A&D', 'Y', 1, 0, 0, 0, $adPoliceRecipients],
            ['A&D', 'N', 0, 1, 0, 0, $adNonPoliceRecipients],
            ['N&P', 'Y', 0, 0, 1, 0, $npPoliceRecipients],
            ['N&P', 'N', 0, 0, 0, 1, $npNonPoliceRecipients]
        ];
    }
}
