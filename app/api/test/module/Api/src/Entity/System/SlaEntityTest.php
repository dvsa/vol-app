<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Entity\System\Sla as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * @covers Dvsa\Olcs\Api\Entity\System\Sla
 * @covers Dvsa\Olcs\Api\Entity\System\AbstractSla
 */
class SlaEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestAppliesTo')]
    public function testAppliesTo(mixed $date, mixed $effFrom, mixed $effTo, mixed $expect): void
    {
        $sut = (new Entity())
            ->setEffectiveFrom($effFrom)
            ->setEffectiveTo($effTo);

        static::assertEquals($expect, $sut->appliesTo($date));
    }

    public static function dpTestAppliesTo(): array
    {
        return [
            [
                'date' => new \DateTime('2016-05-04 01:00:00'),
                'effFrom' => new \DateTime('2016-05-04 02:00:00'),
                'effTo' => null,
                'expect' => false,
            ],
            [
                'date' => new \DateTime('2016-05-04 01:00:00'),
                'effFrom' => null,
                'effTo' => new \DateTime('2016-05-04 00:00:00'),
                'expect' => false,
            ],
            [
                'date' => new \DateTime('2016-05-04 00:01:00'),
                'effFrom' => new \DateTime('2016-05-04 00:00:59'),
                'effTo' => new \DateTime('2016-05-04 00:01:01'),
                'expect' => true,
            ],
            [
                'date' => new \DateTime(),
                'effFrom' => null,
                'effTo' => null,
                'expect' => true,
            ],
        ];
    }
}
