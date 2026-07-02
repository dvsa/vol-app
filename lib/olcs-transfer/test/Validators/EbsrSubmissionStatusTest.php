<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\EbsrSubmissionStatus;

/**
 * EbsrSubmissionStatusTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EbsrSubmissionStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new EbsrSubmissionStatus();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    /**
     * @return array
     */
    public function isValidProvider()
    {
        return [
            ['ebsrs_processed', true],
            ['ebsrs_processing', true],
            ['ebsrs_submitted', true],
            ['ebsrs_validating', true],
            ['ebsrs_failed', true],
            ['ebsrs_uploaded', true],
            ['a', false],
            [1, false],
            [' ', false],
            [null, false],
        ];
    }
}
