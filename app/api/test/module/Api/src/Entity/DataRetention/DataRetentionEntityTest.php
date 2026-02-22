<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\DataRetention;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention as Entity;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime as CustomDateTime;

/**
 * DataRetention Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class DataRetentionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testMarkForDelete(): void
    {
        $dataRetentionEntity = new Entity();
        $dataRetentionEntity->setActionConfirmation(false);

        $expectedEntity = new Entity();
        $expectedEntity->setActionConfirmation(true);
        $expectedEntity->setActionedDate(new CustomDateTime());

        $this->assertEquals(
            $dataRetentionEntity->markForDelete(),
            $expectedEntity
        );
    }

    public function testMarkForReview(): void
    {
        $dataRetentionEntity = new Entity();
        $dataRetentionEntity->setActionConfirmation(true);
        $dataRetentionEntity->setActionedDate(new \DateTime('now'));

        $expectedEntity = new Entity();
        $expectedEntity->setActionConfirmation(false);
        $expectedEntity->setActionedDate(null);

        $this->assertEquals(
            $dataRetentionEntity->markForReview(),
            $expectedEntity
        );
    }

    public function testMarkForDelay(): void
    {
        $dateString = '2060-01-01';
        $date = new \DateTime($dateString);

        $dataRetentionEntity = new Entity();
        $dataRetentionEntity->setActionConfirmation(true);
        $dataRetentionEntity->setActionedDate(new \DateTime('now'));

        $expectedEntity = new Entity();
        $expectedEntity->setActionConfirmation(false);
        $expectedEntity->setActionedDate(null);
        $expectedEntity->setNextReviewDate($date);

        $this->assertEquals(
            $dataRetentionEntity->markForDelay($dateString),
            $expectedEntity
        );
    }
}
