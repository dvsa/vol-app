<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\ContactDetails;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * PhoneContact Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PhoneContactEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetCalculatedValues(): void
    {
        $mockType = new RefData();

        $actual = (new Entity($mockType))->jsonSerialize();
        static::assertNull($actual['contactDetails']);
    }
}
