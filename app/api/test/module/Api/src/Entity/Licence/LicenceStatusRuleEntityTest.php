<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule as Entity;
use Mockery as m;

/**
 * LicenceStatusRule Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceStatusRuleEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var Entity
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestIsQueued')]
    public function testIsQueued(mixed $startDate, mixed $startProcessingDate, mixed $expected): void
    {
        $sut = new Entity(
            m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );

        $sut->setStartDate($startDate);
        $sut->setStartProcessedDate($startProcessingDate);

        $this->assertSame($expected, $sut->isQueued());
    }

    public static function dataProviderTestIsQueued(): array
    {
        return [
            // startDate, startProcessingDate, expected
            [null, null, false],
            [null, new \DateTime(), false],
            [new \DateTime(), null, true],
            [new \DateTime(), new \DateTime(), false],
        ];
    }
}
