<?php

/**
 * ContinuationDetailStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\ContinuationDetailStatus;

/**
 * ContinuationDetailStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class ContinuationDetailStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ContinuationDetailStatus();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderIsValid')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function dataProviderIsValid(): \Iterator
    {
        yield ['con_det_sts_prepared', true];
        yield ['con_det_sts_printing', true];
        yield ['con_det_sts_printed', true];
        yield ['con_det_sts_unacceptable', true];
        yield ['con_det_sts_acceptable', true];
        yield ['con_det_sts_complete', true];
        yield ['con_det_sts_error', true];
        yield [null, false];
        yield [' ', false];
        yield ['con_det_sts_xxxx ', false];
    }
}
