<?php

namespace Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice;

use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection;

/**
 * Class RegisteredBusRouteTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\RulesValidator\ShortNotice
 */
class MissingSectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * tests whether the short notice section exists correctly
     *
     * @dataProvider isValidProvider
     *
     * @param string $isShortNotice
     * @param array $value
     * @param bool $valid
     */
    public function testIsValid($isShortNotice, $value, $valid)
    {
        $sut = new MissingSection();
        $busReg = new BusRegEntity();
        $busReg->setIsShortNotice($isShortNotice);

        $context = ['busReg' => $busReg];

        $this->assertEquals($valid, $sut->isValid($value, $context));
    }

    /**
     * Provider for testIsValid
     *
     * @return array
     */
    public function isValidProvider()
    {
        return [
            ['N', [], true],
            ['N', ['busShortNotice' => []], true],
            ['N', ['busShortNotice' => null], true],
            ['N', ['busShortNotice' => 'content'], true],
            ['Y', [], false],
            ['Y', ['busShortNotice' => []], false],
            ['Y', ['busShortNotice' => null], false],
            ['Y', ['busShortNotice' => 'content'], true]
        ];
    }
}
