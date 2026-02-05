<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\CommunityLic;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLicSuspension as Entity;
use Mockery as m;

/**
 * CommunityLicSuspension Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CommunityLicSuspensionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testUpdateCommunityLicSuspension(mixed $endDate, mixed $expected): void
    {
        $communityLic = 1;
        $startDate = '2014-01-01';
        $sut = m::mock(Entity::class)->makePartial();
        $sut->updateCommunityLicSuspension($communityLic, $startDate, $endDate);
        $this->assertEquals($communityLic, $sut->getCommunityLic());
        $this->assertEquals(new \DateTime($startDate), $sut->getStartDate());
        $this->assertEquals($expected, $sut->getEndDate());
    }

    public static function dataProvider(): array
    {
        return [
            ['2016-01-01', new \DateTime('2016-01-01')],
            [null, null]
        ];
    }
}
