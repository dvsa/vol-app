<?php

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GetSetDatePropertiesTraitTest
 */
class GetSetDatePropertiesTraitTest extends MockeryTestCase
{
    /**
     * @dataProvider dataProviderAsDateTime
     */
    public function testGetDates($expected, $dateTime)
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

    public function dataProviderAsDateTime()
    {
        return [
            [new \DateTime('2017-09-29'), '2017-09-29'],
            [new \DateTime('2017-09-29'), new \DateTime('2017-09-29')],
            [null, null],
        ];
    }
}
