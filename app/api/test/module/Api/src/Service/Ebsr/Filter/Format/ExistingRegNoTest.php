<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\ExistingRegNo;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class ExistingRegNoTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 */
class ExistingRegNoTest extends TestCase
{
    /**
     * Tests filter
     */
    public function testFilter(): void
    {
        $sut = new ExistingRegNo();

        $licNo = 123;
        $routeNo = 45;
        $expected = $licNo . '/' . $routeNo;

        $value = [
            'licNo' => $licNo,
            'routeNo' => $routeNo
        ];

        $result = $sut->filter($value);
        $this->assertEquals($expected, $result['existingRegNo']);
    }
}
