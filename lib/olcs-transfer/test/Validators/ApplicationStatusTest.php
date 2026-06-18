<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\ApplicationStatus;

/**
 * ApplicationStatus Valitador Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationStatus();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function isValidProvider()
    {
        return [
            ['apsts_not_submitted', true],
            ['apsts_granted', true],
            ['apsts_consideration', true],
            ['apsts_valid', true],
            ['apsts_withdrawn', true],
            ['apsts_refused', true],
            ['apsts_ntu', true],
            ['apsts_curtailed', true],
            ['foobar', false],
            [1, false],
            [' ', false],
            [null, false],
        ];
    }
}
