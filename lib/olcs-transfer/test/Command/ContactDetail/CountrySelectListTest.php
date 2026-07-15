<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\ContactDetail;

use Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\ContactDetail\CountrySelectList::class)]
final class CountrySelectListTest extends MockeryTestCase
{
    public function testCountrySelectList()
    {
        $sut = CountrySelectList::create(
            [
                'isEcmtState' => 1,
                'isEeaState' => 1
            ]
        );

        $this->assertEquals(1, $sut->getIsEcmtState());
        $this->assertEquals(1, $sut->getIsEeaState());
    }
}
