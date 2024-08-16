<?php

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Entity\System\SystemInfoMessage as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * @covers Dvsa\Olcs\Api\Entity\System\SystemInfoMessage
 * @covers Dvsa\Olcs\Api\Entity\System\AbstractSystemInfoMessage
 */
class SystemInfoMessageEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @dataProvider dataProviderTestCalculated
     */
    public function testCalculated(Entity $entity, $expect)
    {
        $actual = $entity->getCalculatedBundleValues();

        static::assertEquals($expect, $actual);
    }

    public function dataProviderTestCalculated()
    {
        $now = time();

        return [
            //  current date in interval
            [
                'entity' => (new Entity())
                    ->setStartDate(date('Y-m-d H:i:s', $now - 30000))
                    ->setEndDate(date('Y-m-d H:i:s', $now + 30000)),
                'expect' => [
                    'isActive' => true,
                ],
            ],
            //  interval in past, internal false
            [
                'entity' => (new Entity())
                    ->setStartDate(date('Y-m-d H:i:s', $now - 2 * 30000))
                    ->setEndDate(date('Y-m-d H:i:s', $now - 30000)),
                'expect' => [
                    'isActive' => false,
                ],
            ],
            //  interval in future, internal false
            [
                'entity' => (new Entity())
                    ->setStartDate(date('Y-m-d H:i:s', $now + 30000))
                    ->setEndDate(date('Y-m-d H:i:s', $now + 2 * 30000)),
                'expect' => [
                    'isActive' => false,
                ],
            ],
        ];
    }
}
