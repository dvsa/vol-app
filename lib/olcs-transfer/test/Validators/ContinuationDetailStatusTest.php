<?php

/**
 * ContinuationDetailStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\ContinuationDetailStatus;

/**
 * ContinuationDetailStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ContinuationDetailStatus();
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function dataProviderIsValid()
    {
        return [
            ['con_det_sts_prepared', true],
            ['con_det_sts_printing', true],
            ['con_det_sts_printed', true],
            ['con_det_sts_unacceptable', true],
            ['con_det_sts_acceptable', true],
            ['con_det_sts_complete', true],
            ['con_det_sts_error', true],
            [null, false],
            [' ', false],
            ['con_det_sts_xxxx ', false],
        ];
    }
}
