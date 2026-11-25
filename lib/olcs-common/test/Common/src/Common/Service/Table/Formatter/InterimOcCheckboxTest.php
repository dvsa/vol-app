<?php

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Interim OC Checkbox Formatter Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class InterimOcCheckboxTest extends MockeryTestCase
{
    /**
     * Test formatter
     */
    #[\PHPUnit\Framework\Attributes\Group('interimFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, bool $checked): void
    {
        $data['operatingCentre'] = ['address' => ['addressLine1' => '1 High & Main Street', 'town' => 'York']];
        $output = new \Common\Service\Table\Formatter\InterimOcCheckbox()->format($data);
        $this->assertStringContainsString('id="interim-operating-centre-1" value="1" name="operatingCentres[id][]"', $output);
        $this->assertStringContainsString('for="interim-operating-centre-1">Select operating centre 1 High &amp; Main Street, York</label>', $output);
        $this->assertSame($checked, str_contains($output, ' checked>'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormatPreservesPercentInAddress($data, bool $checked): void
    {
        $data['operatingCentre'] = ['address' => ['addressLine1' => '100% Business & Park', 'town' => 'York']];

        $output = new \Common\Service\Table\Formatter\InterimOcCheckbox()->format($data);

        $this->assertStringContainsString(
            'for="interim-operating-centre-1">Select operating centre 100% Business &amp; Park, York</label>',
            $output
        );
        $this->assertSame($checked, str_contains($output, ' checked>'));
    }

    public static function formatProvider(): \Iterator
    {
        yield [
            [
                'isInterim' => 'Y',
                'id' => 1
            ],
            true
        ];
        yield [
            [
                'isInterim' => 'N',
                'id' => 1
            ],
            false
        ];
        yield [
            [
                'id' => 1
            ],
            false
        ];
    }
}
