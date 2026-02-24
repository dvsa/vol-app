<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Mockery as m;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod as Entity;

/**
 * GracePeriod Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class GracePeriodEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testIsActiveWhenActive(): void
    {
        $gracePeriod = m::mock(Entity::class)->makePartial();
        $gracePeriod->shouldReceive('getStartDate')
            ->andReturn('2015-01-01')
            ->shouldReceive('getEndDate')
            ->andReturn('2015-01-03');

        $this->assertTrue($gracePeriod->isActive('2015-01-02'));

        $this->assertEquals(['isActive' => true], $gracePeriod->getCalculatedValues());
        $this->assertEquals(['isActive' => true], $gracePeriod->getCalculatedBundleValues());
    }

    public function testIsNotActiveWhenNotActive(): void
    {
        $gracePeriod = m::mock(Entity::class)->makePartial();
        $gracePeriod->shouldReceive('getStartDate')
            ->andReturn('2015-01-01')
            ->twice()
            ->shouldReceive('getEndDate')
            ->andReturn('2015-01-03')
            ->twice();

        $this->assertFalse($gracePeriod->isActive('2011-01-02'));
    }
}
