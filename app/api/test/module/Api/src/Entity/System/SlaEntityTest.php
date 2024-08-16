<?php

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

    /**
     * @dataProvider dpTestAppliesTo
     */
    public function testAppliesTo($date, $effFrom, $effTo, $expect)
    {
        $sut = (new Entity())
            ->setEffectiveFrom($effFrom)
            ->setEffectiveTo($effTo);

        static::assertEquals($expect, $sut->appliesTo($date));
    }

    public function dpTestAppliesTo()
    {
        return [
            [
                'date' => new \DateTime('2016-05-04 01:00:00'),
                'effectiveFrom' => new \DateTime('2016-05-04 02:00:00'),
                'effectiveTo' => null,
                'expect' => false,
            ],
            [
                'date' => new \DateTime('2016-05-04 01:00:00'),
                'effectiveFrom' => null,
                'effectiveTo' => new \DateTime('2016-05-04 00:00:00'),
                'expect' => false,
            ],
            [
                'date' => new \DateTime('2016-05-04 00:01:00'),
                'effectiveFrom' => new \DateTime('2016-05-04 00:00:59'),
                'effectiveTo' => new \DateTime('2016-05-04 00:01:01'),
                'expect' => true,
            ],
            [
                'date' => new \DateTime(),
                'effectiveFrom' => null,
                'effectiveTo' => null,
                'expect' => true,
            ],
        ];
    }
}
