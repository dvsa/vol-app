<?php

namespace Dvsa\OlcsTest\Transfer\Query\ContactDetail;

use Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList
 */


class CountrySelectListTest extends MockeryTestCase
{
    public function testCountrySelectList()
    {
        $sut = CountrySelectList::create(
            [
                'isEcmtState' => 1,
                'isEeaState' => 1
            ]
        );

        static::assertEquals(1, $sut->getIsEcmtState());
        static::assertEquals(1, $sut->getIsEeaState());
    }
}
