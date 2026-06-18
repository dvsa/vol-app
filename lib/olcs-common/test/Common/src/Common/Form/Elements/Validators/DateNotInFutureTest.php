<?php

/**
 * Date Not In Future Validator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\DateNotInFuture;

/**
 * Date Not In Future Validator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateNotInFutureTest extends \PHPUnit\Framework\TestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DateNotInFuture();
    }

    /**
     * @group validators
     * @group date_validators
     * @dataProvider providerIsValid
     */
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{string, true}, list{string, false}, list{string, true}}
     */
    public function providerIsValid(): array
    {
        return [
            [
                date('Y-m-d'),
                true
            ],
            [
                date('Y-m-d', strtotime('+1 day')),
                false
            ],
            [
                date('Y-m-d', strtotime('-1 day')),
                true
            ]
        ];
    }
}
