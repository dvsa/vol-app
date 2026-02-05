<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\ContactDetails;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as Entity;

/**
 * Country Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CountryEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsMorocco')]
    public function testIsMorocco(mixed $countryId, mixed $expectedIsMorocco): void
    {
        $entity = new Entity();
        $entity->setId($countryId);

        $this->assertEquals(
            $expectedIsMorocco,
            $entity->isMorocco()
        );
    }

    public static function dpIsMorocco(): array
    {
        return [
            [Entity::ID_NORWAY, false],
            [Entity::ID_BELARUS, false],
            [Entity::ID_MOROCCO, true],
        ];
    }
}
