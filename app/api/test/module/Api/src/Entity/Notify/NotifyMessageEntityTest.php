<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Notify;

use Dvsa\Olcs\Api\Entity\Notify\NotifyMessage as Entity;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Type;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Types\DateTimeType;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * NotifyMessage Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class NotifyMessageEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testRequestedOnSurvivesTheAppDateTimeType(): void
    {
        $entity = new Entity(new RefData('nmc_sms'), new RefData('nms_queued'), '+447700900123', new \DateTimeImmutable('2026-07-01 21:30:00', new \DateTimeZone('Europe/London')));

        // the app replaces the stock 'datetime' type with this one (module.config.php)
        if (Type::hasType('datetime')) {
            Type::overrideType('datetime', DateTimeType::class);
        } else {
            Type::addType('datetime', DateTimeType::class);
        }

        $this->assertSame(
            '2026-07-01 20:30:00',
            Type::getType('datetime')->convertToDatabaseValue($entity->getRequestedOn(), new MySQLPlatform())
        );
    }
}
