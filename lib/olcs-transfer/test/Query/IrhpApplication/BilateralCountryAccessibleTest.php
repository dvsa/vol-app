<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible::class)]
final class BilateralCountryAccessibleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 25;
        $country = 'DE';

        $sut = BilateralCountryAccessible::create(
            [
                'id' => $id,
                'country' => $country,
            ]
        );
        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($country, $sut->getCountry());
        $this->assertEquals(
            [
                'id' => $id,
                'country' => $country,
            ],
            $sut->getArrayCopy()
        );
    }
}
