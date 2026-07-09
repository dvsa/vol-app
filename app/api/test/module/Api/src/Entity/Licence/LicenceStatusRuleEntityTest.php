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
final class LicenceStatusRuleEntityTest extends EntityTester
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

    public static function dataProviderTestIsQueued(): \Iterator
    {
        // startDate, startProcessingDate, expected
        yield [null, null, false];
        yield [null, new \DateTime(), false];
        yield [new \DateTime(), null, true];
        yield [new \DateTime(), new \DateTime(), false];
    }
}
