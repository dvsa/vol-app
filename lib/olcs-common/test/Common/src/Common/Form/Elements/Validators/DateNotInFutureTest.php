<?php

/**
 * Date Not In Future Validator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Validators;

use Common\Form\Elements\Validators\DateNotInFuture;

/**
 * Date Not In Future Validator Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DateNotInFutureTest extends \PHPUnit\Framework\TestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DateNotInFuture();
    }

    #[\PHPUnit\Framework\Attributes\Group('validators')]
    #[\PHPUnit\Framework\Attributes\Group('date_validators')]
    #[\PHPUnit\Framework\Attributes\DataProvider('providerIsValid')]
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return \Iterator<(int | string), array<(bool | string)>>
     *
     * @psalm-return list{list{string, true}, list{string, false}, list{string, true}}
     */
    public static function providerIsValid(): \Iterator
    {
        yield [
            date('Y-m-d'),
            true
        ];
        yield [
            date('Y-m-d', strtotime('+1 day')),
            false
        ];
        yield [
            date('Y-m-d', strtotime('-1 day')),
            true
        ];
    }
}
