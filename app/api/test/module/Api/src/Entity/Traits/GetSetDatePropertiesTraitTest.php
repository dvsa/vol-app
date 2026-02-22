<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GetSetDatePropertiesTraitTest
 */
class GetSetDatePropertiesTraitTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderAsDateTime')]
    public function testGetDates(mixed $expected, mixed $dateTime): void
    {
        $dateProperties = ['createdOn', 'lastModifiedOn', 'deletedDate'];
        foreach ($dateProperties as $property) {
            $entity = new StubGetSetDatePropertiesTrait();
            $setMethod = 'set' . $property;
            $getMethod = 'get' . $property;
            $entity->$setMethod($dateTime);
            $this->assertEquals($expected, $entity->$getMethod(true));
        }
    }

    public static function dataProviderAsDateTime(): array
    {
        return [
            [new \DateTime('2017-09-29'), '2017-09-29'],
            [new \DateTime('2017-09-29'), new \DateTime('2017-09-29')],
            [null, null],
        ];
    }
}
