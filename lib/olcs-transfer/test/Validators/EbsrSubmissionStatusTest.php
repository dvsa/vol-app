<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\EbsrSubmissionStatus;

/**
 * EbsrSubmissionStatusTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class EbsrSubmissionStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new EbsrSubmissionStatus();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function isValidProvider(): \Iterator
    {
        yield ['ebsrs_processed', true];
        yield ['ebsrs_processing', true];
        yield ['ebsrs_submitted', true];
        yield ['ebsrs_validating', true];
        yield ['ebsrs_failed', true];
        yield ['ebsrs_uploaded', true];
        yield ['a', false];
        yield [1, false];
        yield [' ', false];
        yield [null, false];
    }
}
