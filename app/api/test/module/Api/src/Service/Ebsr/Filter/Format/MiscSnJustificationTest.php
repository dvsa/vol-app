<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification;

/**
 * Class MiscSnJustificationTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 * @covers Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class MiscSnJustificationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $expected
     * @param array $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter(mixed $expected, mixed $value): void
    {
        $sut = new MiscSnJustification();

        $result = $sut->filter(['busShortNotice' => $value]);
        $this->assertEquals($expected, $result['busShortNotice']);
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public static function provideFilter(): array
    {
        $unforseenDetailValue = 'unforseen detail text';
        $unforseenDetailKey = 'unforseenDetail';
        $unforseenChangeKey = 'unforseenChange';
        $miscJustificationValue = 'misc justification text';
        $miscJustificationKey = 'miscJustification';
        $formattedMiscJustification = 'Miscellaneous justification: ' . $miscJustificationValue;

        $onlyUnforseen = [
            $unforseenDetailKey => $unforseenDetailValue,
            $unforseenChangeKey => 'Y'
        ];

        $bothHaveValuesInput = [
            $unforseenDetailKey => $unforseenDetailValue,
            $miscJustificationKey => $miscJustificationValue,
            $unforseenChangeKey => 'Y'
        ];

        $bothHaveValuesResult = [
            $unforseenDetailKey => $unforseenDetailValue . ' ' . $formattedMiscJustification,
            $miscJustificationKey => $miscJustificationValue,
            $unforseenChangeKey => 'Y'
        ];

        $onlyMiscJustificationInput = [
            $miscJustificationKey => $miscJustificationValue,
            $unforseenChangeKey => 'N'
        ];

        $onlyMiscJustificationResult = [
            $unforseenDetailKey => $formattedMiscJustification,
            $miscJustificationKey => $miscJustificationValue,
            $unforseenChangeKey => 'Y'
        ];

        return [
            [$onlyUnforseen, $onlyUnforseen],
            [$bothHaveValuesResult, $bothHaveValuesInput],
            [$onlyMiscJustificationResult, $onlyMiscJustificationInput]
        ];
    }
}
